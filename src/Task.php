<?php

declare(strict_types=1);

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Tasks;

use BlitzPHP\Contracts\Container\ContainerInterface;
use BlitzPHP\Tasks\Exceptions\TasksException;
use BlitzPHP\Utilities\Date;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use SplFileObject;

/**
 * Représente une tâche unique qui doit être planifiée et exécutée périodiquement.
 *
 * @property mixed        $action
 * @property list<string> $environments
 * @property string       $name
 * @property string       $type
 * @property list<string> $types
 *
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\Task</a>
 */
class Task
{
    use FrequenciesTrait;
    use HooksTrait;

    /**
     * Types d'action supportés
     *
     * @var list<string>
     */
    protected array $types = [
        'command',
        'shell',
        'closure',
        'event',
        'url',
    ];

    /**
     * S'il n'est pas vide, liste les environnements autorisés dans lesquels le programme peut être exécuté.
     *
     * @var list<string>
     */
    protected array $environments = [];

    /**
     * Timezone dans lequel la tâche doit être traitée.
     */
    protected ?string $timezone = null;

    /**
     * Proprietés magiques emulées
     *
     * @var array<string,mixed>
     */
    protected array $attributes = [];

    protected ContainerInterface $container;

    /**
     * @param string      $type       Type de l'action en cours.
     * @param mixed       $action     Le contenu actuel qu'on souhaite executer.
     * @param list<mixed> $parameters Parametres eventuels de l'action
     *
     * @throws TasksException
     */
    public function __construct(protected string $type, protected mixed $action, protected array $parameters = [])
    {
        if (! in_array($type, $this->types, true)) {
            throw TasksException::invalidTaskType($type);
        }

        $this->container = service('container');
    }

    /**
     * Définissez le nom par lequel sera référencé cette tâche
     */
    public function named(string $name): self
    {
        $this->attributes['name'] = $name;

        return $this;
    }

    /**
     * Renvoie le type de la tache.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Renvoie l'action enregistrée.
     */
    public function getAction(): mixed
    {
        return $this->action;
    }

    /**
     * Exécute l'action de cette tâche.
     *
     * @return mixed
     *
     * @throws TasksException
     */
    public function run()
    {
        $method = 'run' . ucfirst($this->type);
        if (! method_exists($this, $method)) {
            throw TasksException::invalidTaskType($this->type);
        }

        return $this->process($this->container, $method);
    }

    /**
     * Détermine si cette tâche doit être exécutée maintenant en fonction de sa planification et de son environnement.
     */
    public function shouldRun(?string $testTime = null): bool
    {
        $cron = service('cronExpression')->setTimezone($this->timezone);

        // Autoriser le réglage des heures pendant les tests
        if (! empty($testTime)) {
            $cron->testTime($testTime);
        }

        // Sommes-nous limités aux environnements?
        if (! $this->runsInEnvironment(environment())) {
            return false;
        }

        return $cron->shouldRun($this->getExpression());
    }

    /**
     * Limite l'exécution de cette tâche uniquement dans des environnements spécifiés.
     */
    public function environments(string ...$environments): self
    {
        $this->environments = $environments;

        return $this;
    }

    /**
     * Définit le fuseau horaire pour l'exécution de la tâche.
     *
     * @param string $timezone L'identifiant du fuseau horaire à utiliser pour la tâche.
     *                         Il doit s'agir d'une chaîne de caractères PHP valide (par exemple, 'America/New_York', 'Europe/Paris').
     */
    public function timezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Renvoie la date à laquelle cette tâche a été exécutée pour la dernière fois.
     *
     * @return Date|string
     */
    public function lastRun()
    {
        if (parametre('tasks.log_performance') === false) {
            return '--';
        }

        // Recupere les logs
        $logs = parametre("tasks.log-{$this->name}");

        if (empty($logs)) {
            return '--';
        }

        $log = array_shift($logs);

        return Date::parse($log['start']);
    }

    /**
     * Vérifie s'il peut s'exécute dans l'environnement spécifié.
     */
    protected function runsInEnvironment(string $environment): bool
    {
        return empty($this->environments) || in_array($environment, $this->environments, true);
    }

    /**
     * Execute une commande Klinge.
     *
     * @return string Sortie tamponnée de la commande
     *
     * @throws InvalidArgumentException
     */
    protected function runCommand(): string
    {
        if (class_exists($command = $this->getAction())) {
        }

        return command($command);
    }

    /**
     * Execute un script shell.
     *
     * @return list<string> Lignes de la sortie de l'execution
     */
    protected function runShell(): array
    {
        exec($this->getAction(), $output);

        return $output;
    }

    /**
     * Execute un Closure.
     *
     * @return mixed Le resultat de la closure
     */
    protected function runClosure(): mixed
    {
        return $this->container->call($this->getAction(), $this->parameters);
    }

    /**
     * Declanche un evenement.
     *
     * @return bool Resultat du declanchement
     */
    protected function runEvent(): bool
    {
        return ! (false === service('event')->emit($this->getAction()));
    }

    /**
     * Interroge une URL.
     *
     * @return string Corps de la response
     */
    protected function runUrl(): string
    {
        $response = service('httpclient')->get($this->getAction());

        return $response->body();
    }

    /**
     * Crée un nom unique pour la tâche.
     * Utilisé lorsqu'un nom existant n'existe pas.
     *
     * @throws ReflectionException
     */
    protected function buildName(): string
    {
        // Obtenez un hachage basé sur l'action
        // Les closure ne peuvent pas être sérialisées, alors faites-le à la dure
        if ($this->getType() === 'closure') {
            $ref  = new ReflectionFunction($this->getAction());
            $file = new SplFileObject($ref->getFileName());
            $file->seek($ref->getStartLine() - 1);
            $content = '';

            while ($file->key() < $ref->getEndLine()) {
                $content .= $file->current();
                $file->next();
            }
            $actionString = json_encode([
                $content,
                $ref->getStaticVariables(),
            ]);
        } else {
            $actionString = serialize($this->getAction());
        }

        // Obtenir un hachage basé sur l'expression
        $expHash = $this->getExpression();

        return $this->getType() . '_' . md5($actionString . '_' . $expHash);
    }

    /**
     * Getter magique
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        if ($key === 'name' && empty($this->attributes['name'])) {
            return $this->buildName();
        }

        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Setter magique
     */
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }
}
