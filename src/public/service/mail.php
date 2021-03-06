<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

/* E-MAILS VERSCHICKEN */
/* ------------------- */
if(isset($_GET['betreff']) && isset($_GET['vpIdList'])) {

	$vpIdList = array_map('intval', explode(',', $_GET['vpIdList']));
	$letter_template = $_GET['content'];
	$letter_template = str_replace("<br>","\n",$letter_template);

	$stmtExp = $mysqli->prepare(
		sprintf( '
			SELECT		exp.exp_name,
						exp.exp_ort,
						exp.vl_name,
						exp.vl_tele,
						exp.vl_email
			FROM		%1$s AS exp
			WHERE		exp.id = ?'
			, TABELLE_EXPERIMENTE
		)
	);

	$stmtSes = $mysqli->prepare(
		sprintf( 'SELECT tag, session_s, session_e FROM %1$s WHERE id = ?', TABELLE_SITZUNGEN )
	);

	$sql = sprintf( '
		SELECT		exp,
					email,
					geschlecht,
					vorname,
					nachname,
					termin
		FROM		%1$s
		WHERE		id IN (%2$s)'
		, TABELLE_VERSUCHSPERSONEN
		, implode(',', $vpIdList)
	);
	$resultVp = $mysqli->query($sql);
	while($rowVp = $resultVp->fetch_assoc()) {

		$thisLetter = $letter_template;
		if ($rowVp['geschlecht'] == "männlich") { $thisLetter = str_replace("!liebe/r!", "Lieber", $thisLetter); }
		else if ($rowVp['geschlecht'] == "weiblich") { $thisLetter = str_replace("!liebe/r!", "Liebe", $thisLetter); }
		else {$thisLetter = str_replace("!liebe/r!", "Liebe/r", $thisLetter);}

		$thisLetter = str_replace("!vp_vorname!", $rowVp['vorname'], $thisLetter);
		$thisLetter = str_replace("!vp_nachname!", $rowVp['nachname'], $thisLetter);

		$stmtExp->bind_param('i', $rowVp['exp']);
		$stmtExp->execute();

		$rowExp = array();
		$stmtExp->bind_result($rowExp['exp_name'], $rowExp['exp_ort'], $rowExp['vl_name'], $rowExp['vl_tele'], $rowExp['vl_email']);
		$resultExp = $stmtExp->fetch();
		if(true === $resultExp) {
			$thisLetter = str_replace("!exp_name!", $rowExp['exp_name'], $thisLetter);
			$thisLetter = str_replace("!exp_ort!", $rowExp['exp_ort'], $thisLetter);

			$thisLetter = str_replace("!vl_name!", $rowExp['vl_name'], $thisLetter);
			$thisLetter = str_replace("!vl_telefon!", $rowExp['vl_tele'], $thisLetter);
			$thisLetter = str_replace("!vl_email!", $rowExp['vl_email'], $thisLetter);

			$vl_email = $rowExp['vl_email'];
		}
		elseif(false === $resultExp) {
			trigger_error($stmtExp->error, E_USER_WARNING);
		}
		$stmtExp->free_result();

		$stmtSes->bind_param('i', $rowVp['termin']);
		$stmtSes->execute();

		$rowSes = array();
		$stmtSes->bind_result($rowSes['tag'], $rowSes['session_s'], $rowSes['session_e']);
		$resultSes = $stmtSes->fetch();
		if(true === $resultSes) {
			$termin = formatMysqlDate($rowSes['tag']);
			$thisLetter = str_replace("!termin!", $termin, $thisLetter);
			$thisLetter = str_replace("!beginn!", substr($rowSes['session_s'], 0, 5), $thisLetter);
			$thisLetter = str_replace("!ende!", substr($rowSes['session_e'], 0, 5), $thisLetter);
		}
		elseif(false === $resultSes) {
			trigger_error($stmtSes->error, E_USER_WARNING);
		}
		$stmtSes->free_result();

		$header = "From:" . $vl_email . "\r\n" . "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";
		mail($rowVp['email'], $_GET['betreff'], $thisLetter, $header);
	
	}
	$stmtExp->close();
	$stmtSes->close();
}

/* TEILNEHMER EINES TERMINES AUFLISTEN */
/* ----------------------------------- */
elseif (!isset($_GET['betreff'])) {
	$abfrage = "SELECT id, vorname, nachname, email FROM ".TABELLE_VERSUCHSPERSONEN." WHERE exp='$_GET[expid]' AND termin='$_GET[termin]'" ;
	$erg = $mysqli->query($abfrage);
	$result = array();
	while ($data = $erg->fetch_assoc()) {  
		$result[] = array(
			'id'	=> $data['id'],
			'name'	=> substr($data['vorname'],0,1) . '. ' . $data['nachname'],
			'email'	=> $data['email']
		);
	}
    header('Content-type: application/json');
	echo json_encode($result);
}

