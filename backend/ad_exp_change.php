<?php
require_once dirname(__FILE__) . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

if(!isset($_GET['expid'])) {
	die('Expid fehlt');
}

$abfrage = sprintf( '
	SELECT		exp.id,
				exp.vl_name,
				exp.vl_tele,
				exp.vl_email,
				exp.exp_name,
				exp.exp_ort,
				exp.exp_vps,
				exp.exp_vpsnum,
				exp.exp_geld,
				exp.exp_geldnum,
				exp.exp_zusatz,
				exp.exp_mail,
				IF(exp.exp_start="0000-00-00","offen",UNIX_TIMESTAMP(exp.exp_start)) AS exp_start,
				IF(exp.exp_end="0000-00-00","offen",UNIX_TIMESTAMP(exp.exp_end)) AS exp_end,
				exp.vpn_name,
				exp.vpn_geschlecht,
				exp.vpn_gebdat,
				exp.vpn_fach,
				exp.vpn_semester,
				exp.vpn_adresse,
				exp.vpn_tele1,
				exp.vpn_tele2,
				exp.vpn_email,
				exp.vpn_ifreward,
				exp.vpn_ifbereits,
				exp.vpn_ifbenach,
				exp.visible,
				exp.max_vp,
				exp.terminvergabemodus,
				exp.show_in_list,
				exp.session_duration,
				exp.max_simultaneous_sessions
	FROM		%1$s AS exp
	WHERE		exp.id = %2$d'
	, TABELLE_EXPERIMENTE
	, $_GET['expid']
);
$erg = $mysqli->query($abfrage) or die($mysqli->error);
$data = $erg->fetch_assoc();

