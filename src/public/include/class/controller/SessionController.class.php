<?php
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../DatabaseFactory.class.php';

/**
 * Class SessionController
 */
class SessionController
{
    /**
     * WENN SITZUNG HINZUGEFÜGT WURDE
     * (dann wird sie hier eingetragen)
     *
     * @param null $expId
     * @param null $tag
     * @param null $start
     * @param null $end
     * @param null $maxTeilnehmer
     * @param null $labId
     * @return mixed
     */
    public function create($expId = null, $tag = null, $start = null, $end = null, $maxTeilnehmer = null, $labId = null)
    {
        if (null !== $start && null !== $end) {
            try {
                $this->validateSessionTime($start, $end);
            } catch (\InvalidArgumentException $e) {
                trigger_error($e, E_USER_WARNING);
                return;
            }
        }

        $dbf = new DatabaseFactory();
        $mysqli = $dbf->get();
        
        $temp_day = formatDateForMysql($tag);
        
        $sql = sprintf(
            '
			INSERT INTO %1$s
			(`exp`, `tag`, `session_s`, `session_e`, `maxtn`, lab_id)
			VALUES (%2$d, "%3$s", "%4$s", "%5$s", %6$d, %7$d)
			ON DUPLICATE KEY UPDATE maxtn = maxtn + %6$d',
            TABELLE_SITZUNGEN,
            $expId,
            $temp_day,
            $start,
            $end,
            $maxTeilnehmer,
            $labId
        );
        
        $result = $mysqli->query($sql);
        if (false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Eintragen der Sitzung zu Experiment id %1$d', $expId));
        }
        $sitzungsId = $mysqli->insert_id;
        
        return $sitzungsId;
    }

    /**
     * WENN TERMIN VERÄNDERT WURDE
     *
     * @param $sitzungsId
     * @param null $expId
     * @param null $tag
     * @param null $start
     * @param null $end
     * @param null $maxTeilnehmer
     * @param null $labId
     */
    public function update($sitzungsId, $expId = null, $tag = null, $start = null, $end = null, $maxTeilnehmer = null, $labId = null)
    {
        if ($expId === null && $tag === null && $start === null && $end === null && $maxTeilnehmer === null && $labId === null) {
            return;
        }

        if (null !== $start && null !== $end) {
            try {
                $this->validateSessionTime($start, $end);
            } catch (\InvalidArgumentException $e) {
                trigger_error($e, E_USER_WARNING);
                return;
            }
        }

        $dbf = new DatabaseFactory();
        $mysqli = $dbf->get();
        
        $temp_day = formatDateForMysql($tag);
        
        $update = $updateOriginal = sprintf('UPDATE %1$s', TABELLE_SITZUNGEN);
        
        if ($expId !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET exp = %1$d' : ',exp = %1$d'), $expId);
        }
        if ($tag !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET tag = "%1$s"' : ',tag = "%1$s"'), $temp_day);
        }
        if ($start !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET session_s = "%1$s"' : ',session_s = "%1$s"'), $start);
        }
        if ($end !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET session_e = "%1$s"' : ',session_e = "%1$s"'), $end);
        }
        if ($maxTeilnehmer !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET maxtn = %1$d' : ',maxtn = %1$d'), $maxTeilnehmer);
        }
        if ($labId !== null) {
            $update .= sprintf(($update === $updateOriginal ? ' SET lab_id = %1$d' : ',lab_id = %1$d'), $labId);
        }
        
        $update .= sprintf(' WHERE id = %1$d', $sitzungsId);

        $result = $mysqli->query($update);
        if (false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Ändern der Sitzung mit id %1$d', $sitzungsId));
        }
    }

    /**
     * WENN TERMIN GELÖSCHT WURDE
     *
     * @param $sitzungsId
     */
    public function delete($sitzungsId)
    {
        $dbf = new DatabaseFactory();
        $mysqli = $dbf->get();

        $delete = sprintf(
            '
			DELETE FROM %1$s WHERE id = %2$d',
            TABELLE_SITZUNGEN,
            $sitzungsId
        );

        $result = $mysqli->query($delete);
        if (false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Löschen der Sitzung mit id %1$d', $sitzungsId));
        }
    
        $update = sprintf(
    
            '
			UPDATE %1$s SET termin = 0 WHERE termin = %2$d',
            TABELLE_VERSUCHSPERSONEN,
            $sitzungsId
        );
        
        $result = $mysqli->query($update);
        if (false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Aktualisieren der Daten von Versuchspersonen mit termin id %1$d', $$sitzungsId));
        }
    }

    /**
     * Vermeiden dass inkonsistente Daten in die Termintabelle geschrieben werden
     *
     * @param $start
     * @param $end
     */
    private function validateSessionTime($start, $end)
    {
        $dataTimeStart = $this->getDateTimeObject($start);
        $dataTimeEnd = $this->getDateTimeObject($end);
        if (false === $dataTimeStart || false === $dataTimeEnd || $dataTimeStart >= $dataTimeEnd) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Zeitslot kann nicht geschrieben werden, da der Startzeitpunkt (%1$s) zeitlich nach dem Endzeitpunkt (%2$s) liegt.',
                    $dataTimeStart instanceof \DateTime ? $dataTimeStart->format('Y-m-d\TH:i:s') : $start . ' -> ' . var_export($dataTimeStart, true),
                    $dataTimeEnd instanceof \DateTime ? $dataTimeEnd->format('Y-m-d\TH:i:s') : $end . ' -> ' . var_export($dataTimeEnd, true)
                )
            );
        }
    }

    private function getDateTimeObject($timeStr)
    {
        $colonCount = substr_count($timeStr, ':');
        if (1 === $colonCount) {
            return \DateTime::createFromFormat('H:i', $timeStr);
        } elseif (2 === $colonCount) {
            return \DateTime::createFromFormat('H:i:s', $timeStr);
        }
        return false;
    }
}
