<?php
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../user/AccessControl.class.php';
require_once __DIR__ . '/../controller/SessionController.class.php';
require_once __DIR__ . '/../ExperimentDataProvider.class.php';
require_once __DIR__ . '/../DatabaseFactory.class.php';
require_once __DIR__ . '/../calendar/CalendarDataProvider.class.php';

class SessionService {

    const PURPOSE_VIEW = 'viewexp';
    const PURPOSE_MANAGE = 'manage';
    const PURPOSE_SIGNUP = 'signup';

    /**
     * @param $id
     * @param $purpose
     * @return array
     * @throws Exception
     */
    public function getDataForCalendar($id, $purpose)
    {
        if(empty($id) || empty($purpose)) {
            throw new \InvalidArgumentException(sprintf('missing required parameter [%1$s]', var_export([$id, $purpose], true)));
        }

        $result = array();
        $cdp = new CalendarDataProvider();
        switch ($purpose) {

            case self::PURPOSE_VIEW:
                try {
                    $calendarData = $cdp->getBookedSessionsFor((int)$id);
                } catch (\Exception $e) {
                    trigger_error($e, E_USER_WARNING);
                    throw $e;
                }
                break;

            case self::PURPOSE_MANAGE:
                try {
                    $calendarData = $cdp->getOpeningHoursFor((int)$id);
                    $calendarData->unite(
                        $cdp->getAllocationData(
                            (int)$id,
                            null,
                            array(
                                'setEditable' => false,
                                'setBackgroundColor' => '#F3F781'
                            )
                        )
                    );
                } catch (\Exception $e) {
                    trigger_error($e, E_USER_WARNING);
                    throw $e;
                }
                break;

            case self::PURPOSE_SIGNUP:
                try {
                    $calendarData = $cdp->getAvailableSessionsFor((int)$id);
                } catch (\Exception $e) {
                    trigger_error($e, E_USER_WARNING);
                    throw new \RuntimeException(sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__));
                }
                break;

            default:
                throw new \InvalidArgumentException(sprintf('unknown value for purpose (%1$s)', var_export($purpose, true)));
        }

        if (!empty($calendarData)) {
            $result = $calendarData->asArray();
        }

