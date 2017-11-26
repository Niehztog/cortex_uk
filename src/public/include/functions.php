<?php
//Konstanten für meldungs boxen
define('MESSAGE_BOX_ERROR', '<div class="ui-state-error"><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span>%s</div>');
define('MESSAGE_BOX_SUCCESSR', '<div class="ui-state-highlight"><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>%s</div>');

if(function_exists('formatMysqlDate')) {
    return;
}

function formatMysqlDate($mysqlDate) {
	if('0000-00-00' === substr($mysqlDate, 0, 10)) {
		return '';
	}
	return substr($mysqlDate, 8, 2) . "." . substr($mysqlDate, 5, 2) . "." . substr($mysqlDate, 0, 4);
}

function formatDateForMysql($date) {
	if(empty($date)) {
		return '0000-00-00';
	}
	return substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
}

function storeMessageInSession($msg) {
	initSession();
	if(!empty($_SESSION['displayMessage'])) {
		$_SESSION['displayMessage'] .= $msg;
	}
	else {
		$_SESSION['displayMessage'] = $msg;
	}
}

function displayMessagesFromSession() {
	if(initSession(true) && !empty($_SESSION['displayMessage'])) {
		echo $_SESSION['displayMessage'];
		unset($_SESSION['displayMessage']);
	}
}

function initSession($onlyExisting = false) {
	if($onlyExisting && !isset($_COOKIE['PHPSESSID']) && '' === session_id()) {
		return false;
	}
	if('' === session_id()) {
		session_start();
	}
	return true;
}

/**
 * Wird benutzt um klartext zeitangaben von fullcalendar in einen
 * zeitstempel zu konvertieren. dabei wird die zeitzonenangabe des
 * eingabe strings bei der konvertierung ignoriert, bzw. so getan, als
 * ob die zeitangabe in der lokalen zeitzone vorlänge
 * 
 * @param string $timeStr
 * @return int
 */
function strtotimeIgnoreTimeZone($timeStr) {
	$dateTime = new DateTime($timeStr, new DateTimeZone('UTC'));
	$tzCurrent = new DateTimeZone(date_default_timezone_get());
	$tzOffset = $tzCurrent->getOffset($dateTime);
	return $dateTime->getTimestamp()-$tzOffset;
}

