<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Tasks\FrequenciesTrait;
use BlitzPHP\Utilities\Date;

use function Kahlan\expect;

describe('FrequenciesTrait', function () {
    beforeAll(function () {
        $this->assertSame = function ($expected, $actual) {
            expect($expected)->toBe($actual);
        };
    });
    beforeEach(function () {
        $this->class = new class () {
            use FrequenciesTrait;
        };
    });

    it('testSetCron', function () {
        $cron = '5 10 11 12 6';

        $this->class->cron($cron);

        expect($cron)->toBe($this->class->getExpression());
    });

    it('testDaily', function () {
        $this->class->daily();

        expect('0 0 * * *')->toBe($this->class->getExpression());
    });

    it('testDailyWithTime', function () {
        $this->class->daily('4:08 pm');
        expect('08 16 * * *')->toBe($this->class->getExpression());

		$this->class->at('4:08 pm');
        expect('08 16 * * *')->toBe($this->class->getExpression());

		$this->class->at('04:28');
        expect('28 4 * * *')->toBe($this->class->getExpression());
    });

    it('testTime', function () {
        $this->class->time('4:08 pm');

        expect('08 16 * * *')->toBe($this->class->getExpression());
    });

    it('testHourly', function () {
        $this->class->hourly();

        expect('00 * * * *')->toBe($this->class->getExpression());
    });

    it('testHourlyWithMinutes', function () {
        $this->class->hourly(30);

        expect('30 * * * *')->toBe($this->class->getExpression());
    });

    it('testEveryFiveMinutes', function () {
        $this->class->everyFiveMinutes();

        expect('*/5 * * * *')->toBe($this->class->getExpression());
    });

    it('testEveryFifteenMinutes', function () {
        $this->class->everyFifteenMinutes();

        expect('*/15 * * * *')->toBe($this->class->getExpression());
    });

    it('testEveryThirtyMinutes', function () {
        $this->class->everyThirtyMinutes();

        expect('*/30 * * * *')->toBe($this->class->getExpression());
    });

    it('testEverySunday', function () {
        $this->class->sundays();

        expect('* * * * 0')->toBe($this->class->getExpression());
    });

    it('testEverySundayWithTime', function () {
        $this->class->sundays('4:08 pm');

        expect('08 16 * * 0')->toBe($this->class->getExpression());
    });

    it('testEveryMonday', function () {
        $this->class->mondays();

        expect('* * * * 1')->toBe($this->class->getExpression());
    });

    it('testEveryMondayWithTime', function () {
        $this->class->mondays('4:08 pm');

        expect('08 16 * * 1')->toBe($this->class->getExpression());
    });

    it('testEveryTuesday', function () {
        $this->class->tuesdays();

        expect('* * * * 2')->toBe($this->class->getExpression());
    });

    it('testEveryTuesdayWithTime', function () {
        $this->class->tuesdays('4:08 pm');

        expect('08 16 * * 2')->toBe($this->class->getExpression());
    });

    it('testEveryWednesday', function () {
        $this->class->wednesdays();

        expect('* * * * 3')->toBe($this->class->getExpression());
    });

    it('testEveryWednesdayWithTime', function () {
        $this->class->wednesdays('4:08 pm');

        expect('08 16 * * 3')->toBe($this->class->getExpression());
    });

    it('testEveryThursday', function () {
        $this->class->thursdays();

        $this->assertSame('* * * * 4', $this->class->getExpression());
    });

    it('testEveryThursdayWithTime', function () {
        $this->class->thursdays('4:08 pm');

        $this->assertSame('08 16 * * 4', $this->class->getExpression());
    });

    it('testEveryFriday', function () {
        $this->class->fridays();

        $this->assertSame('* * * * 5', $this->class->getExpression());
    });

    it('testEveryFridayWithTime', function () {
        $this->class->fridays('4:08 pm');

        $this->assertSame('08 16 * * 5', $this->class->getExpression());
    });

    it('testEverySaturday', function () {
        $this->class->saturdays();

        $this->assertSame('* * * * 6', $this->class->getExpression());
    });

    it('testEverySaturdayWithTime', function () {
        $this->class->saturdays('4:08 pm');

        $this->assertSame('08 16 * * 6', $this->class->getExpression());
    });

    it('testMonthly', function () {
        $this->class->monthly();

        $this->assertSame('0 0 1 * *', $this->class->getExpression());
    });

    it('testMonthlyWithTime', function () {
        $this->class->monthly('4:08 pm');

        $this->assertSame('08 16 1 * *', $this->class->getExpression());
    });

    it('testYearly', function () {
        $this->class->yearly();

        $this->assertSame('0 0 1 1 *', $this->class->getExpression());
    });

    it('testYearlyWithTime', function () {
        $this->class->yearly('4:08 pm');

        $this->assertSame('08 16 1 1 *', $this->class->getExpression());
    });

    it('testQuarterly', function () {
        $this->class->quarterly();

        $this->assertSame('0 0 1 */3 *', $this->class->getExpression());
    });

    it('testQuarterlyWithTime', function () {
        $this->class->quarterly('4:08 pm');

        $this->assertSame('08 16 1 */3 *', $this->class->getExpression());
    });

    it('testWeekdays', function () {
        $this->class->weekdays();

        $this->assertSame('0 0 * * 1-5', $this->class->getExpression());
    });

    it('testWeekdaysWithTime', function () {
        $this->class->weekdays('4:08 pm');

        $this->assertSame('08 16 * * 1-5', $this->class->getExpression());
    });

    it('testWeekends', function () {
        $this->class->weekends();

        $this->assertSame('0 0 * * 6-7', $this->class->getExpression());
    });

    it('testWeekendsWithTime', function () {
        $this->class->weekends('4:08 pm');

        $this->assertSame('08 16 * * 6-7', $this->class->getExpression());
    });

    it('testEveryHour', function () {
        $this->class->everyHour();

        $this->assertSame('0 * * * *', $this->class->getExpression());
    });

    it('testEveryHourWithHour', function () {
        $this->class->everyHour(3);
        $this->assertSame('0 */3 * * *', $this->class->getExpression());

		$this->class->everyTwoHours();
        $this->assertSame('0 */2 * * *', $this->class->getExpression());

		$this->class->everyThreeHours();
        $this->assertSame('0 */3 * * *', $this->class->getExpression());

		$this->class->everyFourHours();
        $this->assertSame('0 */4 * * *', $this->class->getExpression());

		$this->class->everySixHours();
        $this->assertSame('0 */6 * * *', $this->class->getExpression());
    });

    it('testEveryHourWithHourAndMinutes', function () {
        $this->class->everyHour(3, 15);

        $this->assertSame('15 */3 * * *', $this->class->getExpression());
    });

    it('testEveryOddHour', function () {
        $this->class->everyOddHour();

        $this->assertSame('0 1-23/2 * * *', $this->class->getExpression());
    });

    it('testBetweenHours', function () {
        $this->class->betweenHours(10, 12);

        $this->assertSame('* 10-12 * * *', $this->class->getExpression());
    });

    it('testHours', function () {
        $this->class->hours([12, 16]);

        $this->assertSame('* 12,16 * * *', $this->class->getExpression());
    });

    it('testEveryMonth', function () {
        $this->class->everyMonth();

        $this->assertSame('* * * * *', $this->class->getExpression());
    });

    it('testEveryMonthWithParameter', function () {
        $this->class->everyMonth(4);

        $this->assertSame('* * * */4 *', $this->class->getExpression());
    });

    it('testEveryMinute', function () {
        $this->class->everyMinute();

        $this->assertSame('* * * * *', $this->class->getExpression());
    });

    it('testEveryMinuteWithParameter', function () {
        $this->class->everyMinute(15);
        $this->assertSame('*/15 * * * *', $this->class->getExpression());

		$this->class->everyTwoMinutes();
        $this->assertSame('*/2 * * * *', $this->class->getExpression());

		$this->class->everyThreeMinutes();
        $this->assertSame('*/3 * * * *', $this->class->getExpression());

		$this->class->everyFourMinutes();
        $this->assertSame('*/4 * * * *', $this->class->getExpression());

		$this->class->everyTenMinutes();
        $this->assertSame('*/10 * * * *', $this->class->getExpression());

		$this->class->everyThirtyMinutes();
        $this->assertSame('*/30 * * * *', $this->class->getExpression());
    });

    it('testBetweenMinutes', function () {
        $this->class->betweenMinutes(15, 30);

        $this->assertSame('15-30 * * * *', $this->class->getExpression());
    });

    it('testMinutes', function () {
        $this->class->minutes([0, 10, 30]);

        $this->assertSame('0,10,30 * * * *', $this->class->getExpression());
    });

    it('testDays', function () {
        $this->class->days([0, 4]);

        $this->assertSame('* * * * 0,4', $this->class->getExpression());
    });

    it('testDaysOfMonth', function () {
        $this->class->daysOfMonth([1, 15]);

        $this->assertSame('* * 1,15 * *', $this->class->getExpression());
    });

	it('testLastDayOfMonth', function () {
        $this->class->lastDayOfMonth();
		$lastDay = Date::now()->endOfMonth()->getDay();

        $this->assertSame('0 0 ' . $lastDay . ' * *', $this->class->getExpression());
    });

    it('testMonths', function () {
        $this->class->months([1, 7]);

        $this->assertSame('* * * 1,7 *', $this->class->getExpression());
    });

    it('testBetweenMonths', function () {
        $this->class->betweenMonths(1, 7);

        $this->assertSame('* * * 1-7 *', $this->class->getExpression());
    });

	it('testBetween', function () {
        $this->class->between('10:00', '12:30');

        $this->assertSame('0-30 10-12 * * *', $this->class->getExpression());
    });
});