        return $result;
    }

    /**
     * @param $expId
     * @return array
     */
    public function getDataForTable($expId)
    {
        if(empty($expId)) {
            throw new \InvalidArgumentException(sprintf('missing required parameter [%1$s]', var_export([$expId], true)));
        }

        $dbf = new DatabaseFactory();
        $mysqli = $dbf->get();
        $result = array('aaData' => array());

        $expId = (int)$expId;
        $edp = new ExperimentDataProvider($expId);
        $data = $edp->getExpData();

        $abfrage = sprintf(
            '
        SELECT		ses.id,
                    ses.tag,
                    ses.session_s,
                    ses.session_e,
                    ses.maxtn,
                    lab.label AS ort
        FROM		%1$s AS ses
        LEFT JOIN	%2$s AS lab
            ON		ses.lab_id = lab.id
        WHERE		ses.exp = %3$d
        ORDER BY	ses.tag ASC, ses.session_s ASC',
            TABELLE_SITZUNGEN,
            TABELLE_LABORE,
            $expId
        );
        $erg = $mysqli->query($abfrage);
        $anmeldungenInsgesamt = 0;
        while ($termin = $erg->fetch_assoc()) {
            $resultRow = array();

            $terminDatum = formatMysqlDate($termin['tag']);
            $beginn = substr($termin['session_s'], 0, 5);
            $ende = substr($termin['session_e'], 0, 5);
            $maxtn = $termin['maxtn'];

            $abfrage3 = sprintf(
                '
            SELECT  gebdat,
                    vorname,
                    nachname,
                    geschlecht,
                    telefon1,
                    telefon2,
                    email,
                    vorname,
                    nachname
            FROM    %1$s
            WHERE   exp = %2$d
              AND   termin = %3$d',
                TABELLE_VERSUCHSPERSONEN,
                $expId,
                $termin['id']
            );
            $erg3 = $mysqli->query($abfrage3);
            if ('automatisch' === $data['terminvergabemodus'] && 0 === $erg3->num_rows) {
                continue;
            }

            $resultRow[0] = $termin['id'];
            $resultRow[1] = $terminDatum;
            $resultRow[2] = $beginn;
            $resultRow[3] = $ende;
            $resultRow[4] = empty($termin['ort']) ? '' : $termin['ort'];

            $vpcur = $erg3->num_rows;
            $anmeldungenInsgesamt += $vpcur;
            $colVp = $vpcur;
            if ((empty($termin['ort']) && $maxtn > 0) || $maxtn > $vpcur) {
                $colVp .= '/' . $maxtn;
            }
            $resultRow[5] = $colVp;

            $gebdat = null;

            $resultRow[6] = '';
            $resultRow[7] = array();
            $allNames = '';
            while ($termin3 = $erg3->fetch_assoc()) {
                $vpListItem = array();
                if (!empty($gebdat)) {
                    echo '<br/>';
                }
                $gebdat = formatMysqlDate($termin3['gebdat']);
                $vpListItem['title'] = array();
                $vpListItem['title']['Name'] = $termin3['vorname'] . ' ' . $termin3['nachname'];
                if ($data['vpn_geschlecht'] > 0) {
                    $vpListItem['title']['Geschlecht'] = $termin3['geschlecht'];
                }
                if ($data['vpn_gebdat'] > 0) {
                    $vpListItem['title']['Geburtsdatum'] = $gebdat;
                }
                if ($data['vpn_tele1'] > 0) {
                    $vpListItem['title']['Telefon 1'] = $termin3['telefon1'];
                }
                if ($data['vpn_tele2'] > 0) {
                    $vpListItem['title']['Telefon 2'] = $termin3['telefon2'];
                }
                if ($data['vpn_email'] > 0) {
                    $vpListItem['title']['Email'] = $termin3['email'];
                }

                $vpListItem['name'] = substr($termin3['vorname'], 0, 1) . '. ' . $termin3['nachname'];
                $allNames .= empty($allNames) ? $vpListItem['name'] : '<br/>' . $vpListItem['name'];

                $resultRow[6] = $allNames;
                $resultRow[7][] = $vpListItem;
            }

            $result['aaData'][] = $resultRow;
        }

        return $result;
    }

    /**
     * WENN TERMIN GELÖSCHT WURDE
     *
     * @param $id
     * @return bool|mysqli_result
     */
    public function delete($id)
    {
        if(empty($id)) {
            throw new \InvalidArgumentException(sprintf('missing required parameter [%1$s]', var_export([$id], true)));
        }
        $sc = new SessionController();
        return $sc->delete((int)$id);
    }


    /**
     * WENN TERMIN VERÄNDERT WURDE
     *
     * @param $expId
     * @param $changeId
     * @param $changeTermin
     * @param $changeSessionStart
     * @param $changeSessionEnd
     * @param $changeMaxTn
     * @return bool|mysqli_result|void
     */
    public function change($expId, $changeId, $changeTermin, $changeSessionStart, $changeSessionEnd, $changeMaxTn)
    {
        if(empty($expId)) {
            throw new \InvalidArgumentException(sprintf('missing required parameter [%1$s]', var_export([$expId], true)));
        }

        $sc = new SessionController();
        return $sc->update(
            $changeId,
            (int)$expId,
            $changeTermin,
            $changeSessionStart,
            $changeSessionEnd,
            $changeMaxTn
        );

    }

    /**
     * WENN TERMIN HINZUGEFÜGT WURDE
     * (dann wird sie hier eingetragen)
     *
     * @param $expId
     * @param $hinzuTermin
     * @param $hinzuSessionStart
     * @param $hinzuSessionEnd
     * @param $hinzuMaxTn
     * @return mixed
     */
    public function add($expId, $hinzuTermin, $hinzuSessionStart, $hinzuSessionEnd, $hinzuMaxTn) {
        if(empty($expId)) {
            throw new \InvalidArgumentException(sprintf('missing required parameter [%1$s]', var_export([$expId], true)));
        }

        $sc = new SessionController();
        return $sc->create(
            (int)$expId,
            $hinzuTermin,
            $hinzuSessionStart,
            $hinzuSessionEnd,
            $hinzuMaxTn
        );

    }

}