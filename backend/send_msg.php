<?php
/* EMAIL AN VERSUCHSPERSONEN SENDEN */

require_once dirname(__FILE__) . '/../include/functions.php';
require_once dirname(__FILE__) . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

if(isset($_GET['expid'])) {
	$abfrage = sprintf( '
		SELECT		id AS exp,
					vl_email
		FROM		%1$s
		WHERE		`id` = %2$d
		LIMIT		1'
		, TABELLE_EXPERIMENTE
		, $_GET['expid']
	);
	$erg = $mysqli->query($abfrage) or die($mysqli->error);
	$dataExp = $erg->fetch_assoc();
}
elseif(isset($_GET['vpid'])) {

	$sql = sprintf( '
		SELECT		vp.vorname,
					vp.nachname,
					vp.email
		FROM		%1$s AS vp
		WHERE		vp.id IN(%2$s)'
		, TABELLE_VERSUCHSPERSONENE
		,
	);
}

?>
<html>
<head>
<script type="text/javascript">
$( document ).ready(function() {
	$("[title]").each(function(){
		$(this).tooltip({ content: $(this).attr("title")});
	});	// It allows html content to tooltip.
	$('input[type="button"], input[type="submit"]').button();
	<?php if(isset($_GET['expid'])) {?>
		showEmailRecipients(<?php echo $dataExp['exp'];?>, <?php echo $_GET['terminid'];?>);
	<?php } ?>
});
</script>
</head>
<body>

<form action="admin.php?<?php echo http_build_query(array('expid' => $dataExp['exp']));?>" method="post" enctype="multipart/form-data">
<table style="margin-left:10px; margin-top:10px;">
	<tr>
		<td class="ad_edit_headline"><b>Termin:</b></td>
		<td class="ad_edit_headline">
			<select id="termin" onchange="terminchange();">
				<?php
				$abfrage2 = sprintf( '
					SELECT		`id`,
								`tag`,
								`session_s`,
								`session_e`
					FROM		%1$s
					WHERE		`exp` = %2$d
					ORDER BY	tag ASC, session_s ASC, session_e ASC'
					, TABELLE_SITZUNGEN
					, $dataExp['exp']
				);
				$erg2 = $mysqli->query($abfrage2);
				while($data2 = $erg2->fetch_assoc()) {
					$sitzungsBezeichnung = formatMysqlDate($data2['tag']) . '&nbsp;&nbsp;&nbsp;' . substr($data2['session_s'], 0, 5) . '-' . substr($data2['session_e'], 0, 5);
					?>
					<option value="<?php echo $data2['id']; ?>"<?php if($_GET['terminid'] === $data2['id']) {echo ' selected="selected"';}?>>
						<?php echo $sitzungsBezeichnung; ?>
					</option>
					<?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="ad_edit_headline"><b>Empfänger:</b></td>
		<td class="ad_edit_headline">
			<div id="namelist" style="width: 235px; height: 35px; font-size: 10px; font-style: italic; overflow:auto;"></div>
		</td>
	</tr>
	<tr>
		<td class="ad_edit_headline"><b>Absender:</b></td>
		<td class="ad_edit_headline"><?php echo $dataExp['vl_email']; ?><input type="hidden" size="55" value="<?php echo $dataExp['vl_email']; ?>" /></td>
	</tr>
	<tr>
		<td class="ad_edit_headline"><b>Betreff:</b></td>
		<td class="ad_edit_headline"><input id="betreff" type="text" style="width:486px;" /><br /><br /></td>
	</tr>
	<tr>
		<td class="ad_edit_headline">
			<b>Nachricht:</b><p style="font-variant: small-caps; font-size: 12px; margin-bottom: 3px; margin-left: 3px; ">Variablen</p>
			<div style="font-size: 11px; cursor:pointer; font-style:italic; margin-left:6px; line-height:normal;">
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!liebe/r!');" title="Geschlechtsspezifische Anrede<br /><br /><font color=#FF0000><b>Nur verwenden, wenn VP bei der Anmeldung<br />ihr Geschlecht angeben müssen!</b></font>">!liebe/r!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!vp_vorname!');" title="Vorname der Versuchsperson">!vp_vorname!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!vp_nachname!');" title="Nachname der Versuchsperson">!vp_nachname!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!termin!');" title="Von der VP ausgewähltes Datum; <b>dd.mm.yyyy</b>">!termin!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!beginn!');" title="Beginn der von der VP ausgewählten Session; <b>hh:mm</b>">!beginn!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!ende!');" title="Ende der von der VP ausgewählten Session; <b>hh:mm</b>">!ende!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!exp_name!');" title="Name des Experiments">!exp_name!</a><br />
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!exp_ort!');" title="Ort des Experiments">!exp_ort!</a><br />	
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!vl_name!');" title="Name des Versuchsleiters">!vl_name!</a><br />	
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!vl_telefon!');" title="Telefonnummer des Versuchsleiters">!vl_telefon!</a><br />	
				<a onclick="javascript:$('#nachrichtTermin').insertAtCaret('!vl_email!');" title="E-Mail-Adresse des Versuchsleiters">!vl_email!</a>	
			</div>
		</td>
		<td class="ad_edit_headline">
			<textarea name="nachricht" id="nachrichtTermin" cols="50" rows="10"></textarea><br><br>
			<input type="hidden" name="expid" value="<?php echo $dataExp['exp'];?>">	
			<input type="button" name="vpmsgbut" value="Senden" onclick="sendmsg($('#termin').val(), 'nachrichtTermin', 'terminmailpreview');" style="width:80px;" />
			<input type="button" onclick="javascript:showEmailPreview('nachrichtTermin', $('#termin').val(), 'terminmailpreview');" value="Vorschau" />
			<input type="button" name="delexpn" onclick="javascript: window.parent.$('[id^=send_msg_]').dialog('close');" value="Abbrechen"  style="width:80px;" />
			
			<? if ( isset($_POST['vpmsgbut'])) { ?>
				&nbsp;&nbsp;&nbsp;<font color="#ff0000"><b>Nachricht versendet!</b></font>
			<? } ?>
			<br />
		</td>
	</tr>
</table>
<div id="terminmailpreview" style="width:486px; font-size:11px; overflow:auto;"></div>
</form>	

</body>
</html>