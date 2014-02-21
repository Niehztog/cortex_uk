<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php

if (isset($_GET['betreff'])) {
	
	$letter_template = $_GET['content'];
	$letter_template = str_replace("<br>","\n",$letter_template);
	
	
	$abfrage = "SELECT * FROM ".TABELLE_EXPERIMENTE." WHERE id='$_GET[expid]'" ;
	$erg = $mysqli->query($abfrage);
	$data = $erg->fetch_assoc();
	if(null !== $data) {
		$letter_template = str_replace("!exp_name!",$data['exp_name'],$letter_template);
		$letter_template = str_replace("!exp_ort!",$data['exp_ort'],$letter_template);
		
		$letter_template = str_replace("!vl_name!",$data['vl_name'],$letter_template);
		$letter_template = str_replace("!vl_telefon!",$data['vl_tele'],$letter_template);
		$letter_template = str_replace("!vl_email!",$data['vl_email'],$letter_template);
		
		$vl_email = $data['vl_email'];
	}
	
	$abfrage = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE id='$_GET[termin]'" ;
	$erg = $mysqli->query($abfrage);
	$data = $erg->fetch_assoc();
	if(null !== $data) {
		$termin = formatMysqlDate($data['tag']);
		$letter_template = str_replace("!termin!",$termin,$letter_template);
		$letter_template = str_replace("!beginn!",substr($data['session_s'],0,5),$letter_template);
		$letter_template = str_replace("!ende!",substr($data['session_e'],0,5),$letter_template);
	}
	
	$abfrage = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE exp='$_GET[expid]' AND termin='$_GET[termin]'" ;
	$erg = $mysqli->query($abfrage);
	while ($data = $erg->fetch_assoc()) {
		$letter = $letter_template;
		if ($data['geschlecht'] == "männlich") { $letter = str_replace("!liebe/r!","Lieber",$letter); }
		else if ($data['geschlecht'] == "weiblich") { $letter = str_replace("!liebe/r!","Liebe",$letter); }
		else {$letter = str_replace("!liebe/r!","Liebe/r",$letter);}
		
		$letter = str_replace("!vp_vorname!",$data['vorname'],$letter);
		$letter = str_replace("!vp_nachname!",$data['nachname'],$letter);
		
		$header = "From:" . $vl_email . "\r\n" . "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";
		mail($data['email'],$_GET['betreff'],$letter,$header);  
	
	}
}
elseif (!isset($_GET['betreff']) && !isset($_GET['tn'])) {
	$counter = 0;
	
	$abfrage = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE exp='$_GET[expid]' AND termin='$_GET[termin]'" ;
	$erg = $mysqli->query($abfrage);
	while ($data = $erg->fetch_assoc()) {  
		if ( $counter > 0 ) { echo ', '; }
		echo substr($data['vorname'],0,1) . '. ' . $data['nachname'] . ' (' . $data['email'] . ')'; 	
		$counter++;
	}

}
elseif (isset($_GET['tn'])) {

	$counter = 0;
	
	$abfrage = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE exp='$_GET[expid]' AND termin='$_GET[termin]'" ;
	$erg = $mysqli->query($abfrage);
	while ($data = $erg->fetch_assoc()) {  
		$counter++;
	}
	
	echo $counter;

}

?>
</body>
</html>