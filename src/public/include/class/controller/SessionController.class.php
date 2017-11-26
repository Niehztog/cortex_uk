<?php
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../DatabaseFactory.class.php';

/**
 * Class SessionController
 */
class SessionController {
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
	public function create($expId = null, $tag = null, $start = null, $end = null, $maxTeilnehmer = null, $labId = null) {
		
		$dbf = new DatabaseFactory();
		$mysqli = $dbf->get();
		
		$temp_day = formatDateForMysql($tag);
		
		$sql = sprintf( '
			INSERT INTO %1$s
			(`exp`, `tag`, `session_s`, `session_e`, `maxtn`, lab_id)
			VALUES (%2$d, "%3$s", "%4$s", "%5$s", %6$d, %7$d)
			ON DUPLICATE KEY UPDATE maxtn = maxtn + %6$d'
			, TABELLE_SITZUNGEN
			, $expId
			, $temp_day
			, $start
			, $end
			, $maxTeilnehmer
			, $labId
		);
		
		$result = $mysqli->query($sql);
		if(false === $result) {
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
	public function update($sitzungsId, $expId = null, $tag = null, $start = null, $end = null, $maxTeilnehmer = null, $labId = null) {

		if($expId === null && $tag === null && $start === null && $end === null && $maxTeilnehmer === null && $labId === null) {
			return;
		}
		
		$dbf = new DatabaseFactory();
		$mysqli = $dbf->get();
		
		$temp_day = formatDateForMysql($tag);
		
		$update = $updateOriginal = sprintf('UPDATE %1$s', TABELLE_SITZUNGEN);
		
		if($expId !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET exp = %1$d' : ',exp = %1$d'), $expId);
		}
		if($tag !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET tag = "%1$s"' : ',tag = "%1$s"'), $temp_day);
		}
		if($start !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET session_s = "%1$s"' : ',session_s = "%1$s"'), $start);
		}
		if($end !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET session_e = "%1$s"' : ',session_e = "%1$s"'), $end);
		}
		if($maxTeilnehmer !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET maxtn = %1$d' : ',maxtn = %1$d'), $maxTeilnehmer);
		}
		if($labId !== null) {
			$update .= sprintf(($update === $updateOriginal ? ' SET lab_id = %1$d' : ',lab_id = %1$d'), $labId);
		}
		
		$update .= sprintf(' WHERE id = %1$d', $sitzungsId);

		$result = $mysqli->query($update);
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim Ändern der Sitzung mit id %1$d', $sitzungsId));
		}

	}

    /**
	 * WENN TERMIN GELÖSCHT WURDE
     *
     * @param $sitzungsId
     */
	public function delete($sitzungsId) {

		$dbf = new DatabaseFactory();
		$mysqli = $dbf->get();
		
		$expId = $this->getExpIdBySitzungsId($sitzungsId);
	
		$delete = sprintf( '
			DELETE FROM %1$s WHERE id = %2$d'
			, TABELLE_SITZUNGEN
			, $sitzungsId
		);

		$result = $mysqli->query($delete);
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim Löschen der Sitzung mit id %1$d', $sitzungsId));
		}
	
		$update = sprintf( '
			UPDATE %1$s SET termin = 0 WHERE termin = %2$d'
			, TABELLE_VERSUCHSPERSONEN
			, $sitzungsId
		);
		
		$result = $mysqli->query($update);
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim Aktualisieren der Daten von Versuchspersonen mit termin id %1$d', $$sitzungsId));
		}

	}

    /**
     * @param $sitzungsId
     * @return int
     */
	private function getExpIdBySitzungsId($sitzungsId) {
		
		$dbf = new DatabaseFactory();
		$mysqli = $dbf->get();
		
		$sql = sprintf( '
			SELECT exp FROM %1$s WHERE id = %2$d'
			, TABELLE_SITZUNGEN
			, $sitzungsId
		);
		$result = $mysqli->query($sql);
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim lesen der Daten von Sitzungen %1$d.', $sitzungsId));
		}
		$data = $result->fetch_assoc();
		if(null === $data) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Sitzung mit id %1$d existiert nicht.', $sitzungsId));
		}
		return (int)$data['exp'];
	}

}