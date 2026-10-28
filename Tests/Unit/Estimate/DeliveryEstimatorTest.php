<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeliveryDelay\Tests\Unit\Estimate;

use DeliveryDelay\Estimate\DeliveryEstimator;
use DeliveryDelay\Tests\Double\FakeClosedDays;
use DeliveryDelay\Value\CarrierDelays;
use DeliveryDelay\Value\DeliveryInterval;
use PHPUnit\Framework\TestCase;

final class DeliveryEstimatorTest extends TestCase
{
    private const MODULE_ID = 7;

    // 2026-11-03 is a Tuesday.
    private const TUESDAY_NINE = '2026-11-03 09:00:00';

    public function testAnOrderOfTuesdayMorningWithOneDayOfPreparationAndTwoOfTransitArrivesOnThursday(): void
    {
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertSame(['2026-11-05', '2026-11-05'], $this->days($interval));
    }

    public function testTheTransitIntervalSpansFromTheMinimumToTheMaximum(): void
    {
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 4), self::TUESDAY_NINE);

        self::assertSame(['2026-11-05', '2026-11-07'], $this->days($interval));
    }

    public function testAnOrderAfterTheCutoffTimeWaitsForTheNextWorkingDay(): void
    {
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 2, '12:00'), '2026-11-03 13:00:00');

        self::assertSame(['2026-11-06', '2026-11-06'], $this->days($interval));
    }

    public function testTheCutoffTimeItselfIsAlreadyTooLate(): void
    {
        $delays = $this->delays(1, 2, 2, '12:00');

        self::assertSame(['2026-11-05', '2026-11-05'], $this->days($this->estimate(new FakeClosedDays(), $delays, '2026-11-03 11:59:59')));
        self::assertSame(['2026-11-06', '2026-11-06'], $this->days($this->estimate(new FakeClosedDays(), $delays, '2026-11-03 12:00:00')));
    }

    public function testACarrierWithoutCutoffTimeTakesTheOrderUntilMidnight(): void
    {
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 2), '2026-11-03 23:59:59');

        self::assertSame(['2026-11-05', '2026-11-05'], $this->days($interval));
    }

    public function testAClosedDayInsideTheIntervalPushesTheArrivalByOneDay(): void
    {
        $interval = $this->estimate(new FakeClosedDays(['2026-11-04']), $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertSame(['2026-11-06', '2026-11-06'], $this->days($interval));
    }

    public function testAClosedTodayMovesTheFirstWorkingDayToTomorrow(): void
    {
        $interval = $this->estimate(new FakeClosedDays(['2026-11-03']), $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertSame(['2026-11-06', '2026-11-06'], $this->days($interval));
    }

    public function testTheFirstWorkingDayIsTheFirstDayOfPreparation(): void
    {
        // Three days of preparation: the parcel leaves on the third working day, Thursday.
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(3, 1, 1), self::TUESDAY_NINE);

        self::assertSame(['2026-11-06', '2026-11-06'], $this->days($interval));
    }

    public function testNoPreparationLeavesOnTheFirstWorkingDayLikeOneDayOfPreparation(): void
    {
        $none = $this->estimate(new FakeClosedDays(), $this->delays(0, 2, 2), self::TUESDAY_NINE);
        $one = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertSame($this->days($one), $this->days($none));
    }

    public function testASlowProductAddsItsDaysToThePreparation(): void
    {
        // 1 + 5 days of preparation with the weekend closed: left on Tuesday the 10th, there on Thursday the 12th.
        $interval = $this->estimate(new FakeClosedDays([], true), $this->delays(1, 2, 2), self::TUESDAY_NINE, productDays: 5);

        self::assertSame(['2026-11-12', '2026-11-12'], $this->days($interval));
    }

    public function testAClosedWeekendIsSkippedByThePreparationAndByTheTransit(): void
    {
        // Friday 9h, one day of preparation, two of transit: Monday and Tuesday are the transit days.
        $interval = $this->estimate(new FakeClosedDays([], true), $this->delays(1, 2, 2), '2026-11-06 09:00:00');

        self::assertSame(['2026-11-10', '2026-11-10'], $this->days($interval));
    }

    public function testAnOrderOnAClosedSaturdayStartsOnMonday(): void
    {
        $interval = $this->estimate(new FakeClosedDays([], true), $this->delays(1, 1, 1), '2026-11-07 10:00:00');

        self::assertSame(['2026-11-10', '2026-11-10'], $this->days($interval));
    }

    public function testTheCountCrossesTheNewYear(): void
    {
        // Thursday 31 December 2026, New Year's Day closed: the first transit day is the 2nd, the second the 3rd.
        $interval = $this->estimate(new FakeClosedDays(['2027-01-01']), $this->delays(1, 2, 2), '2026-12-31 09:00:00');

        self::assertSame(['2027-01-03', '2027-01-03'], $this->days($interval));
    }

    public function testTheCountCrossesTheSpringClockChange(): void
    {
        // Saturday 27 March 2027, the night before Europe/Paris loses an hour: Sunday is not skipped or counted twice.
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 3), '2027-03-27 09:00:00', timezone: 'Europe/Paris');

        self::assertSame(['2027-03-29', '2027-03-30'], $this->days($interval));
    }

    public function testTheCountCrossesTheAutumnClockChange(): void
    {
        // Saturday 24 October 2026, the night before Europe/Paris gains an hour.
        $interval = $this->estimate(new FakeClosedDays(), $this->delays(1, 2, 3), '2026-10-24 09:00:00', timezone: 'Europe/Paris');

        self::assertSame(['2026-10-26', '2026-10-27'], $this->days($interval));
    }

    public function testTheCutoffIsReadOnTheClockOfTheShopOnTheDayOfAClockChange(): void
    {
        // 03:30 on the night of the change is before a 05:00 cutoff, whatever the offset.
        $delays = $this->delays(1, 1, 1, '05:00');

        $interval = $this->estimate(new FakeClosedDays(), $delays, '2027-03-28 03:30:00', timezone: 'Europe/Paris');

        self::assertSame(['2027-03-29', '2027-03-29'], $this->days($interval));
    }

    public function testACalendarClosedEveryDayGivesNoEstimateAndStopsLooking(): void
    {
        $calendar = new FakeClosedDays(closeEveryDay: true);

        $interval = $this->estimate($calendar, $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertNull($interval);
        self::assertLessThanOrEqual(4, $calendar->calls, 'The window grows a few times, then the search ends.');
    }

    public function testACountThatRunsOffTheFirstWindowReadsALongerOne(): void
    {
        $calendar = new FakeClosedDays();

        // 200 working days of preparation do not fit in the first 120 days.
        $interval = $this->estimate($calendar, $this->delays(1, 1, 1), self::TUESDAY_NINE, productDays: 200);

        self::assertNotNull($interval);
        self::assertSame('2027-05-23', $interval->earliestDay());
        self::assertSame(2, $calendar->calls);
    }

    public function testTheCalendarIsReadOnceForAnOrdinaryCount(): void
    {
        $calendar = new FakeClosedDays();

        $this->estimate($calendar, $this->delays(1, 2, 2), self::TUESDAY_NINE);

        self::assertSame(1, $calendar->calls);
    }

    public function testACutoffTimeIsParsedStrictly(): void
    {
        self::assertSame(12 * 3600, CarrierDelays::secondOfDay('12:00'));
        self::assertSame(0, CarrierDelays::secondOfDay('00:00:00'));
        self::assertNull(CarrierDelays::secondOfDay(''));
        self::assertNull(CarrierDelays::secondOfDay(null));

        $this->expectException(\InvalidArgumentException::class);

        CarrierDelays::secondOfDay('25:00');
    }

    private function delays(int $preparation, int $transitMin, int $transitMax, ?string $cutoff = null): CarrierDelays
    {
        return new CarrierDelays(self::MODULE_ID, $preparation, $transitMin, $transitMax, CarrierDelays::secondOfDay($cutoff));
    }

    private function estimate(
        FakeClosedDays $calendar,
        CarrierDelays $delays,
        string $now,
        int $productDays = 0,
        string $timezone = 'UTC',
    ): ?DeliveryInterval {
        return (new DeliveryEstimator($calendar))->estimate($delays, $productDays, new \DateTimeImmutable($now, new \DateTimeZone($timezone)));
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function days(?DeliveryInterval $interval): ?array
    {
        self::assertNotNull($interval, 'An estimate was expected.');

        return [$interval->earliestDay(), $interval->latestDay()];
    }
}