/* EXPERIMENT ÄNDERN */
?>
<html>
<head>
<script type="text/javascript">
$( document ).ready(function() {
	$("[title]").each(function(){
		$(this).tooltip({ content: $(this).attr("title")});
	});	// It allows html content to tooltip.
	$('input[type="button"], input[type="submit"]').button();
	$( "#tabgroup_change" ).tabs({ active: <?php if ( 'automatisch' === $data['terminvergabemodus'] ) { echo '0'; } else { echo '1';}?> });
	initDatepicker();
	$(".spinner").spinner({
		spin: function( event, ui ) {
			if ( ui.value < 1 ) {
				$( this ).spinner( "value", 1 );
				return false;
			}
		}
	});
	$('form[name=change]').on('submit', function() {
		$('<input/>').attr({
			type: 'hidden',
			name: 'terminvergabemodus',
			value: $('#subtabs-1').is(':visible') ? 'automatisch' : 'manuell'
		}).appendTo($(this));
	});
});
</script>
</head>
<body>
<form action="admin.php?<?php echo http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="change">
	<input id="sessions" name="sessions" type="hidden" value="" />
	<input id="days" name="days" type="hidden" value="" />

	<table class="optionGroup">
		<tr>
			<td colspan="3" class="ad_edit_headline"><h3>Daten zum Versuchsleiter</h3></td>
		</tr>
		<tr>
			<td width=200px class="ad_edit_headline">Name des VLs</td>
			<td width=220px class="ad_edit_headline"><input id="vl_name" name="vl_name" type="text" size="45" maxlength="200" value="<?= $data['vl_name'] ?>" /></td>
			<td width=30px class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Diese Angaben werden veröffentlicht und als Kontaktdaten für das Experiment angegeben." alt="" /></td>	
		</tr>
		<tr>
			<td class="ad_edit_headline">Telefonnummer des VLs</td>
			<td class="ad_edit_headline"><input name="vl_tele" id="vl_tele" type="text" size="45" maxlength="200" value="<?= $data['vl_tele'] ?>" /></td>
		</tr>
		<tr>
			<td class="ad_edit_headline">Emailadresse des VLs</td>
			<td class="ad_edit_headline"><input name="vl_email" id="vl_email" type="text" size="45" maxlength="200" value="<?= $data['vl_email'] ?>" /></td>
		</tr>
	</table>
		
	<table class="optionGroup">
		<tr>
			<td colspan="3" class="ad_edit_headline"><h3>Daten zum Experiment</h3></td>
		</tr>
		<tr>
			<td class="ad_edit_headline">Name des Experiments</td>
			<td class="ad_edit_headline"><input name="exp_name" id="exp_name" type="text" size="45" maxlength="200" value="<?= $data['exp_name'] ?>" /></td>
			<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Dieser Name wird im Menü links angezeigt."></td>
		</tr>
		<tr>
			<td class="ad_edit_headline">Zusätzliche&nbsp;Informationen&nbsp;/<br />Beschreibung</td>
			<td class="ad_edit_headline"><textarea name="exp_zusatz" rows="5" cols="40"><?= strip_tags(nl2br($data['exp_zusatz'])) ?></textarea></td>
			<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Vorab-Informationen über das Experiment"></td>	
		</tr>
		<tr>
			<td class="ad_edit_headline">Ort des Experiments</td>
			<td class="ad_edit_headline"><textarea name="exp_ort" id="exp_ort" rows="5" cols="40"><?= strip_tags(nl2br($data['exp_ort'])) ?></textarea></td>
			<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Adresse, Wegbeschreibung, etc."></td>	
		</tr> 
		<tr>
			<td class="ad_edit_headline">Bestätigungsemail<br /><p style="font-variant: small-caps; font-size: 12px; margin-bottom: 3px; margin-left: 3px; ">Variablen</p>
				<div style="font-size: 11px; cursor:pointer; font-style:italic; margin-left:6px; ">
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!liebe/r!');" title="Geschlechtsspezifische Anrede<br /><br /><font color=#FF0000><b>Nur verwenden, wenn VP bei der Anmeldung<br />ihr Geschlecht angeben müssen!</b></font>">!liebe/r!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!vp_vorname!');" title="Vorname der Versuchsperson">!vp_vorname!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!vp_nachname!');" title="Nachname der Versuchsperson">!vp_nachname!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!termin!');" title="Von der VP ausgewähltes Datum; <b>dd.mm.yyyy</b>">!termin!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!beginn!');" title="Beginn der von der VP ausgewählten Session; <b>hh:mm</b>">!beginn!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!ende!');" title="Ende der von der VP ausgewählten Session; <b>hh:mm</b>">!ende!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!exp_name!');" title="Name des Experiments">!exp_name!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!exp_ort!');" title="Ort des Experiments">!exp_ort!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!vl_name!');" title="Name des Versuchsleiters">!vl_name!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!vl_telefon!');" title="Telefonnummer des Versuchsleiters">!vl_telefon!</a><br />
				<a onclick="javascript:$('#exp_mail').insertAtCaret('!vl_email!');" title="E-Mail-Adresse des Versuchsleiters">!vl_email!</a><br /><br />
				</div>
			</td>
			<td class="ad_edit_headline">
				<textarea name="exp_mail" id="exp_mail" rows="12" cols="40"><?= strip_tags(nl2br($data['exp_mail'])) ?></textarea>
				<br />
				<input type="button" onclick="javascript:show_preview('exp_mail', 'preview_change');" value="Vorschau" />
				<br />
				<div id="preview_change" style="font-size:11px; overflow:auto;"></div>
			</td>
			<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Mail, die die Versuchspersonen nach ihrer Anmeldung automatisiert erhalten." alt="" /><div width="200px"></div></td>
		</tr>  
		<tr>
			<td class="ad_edit_headline">
				<input name="exp_vps" type="checkbox" value="1" <? if ( $data['exp_vps'] == 1 ) {?>checked /><? } else { ?> /><? } ?> VP-Stunden&nbsp;&nbsp;
				<input name="exp_vpsnum" type="text" size="5" maxlength="5" value="<?= $data['exp_vpsnum'] ?>" />
			</td>
			<td class="ad_edit_headline">
				<input name="exp_geld" type="checkbox" value="1" <? if ( $data['exp_geld'] == 1 ) {?>checked /><? } else { ?>/><? } ?> Geld&nbsp;&nbsp;
				<input name="exp_geldnum" type="text" size="5" maxlength="5" value="<?= $data['exp_geldnum'] ?>" />
			</td>
			<td class="ad_edit_headline">
				<img src="images/info.gif" width="17" height="17" title="Bitte Kommata verwenden, keine Punkte" alt="" />
			</td>
		</tr>  
	</table>

	<div class="optionGroup">
		<h3>Terminvergabe</h3>
		<ul>
			<li>
				<label for="show_in_list">In Liste anzeigen&nbsp;<img src="images/info.gif" width="17" height="17" title="Diese Option entscheidet, ob das Experiment im öffentlichen Teil von Cortex angezeigt wird. Wird es nicht angezeigt, kann es dennoch über einen speziellen Link angesteuert werden." alt="" /></label>
				<input type="checkbox" name="show_in_list" id="show_in_list" value="1"<?php if ( 'true' === $data['show_in_list'] ) { echo ' checked="checked"'; }?> />
			</li>
			<li>
				<label for="session_duration">Sitzungsdauer&nbsp;<img src="images/info.gif" width="17" height="17" title="Die durchschnittliche Sitzungsdauer des Experiments in Minuten" alt="" /></label>
				<input type="text" name="session_duration" id="session_duration" value="<?php echo $data['session_duration'];?>" size="5" class="spinner" />
			</li>
		</ul>
	
		<div id="tabgroup_change">
			<ul>
				<li><a href="#subtabs-1">Automatisch</a></li>
				<li><a href="#subtabs-2">Manuell</a></li>
			</ul>
			<div id="subtabs-1" class="tabs">
				<ul>
					<li><label for="lab_id">Labore</label>
						<select name="lab_id[]" id="lab_id" size="5" style="width:200px;" multiple="multiple">
							<?php
							$sql = sprintf( '
								SELECT		lab.id,
											lab.label,
											IF(e2l.id IS NULL,\'0\', \'1\') AS selected
								FROM		%1$s AS lab
								LEFT JOIN	%2$s AS e2l
									ON		e2l.lab_id = lab.id
									AND		e2l.exp_id = %3$d
								WHERE		lab.active = \'true\' OR NOT e2l.id IS NULL'
								, TABELLE_LABORE							
								, TABELLE_EXP_TO_LAB
								, $_GET['expid']
							);
							$ergebnis = $mysqli->query($sql) OR die($mysqli->error);
							
							while($row = $ergebnis->fetch_assoc()) {
								echo sprintf('<option value="%1$d"%2$s>%3$s</option>', $row['id'], ('1' === $row['selected'] ? ' selected="selected"' : '') ,$row['label']);
							}
							?>
						</select></li>
					<li><label for="exp_start">Beginn des Expepriments<img src="images/info.gif" width="17" height="17" title="Frühester Termin, der vergeben wird" alt="" /></label><input type="text" name="exp_start" id="exp_start" class="datepicker" value="<?php if(is_numeric($data['exp_start'])) { echo date('d.m.Y', $data['exp_start']);}?>" size="11" maxlength="10"/></li>
					<li><label for="exp_end">Ende des Experiments<img src="images/info.gif" width="17" height="17" title="Spätester Termin, der vergeben wird" alt="" /></label><input type="text" name="exp_end" id="exp_end" class="datepicker" value="<?php if(is_numeric($data['exp_end'])) { echo date('d.m.Y', $data['exp_end']);}?>" size="11" maxlength="10"/></li>
					<li><label for="max_vp">Maximale Anzahl VP<img src="images/info.gif" width="17" height="17" title="Terminvergabe endet automatisch, wenn diese Anzahl an VP angemeldet ist" alt="" /></label><input type="text" name="max_vp" id="max_vp" value="<?php echo $data['max_vp'];?>" size="11" maxlength="10" class="spinner"/></li>
					<li>
						<label for="max_simultaneous_sessions">Max. Zahl gleichzeitiger Sitzungen</label>
						<input name="max_simultaneous_sessions" id="max_simultaneous_sessions" type="text" value="<?php echo $data['max_simultaneous_sessions'];?>" size="5" maxlength="5" class="spinner" />
					</li>
				</ul>
			</div>
			<div id="subtabs-2" class="tabs">
				<ul>
					<li>
						<label for="addday">Tage&nbsp;<img src="images/info.gif" width="17" height="17" title="Tage, an denen das Experiment durchgeführt werden soll. Bitte nur im Format <b>tt.mm.jjjj</b>." alt="" /></label>
						<input type="button" id="addday" value="Tag Hinzuf&uuml;gen" onclick="addDay( this.parentNode );" />
					</li>
					<li>
						<label for="addsession">Sessions&nbsp;<img src="images/info.gif" width="17" height="17" title="Tägliche, zeitlich festgelegte Sessions; Format <b>hh:mm</b>." alt="" /></label>
						<input type="button" id="addsession" value="Session Hinzuf&uuml;gen" onclick="addSession( this.parentNode )" />
					</li>
					<li>
						<label for="exp_teiln">Max. Zahl an VPs pro Session</label>
						<input name="exp_teiln" id="exp_teiln" type="text" size="5" maxlength="5" class="spinner" />
					</li>
				</ul>
			</div>
		</div>
	</div>
	
	<table class="optionGroup">
		<tr>
			<td colspan="3" class="ad_edit_headline"><h3>Angaben der Versuchspersonen</h3></td>
		</tr>
		<tr>
			<td class="ad_edit_headline"><input name="vpn_name" id="vpn_name" type="checkbox" value="1" checked="checked" onclick="this.checked=true;" /></td>
			<td class="ad_edit_headline"><label for="vpn_name">Name</label></td>
			<td class="ad_edit_headline"></td>
		</tr>
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_geschlecht" id="vpn_geschlecht" value="1" <? if ( $data['vpn_geschlecht'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_geschlecht">Geschlecht</label></td>	
		</tr>  
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_gebdat" id="vpn_gebdat" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_gebdat_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_gebdat_o').style.visibility='hidden'; };" <? if ( $data['vpn_gebdat'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_gebdat">Geburtsdatum</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_gebdat_o" name="vpn_gebdat_o" <? if ( $data['vpn_gebdat'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_gebdat'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_gebdat'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr>   
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_fach" id="vpn_fach" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_fach_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_fach_o').style.visibility='hidden'; };" <? if ( $data['vpn_fach'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_fach">Studienfach</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_fach_o" name="vpn_fach_o" <? if ( $data['vpn_fach'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_fach'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_fach'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr>	
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_semester" id="vpn_semester" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_semester_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_semester_o').style.visibility='hidden'; };" <? if ( $data['vpn_semester'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_semester">Semesterzahl</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_semester_o" name="vpn_semester_o" <? if ( $data['vpn_semester'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_semester'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_semester'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr> 
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_adresse" id="vpn_adresse" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_adresse_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_adresse_o').style.visibility='hidden'; };" <? if ( $data['vpn_adresse'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_adresse">Anschrift</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_adresse_o" name="vpn_adresse_o" <? if ( $data['vpn_adresse'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_adresse'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_adresse'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr>	
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_tele1" id="vpn_tele1" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_tele1_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_tele1_o').style.visibility='hidden'; };" <? if ( $data['vpn_tele1'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_tele1">Festnetznummer</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_tele1_o" name="vpn_tele1_o" <? if ( $data['vpn_tele1'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_tele1'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_tele1'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr>  
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_tele2" id="vpn_tele2" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_tele2_o').style.visibility='visible'; } else if( this.checked==false ) { document.getElementById('vpn_tele2_o').style.visibility='hidden'; };" <? if ( $data['vpn_tele2'] == 0 ) {?>/><? } else { ?>checked /><? } ?>
			</td>
			<td class="ad_edit_headline"><label for="vpn_tele2">Mobilnummer</label>&nbsp;&nbsp;&nbsp;
				<select id="vpn_tele2_o" name="vpn_tele2_o" <? if ( $data['vpn_tele2'] == 0 ) {?>style="visibility:hidden; "<? } else { ?>style="visibility:visible; "<? } ?>>
					<option value="0" <? if ( $data['vpn_tele2'] == 1 ) { ?>selected<? } ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
					<option value="1" <? if ( $data['vpn_tele2'] == 2 ) { ?>selected<? } ?>>*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>	
		</tr>  
		<tr>					
			<td class="ad_edit_headline">
				<input type="checkbox" name="vpn_email" id="vpn_email" value="1" checked="checked" onclick="this.checked=true;" />
			</td>
			<td class="ad_edit_headline"><label for="vpn_email">Email-Adresse *</label></td>	
		</tr>  
		<tr>					
			<td class="ad_edit_headline"><input type="checkbox" name="vpn_ifreward" id="vpn_ifreward" value="1" <? if ( $data['vpn_ifreward'] == 1 ) {?>checked /><? } else { ?>/><? } ?></td>
			<td class="ad_edit_headline"><label for="vpn_ifreward">Entscheidung: Geld oder VP-Stunden</label></td>	
		</tr>  
		<tr>					
			<td class="ad_edit_headline"><input type="checkbox" name="vpn_ifbereits" id="vpn_ifbereits" value="1" <? if ( $data['vpn_ifbereits'] == 1 ) {?>checked /><? } else { ?>/><? } ?></td>
			<td class="ad_edit_headline"><label for="vpn_ifbereits">Frage: Hat die VP bereits an Experimenten dieses VLs teilgenommen?</label></td>	
		</tr>   
		<tr>					
			<td class="ad_edit_headline"><input type="checkbox" name="vpn_ifbenach" id="vpn_ifbenach" value="1" <? if ( $data['vpn_ifbenach'] == 1 ) {?>checked /><? } else { ?>/><? } ?></td>
			<td class="ad_edit_headline"><label for="vpn_ifbenach">Frage: Möchte die VP über weitere Experimente benachrichtigt werden?</label></td>	
		</tr>	 
		
		<tr>
			<td>
				<br /><br />
			</td>
		</tr>
		
		<tr>
			<td>
				<br />
			</td> 
			<td>
				<input name="expid" type="hidden" value="<?php echo $_GET['expid'];?>" />
				<input name="admin" type="hidden" value="1" />
				<input name="editexp" type="submit" value="Übernehmen" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="addcancel" type="button" value="Abbrechen" onclick="javascript: window.parent.$('[id=changeexp]').dialog('close');" />
				<br /><br /><br />		
			</td>
		</tr>
	</table>


</form>
</body>
</html>