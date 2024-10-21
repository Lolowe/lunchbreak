<?php
declare(strict_types=1);

use MeGroup\Tour\Infrastructure\Service\GetLunchTimeBeginHour;
use PHPUnit\Framework\TestCase;

class GetLunchTimeBeginHourTest extends TestCase
{
    public const MIN_BEGIN_LUNCH = '12:00';
    public const MAX_END_LUNCH = '13:00';
    public const DURATION_LUNCH = 30;

    // Test every scenarii possibles to choose the good lunchTime hour
    public function testGetLunchTimeBeginHour()
    {
        $minLunchTime = \DateTimeImmutable::createFromFormat('H:i', self::MIN_BEGIN_LUNCH);
        $maxLunchTime = \DateTimeImmutable::createFromFormat('H:i', self::MAX_END_LUNCH);

        foreach ($this->daysOfAppointments as $dayOfAppointments) {
            $appointments = $this->makeAppointmentsDatetime($dayOfAppointments);
            // if string we transform it into DateTime, else we except a null result so we set expected to null
            $expected = is_string($dayOfAppointments['expected']) ? \DateTimeImmutable::createFromFormat('H:i', $dayOfAppointments['expected']) : null;
            $getLunchTimeBeginHour = new GetLunchTimeBeginHour();
            $actualResult = $getLunchTimeBeginHour->execute($appointments, $minLunchTime, $maxLunchTime, self::DURATION_LUNCH);

            $this->assertEquals(
                $expected,
                $actualResult,
                'Day of appointment #'.$dayOfAppointments['name'].': unexpected result');
        }
    }

    /**
     * Transform an array of strings to datetime begin and end of an appointment, avoiding unecessary data (name and result).
     */
    private function makeAppointmentsDatetime(array $appointments): array
    {
        foreach ($appointments as $key => $appointment) {
            if (is_array($appointment)) {
                $appointments[$key]['begin'] = \DateTimeImmutable::createFromFormat('H:i', $appointment[0]);
                $appointments[$key]['end'] = \DateTimeImmutable::createFromFormat('H:i', $appointment[1]);
            }
        }

        return $appointments;
    }

    private array $daysOfAppointments = [
        [
            'name' => '1',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['11:55', '12:12'],
            ['12:15', '12:20'],
            ['12:25', '12:29'],
            'expected' => '12:30',
        ],
        [
            'name' => '2',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['11:55', '12:12'],
            ['12:15', '12:20'],
            ['12:22', '12:24'],
            ['12:25', '12:29'],
            'expected' => '12:30',
        ],
        [
            'name' => '3',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['11:55', '11:58'],
            'expected' => '12:00',
        ],
        [
            'name' => '4',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['12:15', '12:25'],
            'expected' => '12:26',
        ],
        [
            'name' => '5',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['12:15', '12:25'],
            ['12:26', '12:27'],
            'expected' => '12:28',
        ],
        [
            'name' => '6',
            ['12:33', '12:40'],
            'expected' => '12:00',
        ],
        [
            'name' => '7',
            ['09:12', '9:44'],
            'expected' => '12:00',
        ],
        [
            'name' => '8',
            ['14:00', '15:00'],
            'expected' => '12:00',
        ],
        [
            'name' => '9',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['11:55', '12:12'],
            ['12:15', '12:20'],
            ['12:25', '12:30'],
            ['12:40', '12:50'],
            'expected' => null,
        ],
        [
            'name' => '10',
            ['09:12', '9:44'],
            ['09:50', '11:00'],
            ['12:20', '12:40'],
            'expected' => null,
        ],
        [
            'name' => '11',
            ['12:35', '12:40'],
            ['12:42', '12:48'],
            'expected' => '12:00',
        ],
        [
            'name' => '12',
            ['12:28', '12:40'],
            ['12:42', '12:48'],
            'expected' => null,
        ],
        [
            'name' => '13',
            ['12:12', '12:14'],
            ['12:21', '12:24'],
            'expected' => '12:25',
        ],
        [
            'name' => '14',
            ['11:50', '12:15'],
            ['12:28', '14:04'],
            'expected' => null,
        ],
        [
            'name' => '15',
            ['12:03', '12:04'],
            ['12:40', '14:04'],
            'expected' => '12:05',
        ],
        [
            'name' => '16',
            ['12:32', '12:35'],
            ['12:36', '14:40'],
            ['12:42', '12:50'],
            ['12:52', '12:55'],
            'expected' => '12:00',
        ],
        [
            'name' => '17',
            ['12:01', '12:02'],
            ['12:34', '12:35'],
            ['12:36', '14:40'],
            ['12:42', '12:50'],
            ['12:52', '12:55'],
            'expected' => '12:03',
        ],
        [
            'name' => '18',
            ['10:00', '11:00'],
            ['12:40', '12:45'],
            'expected' => '12:00',
        ],
        [
            'name' => '19',
            ['14:00', '15:00'],
            ['16:00', '17:00'],
            ['17:02', '17:10'],
            'expected' => '12:00',
        ],
    ];
}
