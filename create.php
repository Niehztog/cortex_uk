<html>
<head>
<title>CORTEX Creator</title>
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>menu.css">
<link rel="stylesheet" href="css/<?php if(CSS_OLD_STYLE) {echo 'old/';}?>calender.css">
</head>
<body>
<div style="text-align:center">
<br /><br /><br /><br /><br />
<h1 style="text-align:center;">Cortex Database Creator</h1>
<?
if (!isset($_POST['createme']))
  {
?>  
<font color="#FF0000"><b>Achtung: es wird empfohlen vor dem Ausführen ein Backup ggf. vorhandener Daten zu machen.</b></font>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="create">
<input type="submit" value="Create database" name="createme" />
</form>
<?
  }
?>  



<?
die('work in progress');
include("db.php");

if ( isset($_POST['createme']) )
{
	$createTableQueries = array(
			
		TABELLE_EXPERIMENTE =>
			"CREATE TABLE ".TABELLE_EXPERIMENTE." (
			`id` int(9) NOT NULL auto_increment,
			`vl_name` varchar(255) collate utf8_unicode_ci NOT NULL,
			`vl_tele` varchar(255) collate utf8_unicode_ci NOT NULL,
			`vl_email` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_name` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_ort` text collate utf8_unicode_ci NOT NULL,
			`exp_vps` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_vpsnum` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_geld` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_geldnum` varchar(255) collate utf8_unicode_ci NOT NULL,
			`exp_zusatz` text collate utf8_unicode_ci NOT NULL,
			`exp_mail` text collate utf8_unicode_ci NOT NULL,
			`exp_start` date DEFAULT NULL,
			`exp_end` date DEFAULT NULL,
			`vpn_name` int(1) NOT NULL,
			`vpn_geschlecht` int(1) NOT NULL,
			`vpn_gebdat` int(1) NOT NULL,
			`vpn_fach` int(1) NOT NULL,
			`vpn_semester` int(1) NOT NULL,
			`vpn_adresse` int(1) NOT NULL,
			`vpn_tele1` int(1) NOT NULL,
			`vpn_tele2` int(1) NOT NULL,
			`vpn_email` int(1) NOT NULL,
			`vpn_ifreward` int(1) NOT NULL,
			`vpn_ifbereits` int(1) NOT NULL,
			`vpn_ifbenach` int(1) NOT NULL,
			`visible` int(1) NOT NULL,
			`max_vp` smallint(5) unsigned NOT NULL,
			`terminvergabemodus` enum('automatisch','manuell') NOT NULL DEFAULT 'manuell',
			`show_in_list` ENUM('true','false') NOT NULL DEFAULT 'true',
			`session_duration` INT UNSIGNED NOT NULL DEFAULT '0',
			`max_simultaneous_sessions` SMALLINT(5) UNSIGNED NOT NULL,
			PRIMARY KEY(`id`)
			) ENGINE=MyISAM ;",
	
		TABELLE_SITZUNGEN =>
			"CREATE TABLE ".TABELLE_SITZUNGEN." (
			`id` int(4) NOT NULL auto_increment,
			`exp` int(3) NOT NULL,
			`tag` date NOT NULL,
			`session_s` time NOT NULL,
			`session_e` time NOT NULL,
			`maxtn` int(3) NOT NULL,
			`virtualtn` int(3) NOT NULL,
			`remind` int(1) NOT NULL,
			`lab_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
			PRIMARY KEY(`id`),
			UNIQUE KEY exp_tag_start_end(exp, tag, session_s, session_e)
			) ENGINE=MyISAM ;",
		
		TABELLE_VERSUCHSPERSONEN =>
			"CREATE TABLE ".TABELLE_VERSUCHSPERSONEN." (
			`id` int(4) NOT NULL auto_increment,
			`exp` int(3) NOT NULL,
			`vorname` varchar(255) collate utf8_unicode_ci NOT NULL,
			`nachname` varchar(255) collate utf8_unicode_ci NOT NULL,
			`geschlecht` varchar(10) collate utf8_unicode_ci NOT NULL,
			`gebdat` date NOT NULL,
			`fach` varchar(255) collate utf8_unicode_ci NOT NULL,
			`semester` varchar(255) collate utf8_unicode_ci NOT NULL,
			`anschrift` text collate utf8_unicode_ci NOT NULL,
			`telefon1` varchar(255) collate utf8_unicode_ci NOT NULL,
			`telefon2` varchar(255) collate utf8_unicode_ci NOT NULL,
			`email` varchar(255) collate utf8_unicode_ci NOT NULL,
			`geldvps` varchar(10) collate utf8_unicode_ci NOT NULL,
			`andere` varchar(4) collate utf8_unicode_ci NOT NULL,
			`weitere` varchar(4) collate utf8_unicode_ci NOT NULL,
			`termin` int(4) NOT NULL,
			`anruf` int(1) NOT NULL,
			PRIMARY KEY(`id`),
			UNIQUE KEY exp_email(exp, email)
			) ENGINE=MyISAM ;",
		
		TABELLE_LABORE =>
			"CREATE TABLE ".TABELLE_LABORE." (
			`id` int(4) NOT NULL auto_increment,
			`label` varchar(255) collate utf8_unicode_ci NOT NULL,
			`address` varchar(255) collate utf8_unicode_ci NOT NULL,
			`room_number` varchar(255) collate utf8_unicode_ci NOT NULL,
			`capacity` int(3) NOT NULL,
			`open_mon_from` time NULL DEFAULT NULL,
			`open_mon_to` time NULL DEFAULT NULL,
			`open_tue_from` time NULL DEFAULT NULL,
			`open_tue_to` time NULL DEFAULT NULL,
			`open_wed_from` time NULL DEFAULT NULL,
			`open_wed_to` time NULL DEFAULT NULL,
			`open_thu_from` time NULL DEFAULT NULL,
			`open_thu_to` time NULL DEFAULT NULL,
			`open_fri_from` time NULL DEFAULT NULL,
			`open_fri_to` time NULL DEFAULT NULL,
			`open_sat_from` time NULL DEFAULT NULL,
			`open_sat_to` time NULL DEFAULT NULL,
			`open_sun_from` time NULL DEFAULT NULL,
			`open_sun_to` time NULL DEFAULT NULL,
			`active` enum('true','false') NOT NULL DEFAULT 'false',
			PRIMARY KEY(`id`)
			) ENGINE=MyISAM ;",

		TABELLE_EXP_TO_LAB =>
			"CREATE TABLE ".TABELLE_EXP_TO_LAB." (
			`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
			`exp_id` smallint(5) unsigned NOT NULL,
			`lab_id` smallint(5) unsigned NOT NULL,
			PRIMARY KEY(`id`),
			UNIQUE KEY `exp_id_lab_id` (`exp_id`,`lab_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;",
			
	);
	
	foreach( $createTableQueries as $tabellenName => $sql ) {
		$erfolg = $mysqli->query($sql);
		if( $erfolg ) {
			echo sprintf( 'Tabelle "%1$s" erfolgreich erstellt.<br/>', $tabellenName);
		}
		else {
			if(TABELLE_EXPERIMENTE === $tabellenName) {
				// hash wert von tabellenstruktur bestimmen, wenn aktueller hash mit
				// hash von alter version der tabelle übereinstimmt -> tabellenstruktur
				// updaten
				$sql = 'SHOW CREATE TABLE ' . TABELLE_EXPERIMENTE;
				$ergebnis = $mysqli->query($sql);
				$row = $ergebnis->fetch_assoc();
				//die folgende zeile schneidet alles hinter der letzten schliessenden klammer ab,
				//damit individuell unterschiedliche auto_increment werte den md5 nicht verzerren
				$createTable = substr($row['Create Table'],0,strrpos($row['Create Table'],')'));
				if('6df7d3292133969ed91b5b08291410eb' === md5($createTable)) {
					$sql = sprintf( '
						ALTER TABLE `c_cortex`
						ADD `exp_start` DATE NULL AFTER `exp_mail`,
						ADD `exp_end` DATE NULL AFTER `exp_start`,
						ADD `max_vp` SMALLINT UNSIGNED NOT NULL,
						ADD `terminvergabemodus` ENUM( \'automatisch\', \'manuell\' ) NOT NULL DEFAULT \'manuell\'
						ADD `show_in_list` ENUM(\'true\',\'false\') NOT NULL DEFAULT \'true\',
						ADD `session_duration` INT UNSIGNED NOT NULL DEFAULT \'0\',
						ADD `max_simultaneous_sessions` SMALLINT(5) UNSIGNED NOT NULL; '
					);
					$erfolg = $mysqli->query($sql);
					if( $erfolg ) {
						echo sprintf( 'Tabelle "%1$s" erfolgreich erneuert.<br/>', $tabellenName);
					}
					else {
						echo sprintf( 'Tabelle "%1$s" besitzt altes Format und konnte nicht erneuert werden:<br/>', $tabellenName);
						echo $mysqli->error . '<br/>';
					}
				}
				else {
					echo sprintf( 'Tabelle "%1$s" besitzt unbekanntes Format und konnte nicht erneuert werden(%2$s):<br/>', $tabellenName, md5($createTable));
					//print_r($createTable);
				}
			}
			elseif(TABELLE_SITZUNGEN === $tabellenName) {
//TODO
				$sql = sprintf('
					ALTER IGNORE TABLE `c_sessions`
					ADD `lab_id` SMALLINT( 5 ) UNSIGNED NOT NULL,
					ADD UNIQUE KEY exp_tag_start_end_lab(exp, tag, session_s, session_e, lab_id)');
			}
			else {
				echo sprintf( 'Tabelle "%1$s" konnte nicht erstellt werden:<br/>', $tabellenName);
				echo $mysqli->error . '<br/>';
			}
		}
	}

	?>
	<font color="#FF0000"><b>Database successfully created</b></font>
	<?
}
?>

</div>
</body>
</html>
