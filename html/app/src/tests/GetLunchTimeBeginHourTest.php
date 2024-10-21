<?php

declare(strict_types=1);

namespace App\Tests;

use App\GetLunchTimeBeginHour;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\tests\DataProviders\GetLunchTimeBeginHourDataProvider;


class GetLunchTimeBeginHourTest extends TestCase
{
    public const MIN_BEGIN_LUNCH = '12:00';
    public const MAX_END_LUNCH = '13:00';
    public const DURATION_LUNCH = 30;

    // Test every scenarios possibles to choose the good lunchTime begin hour

    #[DataProviderExternal(GetLunchTimeBeginHourDataProvider::class, 'getLunchTimeBeginHourDataProvider')]
    public function testGetLunchTimeBeginHour(string $caseNumber, array $appointments, ?string $expected): void
    {
        $minLunchTime = DateTimeImmutable::createFromFormat('H:i', self::MIN_BEGIN_LUNCH);
        $maxLunchTime = DateTimeImmutable::createFromFormat('H:i', self::MAX_END_LUNCH);

        // if string we transform it into DateTime, else we except a null result so we set expected to null
        $expected = is_string($expected) ? DateTimeImmutable::createFromFormat('H:i', $expected) : null;

        $appointments = $this->makeAppointmentsDatetime($appointments);
        $getLunchTimeBeginHour = new GetLunchTimeBeginHour();
        $actualResult = $getLunchTimeBeginHour->execute($appointments, $minLunchTime, $maxLunchTime, self::DURATION_LUNCH);

        $this->assertEquals(
            $expected,
            $actualResult,
            'Day of appointment #' . $caseNumber . ': unexpected result');
    }

    /**
     * Transform an array of strings to datetime begin and end of an appointment, avoiding unnecessary data (name and result).
     */
    private function makeAppointmentsDatetime(array $appointments): array
    {
        foreach ($appointments as $key => $appointment) {
            if (is_array($appointment)) {
                $appointments[$key]['begin'] = DateTimeImmutable::createFromFormat('H:i', $appointment[0]);
                $appointments[$key]['end'] = DateTimeImmutable::createFromFormat('H:i', $appointment[1]);
            }
        }

        return $appointments;
    }

}