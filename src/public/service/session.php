<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
require_once __DIR__ . '/../include/class/controller/SessionController.class.php';
require_once __DIR__ . '/../include/class/ExperimentDataProvider.class.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/calendar/CalendarDataProvider.class.php';

$auth = new AccessControl();
if (!$auth->mayAccessExpControl() || !isset($_REQUEST['action'])) {
    header('Content-type: application/json');
    echo json_encode(false);
    exit;
}

$result = 'success';
$action = $_REQUEST['action'];

try {
    if('calendar' === $action && !empty($_GET['expid'])) {
        $result = array();
		try {
			$cdp = new CalendarDataProvider();
			$calendarData = $cdp->getBookedSessionsFor((int)$_GET['expid']);
		}
		catch(\Exception $e) {
			trigger_error($e, E_USER_WARNING);
			throw $e;
		}
		if(!empty($calendarData)) {
			foreach($calendarData as $termin) {
                $result[] = json_decode((string)$termin);
			}
		}
    }
    elseif ('list' === $action && !empty($_REQUEST['expid'])) {
        $dbf = new DatabaseFactory();
        $mysqli = $dbf->get();
        $result = array('aaData' => array());

        $expId = (int) $_REQUEST['expid'];
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
            if ('automatisch'===$data['terminvergabemodus'] && 0 === $erg3->num_rows) {
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
    }


    /* WENN TERMIN GELÖSCHT WURDE */
    /* -------------------------- */
    elseif ('delete' === $action && !empty($_POST['id'])) {
        $sc = new SessionController();
        $sc->delete($_POST['id']);
    }


    /* WENN TERMIN VERÄNDERT WURDE */
    /* --------------------------- */
    elseif ('change' === $action && !empty($_POST['expid'])) {
        $sc = new SessionController();
        $sc->update(
            $_POST['change_id'],
            $_POST['expid'],
            $_POST['change_termin'],
            $_POST['change_session_s'],
            $_POST['change_session_e'],
            $_POST['change_maxtn']
        );
    }


    /* WENN TERMIN HINZUGEFÜGT WURDE  */
    /* (dann wird sie hier eingetragen)*/
    /* ------------------------------- */
    elseif ('add' === $action && !empty($_POST['expid'])) {
        $sc = new SessionController();
        $sc->create(
            $_POST['expid'],
            $_POST['hinzu_termin'],
            $_POST['hinzu_session_s'],
            $_POST['hinzu_session_e'],
            $_POST['hinzu_maxtn']
        );
    }
} catch (\Exception $e) {
    $result = $e->getMessage();
}

header('Content-type: application/json');
echo json_encode($result);
