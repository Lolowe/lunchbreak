<?php
declare(strict_types=1);

// Calculate the exact time when the technician should eat his lunchtime
class GetLunchTimeBeginHour
{
    /*
     * $appointmentsIntervalList is an array of appointments in one day like:
     *  [
           ['begin' => \DateTimeImmutable '09:50', ['end'] => \DateTimeImmutable '11:00'],
           ['begin' => \DateTimeImmutable '11:55', ['end'] => \DateTimeImmutable '12:12'],
           ['begin' => \DateTimeImmutable '12:15', ['end'] => \DateTimeImmutable '12:20']
        ]
    */
    public function execute(array $appointmentsIntervalList, \DateTimeImmutable $minLunchTime, \DateTimeImmutable $maxLunchTime, int $lunchTimeDuration): ?\DateTimeImmutable
    {
        // order array by begin Hour
        array_multisort($appointmentsIntervalList);

        foreach ($appointmentsIntervalList as $key => $appointment) {
            if (is_array($appointment)) {
                $nextKey = $appointmentsIntervalList[$key + 1] ?? null;
                $previousKey = $appointmentsIntervalList[$key - 1] ?? null;
                $nextKeyExist = array_key_exists($key + 1, $appointmentsIntervalList);
                $previousKeyExist = array_key_exists($key - 1, $appointmentsIntervalList);

                // check if there are appoinments between minLunchTime and maxLunchTime
                if ($this->isAppointmentOnLunchTimeInterval($appointment, $minLunchTime, $maxLunchTime)) {
                    if ($appointment['begin'] < $minLunchTime) {
                        if ($appointment['end'] > $maxLunchTime) {
                            if (!$nextKeyExist) {
                                return null;
                            }
                        } elseif ($this->datePlusMinutes($appointment['end'], $lunchTimeDuration) < $nextKey['begin']) {
                            return $appointment['end'];
                        }
                    } else {
                        if ($this->datePlusMinutes($minLunchTime, $lunchTimeDuration) < $appointment['begin']) {
                            if ($previousKeyExist && $previousKey['begin'] > $minLunchTime) {
                            } else {
                                return $minLunchTime;
                            }
                        } elseif ($appointment['end'] < $maxLunchTime) {
                            // il reste bien le durationLunch avant le maxLunchTime
                            if ($this->datePlusMinutes($appointment['end'], 30) < $maxLunchTime) {
                                // si pas d'autre appointment après ou s'il y en a un( pas besoin de check car on vient de tester l'inverse avec !$nextKeyExist),
                                // mais qu'il est assez loin
                                if (!$nextKeyExist || $this->datePlusMinutes($appointment['end'], 30) < $nextKey['begin']) {
                                    return $this->datePlusMinutes($appointment['end'], 1);
                                }
                            }
                        } else {
                            return null;
                        }
                    }
                    // if no appointments on the lunchtime interval, the lunchtime can become at the initial time
                } elseif (!$nextKeyExist) {
                    return $minLunchTime;
                }
            }
        }

        return null;
    }

    // add minutes to a datetime
    private function datePlusMinutes(\DateTimeImmutable $time, int $minutes)
    {
        return $time->add(new \DateInterval('PT' . $minutes . 'M'));
    }

    private function isAppointmentOnLunchTimeInterval(array $appointment, \DateTimeImmutable $minLunchTime, \DateTimeImmutable $maxLunchTime): bool
    {
        if ($appointment['end'] > $minLunchTime && $appointment['end'] < $maxLunchTime || $appointment['end'] > $maxLunchTime && $appointment['begin'] < $maxLunchTime) {
            return true;
        }

        return false;
    }
}
