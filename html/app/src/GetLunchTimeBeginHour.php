<?php
declare(strict_types=1);

namespace App;

// Calculate the exact time when the technician should eat his lunchtime

use DateTimeImmutable;

class GetLunchTimeBeginHour
{
    /**
     * Calcule et renvoie l'heure de début de la pause déjeuner, en tenant compte des rendez-vous et de la plage de temps disponible.
     *
     * @param array $appointmentsOfTheDay
     * @param DateTimeImmutable $minLunchTime Heure minimale pour commencer la pause déjeuner.
     * @param DateTimeImmutable $maxLunchTime Heure maximale pour commencer la pause déjeuner.
     * @param int $lunchDuration Durée de la pause déjeuner en minutes.
     * @return DateTimeImmutable|null Heure de début de la pause ou null si aucun créneau n'est disponible.
     * @throws \DateMalformedIntervalStringException
     */
    public function execute(array $appointmentsOfTheDay, DateTimeImmutable $minLunchTime, DateTimeImmutable $maxLunchTime, int $lunchDuration): ?DateTimeImmutable
    {

        // Garde les rendez-vous qui sont dans la plage du déjeuner
        $appointmentsOfTheDay = array_filter($appointmentsOfTheDay, function ($appointment) use ($minLunchTime, $maxLunchTime) {

            return $appointment['end'] > $minLunchTime && $appointment['begin'] < $maxLunchTime;
        });
        
        // Trie les rendez-vous par ordre chronologique
        usort($appointmentsOfTheDay, function ($a, $b) {
            return $a['begin'] <=> $b['begin'];
        });

        // On choisit de faire commencer le déjeuner au plus tôt possible dans un 1er temps
        $LunchBegin = $minLunchTime;
        $lunchDurationInterval = new \DateInterval('PT' . $lunchDuration . 'M');

        foreach ($appointmentsOfTheDay as $appointment) {

            $appointmentStart = $appointment['begin'];
            $appointmentEnd = $appointment['end'];

            $LunchEnd = $LunchBegin->add($lunchDurationInterval);

            // Vérifie si la pause déjeuner peut se terminer avant le début du rendez-vous actuel
            if ($LunchEnd < $appointmentStart) {
                // Si le déjeuner peut finir avant le RDV, c'est un créneau valide
                return $LunchBegin;
            }

            //Vérifie si l'heure de début du déjeuner est avant la fin du rendez-vous actuel.
            // Sinon, on décale le début de la pause d'une min après le RDV
            if ($LunchBegin < $appointmentEnd) {
                $LunchBegin = $appointmentEnd->add(new \DateInterval('PT1M'));

            }

            // Vérifie juste si le déjeuner commence après 13h
            if ($LunchBegin >= $maxLunchTime) {
                return null;
            }
        }

        // on sait que le déjeuner commence avant 13h, mais on check qu'on a bien 30 min avant 13h
        $LunchEnd = $LunchBegin->add($lunchDurationInterval);
        if ($LunchEnd <= $maxLunchTime) {
            return $LunchBegin;
        }

        return null; // Pas de créneau disponible
    }
}
