<?php
/* VP EINTRAG ÄNDERN */

if(!isset($_GET['editvp'])) {
	die('missing parameter editvp');
}

require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

$abfrage = sprintf( '
	SELECT		vp.id,
				vp.exp,
				vp.vorname,
				vp.nachname,
				vp.geschlecht,
				vp.gebdat,
				vp.fach,
				vp.semester,
				vp.anschrift,
				vp.telefon1,
				vp.telefon2,
				vp.email,
				vp.geldvps,
				vp.andere,
				vp.weitere,
				termin.id AS termin,
				vp.anruf
	FROM		%1$s AS vp
	LEFT JOIN	%2$s AS termin
		ON		vp.termin = termin.id
	WHERE		vp.id = %3$d'
	, TABELLE_VERSUCHSPERSONEN
	, TABELLE_SITZUNGEN
	, $_GET['editvp']
);
$erg = $mysqli->query($abfrage);
$data = $erg->fetch_assoc();
if(null === $data) {
	die('no data found');
}
$gebDatum = formatMysqlDate($data['gebdat']);

?>
<html>
<head>
<script type="text/javascript">
$( document ).ready(function() {
	$('input[type="button"], input[type="submit"]').button();
	initDatepicker();
});
</script>
</head>
<body>
<h1><?= $data['vorname'] ?> <?= $data['nachname']?></h1>
	
<form action="vpview.php?<?php echo http_build_query(array('expid'=>$_GET['expid']));?>" method="post" enctype="multipart/form-data" name="vpaendern">
<table>
<tr>
	<td width=110px><b>Vorname: </b></td>
	<td><input name="vorname" type="text" size=30 value="<?= $data['vorname'] ?>"></td>
</tr>
<tr>
	<td><b>Nachname: </b></td>
	<td><input name="nachname" type="text" size=30 value="<?= $data['nachname'] ?>"></td>
</tr>
<?
if(!empty($_GET['expid'])) {
	?>		  
	<tr>
		<td><b>Termin: </b></td>
		<td><select name="termin">
	<?
	if(empty($data['termin'])) {
		?>
		<option value="0">-</option>
		<?php
	}
	
	$abfrage3 = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE `exp` = '$_GET[expid]'" ;
	$erg3 = $mysqli->query($abfrage3);
	while ($data3 = $erg3->fetch_assoc()) {  
		?>
		<option value="<?= $data3['id'] ?>" <? if ($data['termin'] == $data3['id'] ) {?>selected<? } ?>><?php echo formatMysqlDate($data3['tag']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . substr($data3['session_s'], 0, 5) . '-' . substr($data3['session_e'], 0, 5);?></option>
		<?
	}
	?>
		</select></td>
	</tr>
	<?
}
?>	
<tr>
	<td><b>Geschlecht: </b></td>
	<td><select name="geschlecht"><option value="" <? if ($data['geschlecht'] == '' ) { ?>selected<? }; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option><option value="männlich" <? if ($data['geschlecht'] == 'm&auml;nnlich' ) { ?>selected<? }; if ($data['geschlecht'] == 'männlich' ) { ?>selected<? };?>>männlich</option><option value="weiblich" <? if ($data['geschlecht'] == 'weiblich' ) { ?>selected<? };?>>weiblich</option></select></td>
</tr>
<tr>
	<td><b>Geburtsdatum: </b></td>
	<td>
		<input type="text" name="gebdat" class="datepicker" size="12" maxlength="10"  value="<?php echo $gebDatum; ?>" />
	</td>
</tr>
<tr>
	<td><b>Studienfach: </b></td>
	<td><input name="fach" type="text" size=30 value="<?= $data['fach'] ?>"></td>
</tr>
<tr>
	<td><b>Anschrift: </b></td>
	<td><textarea name="anschrift" cols=27 rows=5><?= $data['anschrift'] ?></textarea></td>
</tr>
<tr>
	<td><b>Telefon 1: </b></td>
	<td><input name="telefon1" type="text" size=30 value="<?= $data['telefon1'] ?>"></td>
</tr>
<tr>
	<td><b>Telefon 2: </b></td>
	<td><input name="telefon2" type="text" size=30 value="<?= $data['telefon2'] ?>"></td>
</tr>
<tr>
	<td><b>Email: </b></td>
	<td><input name="email" type="text" size=30 value="<?= $data['email'] ?>"><br><br></td>
</tr>
<tr>
	<td colspan="2">
		<input name="vpid" type="hidden" value="<?= $data['id'] ?>">
		<input type="submit" name="editvpbut" value="Ändern" />&nbsp;&nbsp;&nbsp;
		<input type="submit" name="delvp" value="Löschen" />&nbsp;&nbsp;&nbsp;
		<input type="button" value="Abbrechen" name="addcancel" onclick="javascript: window.parent.$('[id^=editvp_]').dialog('close');">
	</td>
</tr> 
</table>
<?
if (isset($_GET['vpnsuche']) && $_GET['vpnsuche'] == '1') {
	?>
	<input name="vpnsuche" type="hidden" value="1">
	<input name="vpn_vorname" type="hidden" value="<?= $_GET['vpn_vorname'] ?>">
	<input name="vpn_nachname" type="hidden" value="<?= $_GET['vpn_nachname'] ?>">
	<input name="vpn_geschlecht" type="hidden" value="<?= $_GET['vpn_geschlecht'] ?>">
	<input name="vpn_alter_sym" type="hidden" value="<?= $_GET['vpn_alter_sym'] ?>">
	<input name="vpn_alter" type="hidden" value="<?= $_GET['vpn_alter'] ?>">
	<input name="sortby" type="hidden" value="<?= $_GET['sortby'] ?>">	
	<?
}
?>
</form>