function prepareIncomingVariables() {
	$GLOBALS['vpn_gebdat'] = isset($_POST['vpn_gebdat'])?$_POST['vpn_gebdat']:null;
	$GLOBALS['vpn_fach'] = isset($_POST['vpn_fach'])?$_POST['vpn_fach']:null;
	$GLOBALS['vpn_semester'] = isset($_POST['vpn_semester'])?$_POST['vpn_semester']:null;
	$GLOBALS['vpn_adresse'] = isset($_POST['vpn_adresse'])?$_POST['vpn_adresse']:null;
	$GLOBALS['vpn_tele1'] = isset($_POST['vpn_tele1'])?$_POST['vpn_tele1']:null;
	$GLOBALS['vpn_tele2'] = isset($_POST['vpn_tele2'])?$_POST['vpn_tele2']:null;
	$GLOBALS['vpn_email'] = isset($_POST['vpn_email'])?$_POST['vpn_email']:null;
	$GLOBALS['vl_name'] =  htmlspecialchars($_POST['vl_name'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['vl_tele'] =  htmlspecialchars($_POST['vl_tele'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['vl_email'] =  htmlspecialchars($_POST['vl_email'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['exp_name'] = htmlspecialchars($_POST['exp_name'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['exp_zusatz'] = htmlspecialchars($_POST['exp_zusatz'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['exp_mail'] = htmlspecialchars($_POST['exp_mail'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['exp_ort'] = htmlspecialchars($_POST['exp_ort'], ENT_QUOTES, 'UTF-8');
	$GLOBALS['exp_start'] = isset($_POST['exp_start'])?date_parse_from_format('d.m.Y', $_POST['exp_start']):null;
	$GLOBALS['exp_end'] = isset($_POST['exp_end'])?date_parse_from_format('d.m.Y', $_POST['exp_end']):null;
	
	if( isset($_POST['vpn_gebdat_o'])&&$_POST['vpn_gebdat_o'] == 1 ) { $GLOBALS['vpn_gebdat'] = 2; };
	if( !isset($_POST['vpn_gebdat'])||$_POST['vpn_gebdat'] == 0 ) { $GLOBALS['vpn_gebdat'] = 0; };
	if( isset($_POST['vpn_fach_o'])&&$_POST['vpn_fach_o'] == 1 ) { $GLOBALS['vpn_fach'] = 2; };
	if( !isset($_POST['vpn_fach'])||$_POST['vpn_fach'] == 0 ) { $GLOBALS['vpn_fach'] = 0; };
	if( isset($_POST['vpn_semester_o'])&&$_POST['vpn_semester_o'] == 1 ) { $GLOBALS['vpn_semester'] = 2; };
	if( !isset($_POST['vpn_semester'])||$_POST['vpn_semester'] == 0 ) { $GLOBALS['vpn_semester'] = 0; };
	if( isset($_POST['vpn_adresse_o'])&&$_POST['vpn_adresse_o'] == 1 ) { $GLOBALS['vpn_adresse'] = 2; };
	if( !isset($_POST['vpn_adresse'])||$_POST['vpn_adresse'] == 0 ) { $GLOBALS['vpn_adresse'] = 0; };
	if( isset($_POST['vpn_tele1_o'])&&$_POST['vpn_tele1_o'] == 1 ) { $GLOBALS['vpn_tele1'] = 2; };
	if( !isset($_POST['vpn_tele1'])||$_POST['vpn_tele1'] == 0 ) { $GLOBALS['vpn_tele1'] = 0; };
	if( isset($_POST['vpn_tele2_o'])&&$_POST['vpn_tele2_o'] == 1 ) { $GLOBALS['vpn_tele2'] = 2; };
	if( !isset($_POST['vpn_tele2'])||$_POST['vpn_tele2'] == 0 ) { $GLOBALS['vpn_tele2'] = 0; };
	if( isset($_POST['vpn_email_o'])&&$_POST['vpn_email_o'] == 1 ) { $GLOBALS['vpn_email'] = 2; };
	if( !isset($_POST['vpn_email'])||$_POST['vpn_email'] == 0 ) { $GLOBALS['vpn_email'] = 0; };
}

function validateExpData() {
	$result = true;
	if(		isset($_POST['terminvergabemodus']) && 'automatisch' === $_POST['terminvergabemodus']
		&&	empty($_POST['session_duration'])
	) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Wenn automatische Terminvergabe gewählt wurde muss die Sitzungsdauer angegeben werden.'));
		$result = false;
	}

    if('automatisch' === $_POST['terminvergabemodus'] && empty($_POST['lab_id'])) {
        storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Wenn automatische Terminvergabe gewählt wurde muss mindestens ein Raum ausgewählt werden.'));
        $result = false;
    }
	
	if(!empty($_POST['exp_start']) && !empty($_POST['exp_end'])) {
		$start = DateTime::createFromFormat('d.m.Y', $_POST['exp_start']);
		$end = DateTime::createFromFormat('d.m.Y', $_POST['exp_end']);
		if(false !== $start && false !== $end && $start > $end) {
			storeMessageInSession(
				sprintf(
					MESSAGE_BOX_ERROR,
					sprintf(
						'Der Beginn des Experiments (%1$s) kann nicht nach dem Ende (%2$s) liegen.'
						, $start->format('d.m.Y')
						, $end->format('d.m.Y')
					)	
				)
			);
			$result = false;
		}
	}
	return $result;
}

function castToIntArray($idParam) {
	$arr = array();
	if(is_array($idParam)) {
		foreach($idParam as $id) {
			$arr[] = (int) $id;
		}
	}
	else {
		$arr[] = (int) $idParam;
	}
	return $arr;
}