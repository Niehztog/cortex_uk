<?php
require_once __DIR__ . '/include/functions.php';
require_once 'include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

/* WENN VP GELÖSCHT WURDE(N) */
/*---------------------------*/
if(isset($_POST['vpid']) && (isset($_POST['delvp'])||(isset($_POST['action']) && 'delete' === $_POST['action']))) {
	$delArray = castToIntArray($_POST['vpid']);

	$delete = sprintf( '
		DELETE FROM %1$s WHERE id IN (%2$s)'
		, TABELLE_VERSUCHSPERSONEN
		, implode(',', $delArray)
	);

	$ergebnis = $mysqli->query($delete) OR die($mysqli->error);
}




/* WENN VP EINRAG GEÄNDERT WURDE */
/*-------------------------------*/
if(isset($_POST['editvpbut'])) {

	$gebdat = formatDateForMysql($_POST['gebdat']);

	$update = sprintf( '
		UPDATE	`%1$s`
		SET		vorname = \'%2$s\',
				nachname = \'%3$s\',
				geschlecht = \'%4$s\',
				gebdat = \'%5$s\',
				fach = \'%6$s\',
				anschrift = \'%7$s\',
				telefon1 = \'%8$s\',
				telefon2 = \'%9$s\',
				email = \'%10$s\'
				%11$s
		WHERE	id = %12$d'
		, /*  1 */ TABELLE_VERSUCHSPERSONEN
		, /*  2 */ $mysqli->real_escape_string($_POST['vorname'])
		, /*  3 */ $mysqli->real_escape_string($_POST['nachname'])
		, /*  4 */ $mysqli->real_escape_string($_POST['geschlecht'])
		, /*  5 */ $mysqli->real_escape_string($gebdat)
		, /*  6 */ $mysqli->real_escape_string($_POST['fach'])
		, /*  7 */ $mysqli->real_escape_string($_POST['anschrift'])
		, /*  8 */ $mysqli->real_escape_string($_POST['telefon1'])
		, /*  9 */ $mysqli->real_escape_string($_POST['telefon2'])
		, /* 10 */ $mysqli->real_escape_string($_POST['email'])
		, /* 11 */ !isset($_POST['termin']) ? '' : ', termin = ' . $mysqli->real_escape_string($_POST['termin'])
		, /* 12 */ $_POST['vpid']
	);

	$ergebnis = $mysqli->query($update) OR die($mysqli->error);
}

if(isset($_POST['termin']) && isset($_POST['del_ch_vpid'])) {
	$update = sprintf( '
		UPDATE `%1$s` SET termin = \'%2$s\' WHERE id = %3$d'
		, TABELLE_VERSUCHSPERSONEN
		, $mysqli->real_escape_string($_POST['termin'])
		, $_POST['del_ch_vpid']
	);
	$ergebnis = $mysqli->query($update) OR die($mysqli->error);
}


/* ALLE VERSUCHSPERSONEN EINES EXPERIMENTES ANZEIGEN */  
/*---------------------------------------------------*/

require_once 'pageelements/header.php';

if(isset($_GET['expid'])) {
	$abfrage = "SELECT * FROM ".TABELLE_EXPERIMENTE." WHERE id=$_GET[expid] LIMIT 1";
	$erg = $mysqli->query($abfrage) or die($mysqli->error);
	$data = $erg->fetch_assoc();
	
	if(null !== $data) {
		?><h1><?= $data['exp_name'] ?>: Versuchspersonen</h1><?php
	}
}

require_once 'backend/ad_vp_list.php';

if(isset($_POST['vpmsgbut'])) {
	mail($_POST['absender'],"[Kopie] " . $_POST['betreff'],$letter,"from:" . $_POST['absender']);
}

require_once 'pageelements/footer.php';
?>
  