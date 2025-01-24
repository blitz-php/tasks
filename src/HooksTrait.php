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
use BlitzPHP\Contracts\Mail\MailerInterface;
use BlitzPHP\Utilities\Iterable\Arr;
use BlitzPHP\Utilities\String\Stringable;
use Closure;
use Throwable;

/**
 *
 */
trait HooksTrait
{
	/**
     * L'emplacement où la sortie doit être envoyée.
     */
    public ?string $location = null;

	/**
	 * Code de sortie de la tache
	 */
	protected ?int $exitCode = null;

	/**
     * Exception levée lors de l'exécution de la tâche.
     */
	protected ?Throwable $exception = null;

	/**
     * Indique si la sortie doit être ajoutée.
     */
    public bool $shouldAppendOutput = false;

    /**
     * Tableau de rappels à exécuter avant l'execution de la tâche.
	 *
	 * @var list<Closure>
     */
    protected array $beforeCallbacks = [];

    /**
     * Tableau de rappels à exécuter après l'execution de la tâche.
	 *
	 * @var list<Closure>
     */
    protected $afterCallbacks = [];

	/**
     * Met la sortie de la tâche dans un fichier donné.
     */
    public function sendOutputTo(string $location, bool $append = false): self
    {
		$this->location           = $location;
		$this->shouldAppendOutput = $append;

        return $this;
    }

	/**
     * Ajoute la sortie de la tâche à la fin d'un fichier donné.
     */
    public function appendOutputTo(string $location): self
    {
        return $this->sendOutputTo($location, true);
    }

	/**
     * Envoi le resultat de l'execution de la tache par mail.
     *
     * @param  array|mixed  $addresses
     *
     * @throws \LogicException
     */
    public function emailOutputTo($addresses, bool $onlyIfOutputExists = false): self
    {
        $this->ensureOutputIsBeingCaptured();

        $addresses = Arr::wrap($addresses);

        return $this->then(function (MailerInterface $mailer) use ($addresses, $onlyIfOutputExists) {
            $this->emailOutput($mailer, $addresses, $onlyIfOutputExists);
        });
    }

    /**
     * Envoi le resultat de l'execution de la tache par mail si un resultat existe dans la sortie.
     *
     * @param  array|mixed  $addresses
     *
     * @throws \LogicException
     */
    public function emailWrittenOutputTo($addresses): self
    {
        return $this->emailOutputTo($addresses, true);
    }

    /**
     * Envoi le resultat de l'execution de la tache par mail si l'operation a echouée.
     *
     * @param  array|mixed  $addresses
     */
    public function emailOutputOnFailure($addresses): self
    {
        $this->ensureOutputIsBeingCaptured();

        $addresses = Arr::wrap($addresses);

        return $this->onFailure(function (MailerInterface $mailer) use ($addresses) {
            $this->emailOutput($mailer, $addresses, false);
        });
    }

	/**
     * Enregistre un callback à appeler avant l'opération.
     */
    public function before(Closure $callback): self
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Enregistre un callback à appeler apres l'opération.
     */
    public function after(Closure $callback): self
    {
        return $this->then($callback);
    }

    /**
     * Enregistre un callback à appeler apres l'opération.
     */
    public function then(Closure $callback): self
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

	/**
     * Enregistre un callback à appeler si l'opération se deroulle avec succes.
     */
    public function onSuccess(Closure $callback): self
    {
        return $this->then(function (ContainerInterface $container) use ($callback) {
            if ($this->exitCode === EXIT_SUCCESS) {
                $container->call($callback);
            }
        });
    }

	/**
     * Enregistre un callback à appeler si l'opération ne se deroulle pas correctement.
     */
    public function onFailure(Closure $callback): self
    {
        return $this->then(function (ContainerInterface $container) use ($callback) {
            if ($this->exitCode !== EXIT_SUCCESS) {
                $container->call($callback, array_filter([$this->exception]));
            }
        });
    }

	/**
	 * Procede a l'execution de la tache
	 */
	protected function process(ContainerInterface $container, string $method): mixed
	{
		ob_start();

		$result = $this->start($this->container, $method);

		// if (! $this->runInBackground) {
        	$result = $this->finish($this->container, $result);

			ob_end_flush();

			return $result;
        // }
	}

	/**
     * Demarre l'execution de la tache
	 *
	 * @return mixed Le resultat de l'execution de la tache
     *
     * @throws Throwable
     */
    protected function start(ContainerInterface $container, string $runMethod)
    {
        try {
            $this->callBeforeCallbacks($container);

            return $this->execute($container, $runMethod);
        } catch (Throwable $e) {
			$this->registerException($e);
        }
    }

	/**
     * Execute la tache.
	 *
	 * @return mixed Le resultat de l'execution de la tache
     */
    protected function execute(ContainerInterface $container, string $runMethod): mixed
    {
		try {
			$result = $this->{$runMethod}();

			if (is_int($result)) {
				$this->exitCode = $result;
			} else {
				$this->exitCode = EXIT_SUCCESS;
			}
		} catch (Throwable $e) {
			$this->registerException($e);
		}

		return $result ?? null;
    }

    /**
     * Marque l'execution de la tache comme terminée et lance les callbacks/nettoyages.
     */
    protected function finish(ContainerInterface $container, mixed $result): mixed
    {
        try {
            $output = $this->callAfterCallbacks($container, $result);
        } finally {
			if (isset($output) && $output !== '' && $this->location !== null) {
				@file_put_contents($this->location, $output, $this->shouldAppendOutput ? FILE_APPEND : 0);
			}
        }

		return $result;
    }

    /**
     * S'assurer que les résultats de la tâche sont capturés.
     */
    protected function ensureOutputIsBeingCaptured(): void
    {
        if (null === $this->location) {
            $this->sendOutputTo(storage_path('logs/task-' . sha1($this->name) . '.log'));
        }
    }

    /**
     * Envoie du résultat de l'execution de la tache par mail aux destinataires.
	 *
	 * @param list<string> $addresses Liste des addresses a qui le mail sera envoyer
     */
    protected function emailOutput(MailerInterface $mailer, array $addresses, bool $onlyIfOutputExists = false): void
    {
        $text = is_file($this->location) ? file_get_contents($this->location) : '';

        if ($onlyIfOutputExists && empty($text)) {
            return;
        }

		$mailer->to($addresses)->subject($this->getEmailSubject())->text($text)->send();
    }

    /**
     * Objet de l'e-mail pour les résultats de sortie.
     */
    protected function getEmailSubject(): string
    {
        return "Sortie de la tâche planifiée pour [{$this->name}]";
    }

	/**
     * Appelle tous les callbacks qui doivent être lancer "avant" l'exécution de la tâche.
     */
    protected function callBeforeCallbacks(ContainerInterface $container): void
    {
        foreach ($this->beforeCallbacks as $callback) {
            $container->call($callback);
        }
    }

	/**
     * Appelle tous les callbacks qui doivent être lancer "apres" l'exécution de la tâche.
     */
    protected function callAfterCallbacks(ContainerInterface $container, mixed $result = null): string
    {
		$parameters = ['result' => $result];

		if ('' !== $output = ob_get_contents() ?: '') {
			$parameters['output'] = new Stringable($output);
		}

        foreach ($this->afterCallbacks as $callback) {
            $container->call($callback, $parameters);
        }

		return $output;
    }

	/**
     * Marque l'exception en cours et définit le code de sortie à EXIT_ERROR.
     */
	protected function registerException(Throwable $e): void
	{
		$this->exception = $e;
		$this->exitCode  = EXIT_ERROR;
	}
}
