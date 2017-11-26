<?php
if(basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	header('HTTP/1.1 404 Not Found');
	exit;
}
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/controller/SessionController.class.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();
$auth = new AccessControl();

/* WENN EXPERIMENT ERSTELLT WURDE */
/* ------------------------------ */
if(isset($_POST['createexp'])) {
	
	prepareIncomingVariables();
	$success = validateExpData();
	
	if($success) {

		$eintrag = sprintf( '
			INSERT INTO `%1$s`
			(`vl_name`, `vl_tele`, `vl_email`, `exp_name`, `exp_ort`, `exp_vps`, `exp_vpsnum`, `exp_geld`, `exp_geldnum`, `exp_zusatz`, `exp_mail`, `exp_start`, `exp_end`, `vpn_name`, `vpn_geschlecht`, `vpn_gebdat`, `vpn_fach`, `vpn_semester`, `vpn_adresse`, `vpn_tele1`, `vpn_tele2`, `vpn_email`, `vpn_ifreward`, `vpn_ifbereits`, `vpn_ifbenach`, `visible`, `max_vp`, `terminvergabemodus`, `show_in_list`, `session_duration`, `max_simultaneous_sessions`)
			VALUES (\'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%13$s\', \'%14$s\', \'%15$s\', %16$d, %17$d, %18$d, %19$d, %20$d, %21$d, %22$d, %23$d, %24$d, %25$d, %26$d, %27$d, 0, %28$d, "%29$s", "%30$s", %31$d, %32$d)'
			, /*  1 */ TABELLE_EXPERIMENTE
			, /*  2 */ $mysqli->real_escape_string($vl_name)
			, /*  3 */ $mysqli->real_escape_string($vl_tele)
			, /*  4 */ $mysqli->real_escape_string($vl_email)
			, /*  5 */ $mysqli->real_escape_string($exp_name)
			, /*  6 */ $mysqli->real_escape_string($exp_ort)
			, /*  7 */ isset($_POST['exp_vps'])?$mysqli->real_escape_string($_POST['exp_vps']):''
			, /*  8 */ isset($_POST['exp_vpsnum'])?$mysqli->real_escape_string($_POST['exp_vpsnum']):''
			, /*  9 */ isset($_POST['exp_geld'])?$mysqli->real_escape_string($_POST['exp_geld']):''
			, /* 10 */ isset($_POST['exp_geldnum'])?$mysqli->real_escape_string($_POST['exp_geldnum']):''
			, /* 11 */ $mysqli->real_escape_string($exp_zusatz)
			, /* 12 */ null
			, /* 13 */ $mysqli->real_escape_string($exp_mail)
			, /* 14 */ is_array($exp_start) && false !== $exp_start['year'] && false !== $exp_start['month'] && false !== $exp_start['day'] ? $exp_start['year'] . '-' . $exp_start['month'] . '-' . $exp_start['day'] : ''
			, /* 15 */ is_array($exp_end) && false !== $exp_end['year'] && false !== $exp_end['month'] && false !== $exp_end['day'] ? $exp_end['year'] . '-' . $exp_end['month'] . '-' . $exp_end['day'] : ''
			, /* 16 */ isset($_POST['vpn_name'])?$_POST['vpn_name']:''
			, /* 17 */ isset($_POST['vpn_geschlecht'])?$_POST['vpn_geschlecht']:''
			, /* 18 */ $vpn_gebdat
			, /* 19 */ $vpn_fach
			, /* 20 */ $vpn_semester
			, /* 21 */ $vpn_adresse
			, /* 22 */ $vpn_tele1
			, /* 23 */ $vpn_tele2
			, /* 24 */ $vpn_email
			, /* 25 */ isset($_POST['vpn_ifreward'])?$_POST['vpn_ifreward']:''
			, /* 26 */ isset($_POST['vpn_ifbereits'])?$_POST['vpn_ifbereits']:''
			, /* 27 */ isset($_POST['vpn_ifbenach'])?$_POST['vpn_ifbenach']:''
			, /* 28 */ isset($_POST['max_vp'])?$_POST['max_vp']:0
			, /* 29 */ (isset($_POST['terminvergabemodus']) && 'automatisch' === $_POST['terminvergabemodus']) ? 'automatisch' : 'manuell'
			, /* 30 */ (isset($_POST['show_in_list']) && '1' === $_POST['show_in_list']) ? 'true' : 'false'
			, /* 31 */ isset($_POST['session_duration'])?$_POST['session_duration']:0
			, /* 32 */ isset($_POST['max_simultaneous_sessions'])?$_POST['max_simultaneous_sessions']:0
		);
		$ergebnis = $mysqli->query($eintrag) OR die($mysqli->error);
		$expid = $mysqli->insert_id;

        if($auth->mayEditExpLab()){
            if('automatisch' === $_POST['terminvergabemodus'] && isset($_POST['lab_id'])) {
                foreach($_POST['lab_id'] as $labId) {
                    $eintrag = sprintf( '
                        INSERT INTO %1$s (`exp_id`, `lab_id`)
                        VALUES (%2$d, %3$d)'
                        , TABELLE_EXP_TO_LAB
                        , $expid
                        , $labId
                    );
                    $ergebnis = $mysqli->query($eintrag) OR die($mysqli->error);
                }
            }
            else {
                //hier werden die einzelnen termine des experiments angelegt
                $sc = new SessionController();
                for ( $x = 1; $x <= $_POST['days']; $x++ ) {
                    $temp_help = $_POST['exp_tag' . $x];
                    for ( $y = 1; $y <= $_POST['sessions']; $y++ ) {
                        $temp_session_s = $_POST['exp_session' . $y . 's'];
                        $temp_session_e = $_POST['exp_session' . $y . 'e'];
                        $sc->create(
                            $expid,
                            $temp_help,
                            $temp_session_s,
                            $temp_session_e,
                            $_POST['exp_teiln']
                        );
                    }
                }
            }

            $location = sprintf(
                'http://%1$s%2$s?expid=%3$d'
                , $_SERVER['HTTP_HOST']
                , $_SERVER['PHP_SELF']
                , $expid
            );
            header('Location: ' . $location);
            exit;
        }
    }
}

require_once 'pageelements/header.php';

/* EXPERIMENT HINZUFÜGEN */
/* -------------------------- */  

if(isset($_GET['action']) && $_GET['action'] == 'create') {
?>
<script type="text/javascript">
	$( document ).ready(function() {
		$( "#tabs" ).tabs();
		initDatepicker();
		$(".spinner").spinner({
			spin: function( event, ui ) {
				if ( ui.value < 1 ) {
					$( this ).spinner( "value", 1 );
					return false;
				}
			}
		});
		$('#exp_start').val(getToday(1));
		$('#exp_vpsnum').on('focusin', function() {
			if($(this).val()=='') {
				$("input[name=exp_vps]").prop('checked', true);
			}
		});
		$('#exp_vpsnum').on('focusout', function() {
			if($(this).val()=='') {
				$("input[name=exp_vps]").prop('checked', false);
			}
		});
		$('#exp_geldnum').on('focusin', function() {
			if($(this).val()=='') {
				$("input[name=exp_geld]").prop('checked', true);
			}
		});
		$('#exp_geldnum').on('focusout', function() {
			if($(this).val()=='') {
				$("input[name=exp_geld]").prop('checked', false);
			}
		});
		$('#create').on('submit', function() {
			$('<input/>').attr({
				type: 'hidden',
				name: 'terminvergabemodus',
				value: $('#tabs-1').is(':visible') ? 'automatisch' : 'manuell',
			}).appendTo($(this));
		});

		var copyOldSelect = $('#copy_old');
		$.ajax({
			type: "GET",
			url: "service/exp.php",
			data: {
				'method': 'list'
			},
			dataType: "json",
			success: function( data ) {
				copyOldSelect.append(
					$('<option />').val(0).html('-')
				);
				$.each( data, function( key, value ) {
					copyOldSelect.append(
						$('<option />').val(value.id).html(value.name)
					);
				});
			}
		});
		copyOldSelect.on('change', function() {
			if($(this).val() > 0) {
				var decision = confirm("Daten des gewählten Experiments in das aktuelle Formular laden (bereits eingegebene Daten werden überschrieben)?");
				if (decision) {
					$.ajax({
						type: "GET",
						url: "service/exp.php",
						data: {
							'method': 'single',
							'id': $(this).val()
						},
						dataType: "json",
						success: function( data ) {
							var expData = data[0];
							$('input[name=vl_name]').val(expData.vl_name);
							$('input[name=vl_tele]').val(expData.vl_tele);
							$('input[name=vl_email]').val(expData.vl_email);
							$('input[name=exp_name]').val(expData.exp_name);
							$('textarea[name=exp_zusatz]').val(expData.exp_zusatz);
							$('textarea[name=exp_ort]').val(expData.exp_ort);
							$('textarea[name=exp_mail]').val(expData.exp_mail);
							$('input[name=exp_vps]').prop('checked', '1'==expData.exp_vps);
							$('input[name=exp_vpsnum]').val(expData.exp_vpsnum);
							$('input[name=exp_geld]').prop('checked', '1'==expData.exp_geld);
							$('input[name=exp_geldnum]').val(expData.exp_geldnum);
							$('input[name=show_in_list]').prop('checked', '1'==expData.visible);
							$('input[name=session_duration]').val(expData.session_duration);
							$('input[name=exp_start]').val(expData.exp_start);
							$('input[name=exp_end]').val(expData.exp_end);
							$('input[name=max_vp]').val(expData.max_vp);
							$('input[name=max_simultaneous_sessions]').val(expData.max_simultaneous_sessions);
							$('input[name=exp_teiln]').val(expData.exp_vps);
							$('input[name=vpn_geschlecht]').prop('checked', expData.vpn_geschlecht>0);
							$('input[name=vpn_name]').prop('checked', expData.vpn_name>0);
							$('input[name=vpn_gebdat]').prop('checked', expData.vpn_gebdat>0);
							$('input[name=vpn_fach]').prop('checked', expData.vpn_fach>0);
							$('input[name=vpn_semester]').prop('checked', expData.vpn_semester>0);
							$('input[name=vpn_adresse]').prop('checked', expData.vpn_adresse>0);
							$('input[name=vpn_tele1]').prop('checked', expData.vpn_tele1>0);
							$('input[name=vpn_tele2]').prop('checked', expData.vpn_tele2>0);
							$('input[name=vpn_email]').prop('checked', expData.vpn_email>0);
							$('input[name=vpn_ifreward]').prop('checked', expData.vpn_ifreward>0);
							$('input[name=vpn_ifbereits]').prop('checked', expData.vpn_ifbereits>0);
							$('input[name=vpn_ifbenach]').prop('checked', expData.vpn_ifbenach>0);
							$('#tabs').tabs("option", "active", 'automatisch'==expData.terminvergabemodus ? 0 : 1);
						}
					});
				}
			}
		})

	});
</script>

<form action="admin.php" method="post" enctype="multipart/form-data" name="create" id="create">
	<input id="sessions" name="sessions" type="hidden" value="" />
	<input id="days" name="days" type="hidden" value="" />
<h1>Experiment hinzufügen</h1>

<table class="optionGroup">
	<tr>
		<td class="ad_edit_headline">Daten kopieren von</td>
		<td class="ad_edit_headline"><select name="copy_old" id="copy_old" style="width: 343px;" /></td>
		<td class="ad_edit_headline"></td>
	</tr>
	<tr>
		<td colspan="3" class="ad_edit_headline"><h3>Daten zum Versuchsleiter</h3></td>
	</tr>
	<tr>
		<td style="width:200px;" class="ad_edit_headline">Name des VLs</td>
		<td class="ad_edit_headline"><input name="vl_name" id="vl_name" type="text" size="45" maxlength="200" /></td>
		<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Diese Angaben werden veröffentlicht und als Kontaktdaten für das Experiment angegeben." alt="" /></td>
	</tr>
	<tr>
		<td class="ad_edit_headline">Telefonnummer des VLs</td>
		<td class="ad_edit_headline"><input name="vl_tele" id="vl_tele" type="text" size="45" maxlength="200" /></td>
		<td class="ad_edit_headline"></td>
	</tr>
	<tr>
		<td class="ad_edit_headline">Emailadresse des VLs</td>
		<td class="ad_edit_headline"><input name="vl_email" id="vl_email" type="text" size="45" maxlength="200" /></td>
		<td class="ad_edit_headline"></td>
	</tr>
</table>
	
<table class="optionGroup">
  <tr>
	<td colspan="4" class="ad_edit_headline"><h3>Daten zum Experiment</h3></td>
  </tr>
  <tr>
	<td style="width:200px;" class="ad_edit_headline">Name des Experiments</td>
	<td class="ad_edit_headline"><input name="exp_name" id="exp_name" type="text" size="45" maxlength="200" /></td>
	<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Dieser Name wird im Menü links angezeigt." alt="" /></td> 
	<td class="ad_edit_headline"></td>
  </tr>
  <tr>
	<td class="ad_edit_headline">Zusätzliche Informationen /<br />Beschreibung</td>
	<td class="ad_edit_headline"><textarea name="exp_zusatz" rows="5" style="width:343px;"></textarea></td>
	<td class="ad_edit_headline"  colspan="2"><img src="images/info.gif" width="17" height="17" title="Vorab-Informationen über das Experiment" alt="" /></td>	
  </tr>
  <tr>
	<td class="ad_edit_headline">Ort des Experiments</td>
	<td class="ad_edit_headline"><textarea name="exp_ort" id="exp_ort" rows="5" style="width:343px;"></textarea></td>
	<td class="ad_edit_headline" colspan="2"><img src="images/info.gif" width="17" height="17" title="Adresse, Wegbeschreibung, etc." alt="" /></td>	
  </tr>  
  <tr>
	<td class="ad_edit_headline">
		Bestätigungsemail
		<p style="font-variant: small-caps; font-size: 12px; margin-bottom: 3px; margin-left: 3px; ">
			Variablen
		</p>
		<span style="font-size: 11px; cursor:pointer; font-style:italic; margin-left:6px; ">
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
		</span>
	</td>
	
	<td class="ad_edit_headline"><textarea name="exp_mail" id="exp_mail" rows="12" style="width:343px;"></textarea></td>
	<td class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Mail, die die Versuchspersonen nach ihrer<br />Anmeldung automatisiert erhalten." alt="" /><div width="200px"></div></td>
	<td><input type="button" onclick="javascript:show_preview('exp_mail', 'expmailpreview');" value="Vorschau" /><br />
	<div id="expmailpreview" style="width:170px; font-size:11px; "></div></td>
  </tr>  
  <tr>					
	<td width="200px" class="ad_edit_headline">
	<input name="exp_vps" type="checkbox" value="1" />&nbsp;<label for="exp_vpsnum">VP-Stunden &nbsp;&nbsp;</label>
	<input name="exp_vpsnum" id="exp_vpsnum" type="text" size="5" maxlength="5" />
	</td>
	<td class="ad_edit_headline">
	<input name="exp_geld" type="checkbox" value="1" />&nbsp;<label for="exp_geldnum">Geld &nbsp; &nbsp;</label>
	<input name="exp_geldnum" id="exp_geldnum" type="text" size="5" maxlength="5" />
	</td>
	<td class="ad_edit_headline"  colspan="2"><img src="images/info.gif" width="17" height="17" title="Bitte Kommata verwenden, keine Punkte" alt="" /></td>
  </tr>  
</table>
	
<br /><br />
  
<div class="optionGroup">
	<h3>Terminvergabe</h3>
	<ul>
		<li>
			<label for="show_in_list">In Liste anzeigen&nbsp;<img src="images/info.gif" width="17" height="17" title="Diese Option entscheidet, ob das Experiment im öffentlichen Teil von Cortex angezeigt wird. Wird es nicht angezeigt, kann es dennoch über einen speziellen Link angesteuert werden." alt="" /></label>
			<input type="checkbox" name="show_in_list" id="show_in_list" value="1" checked="checked" />
		</li>
		<li>
			<label for="session_duration">Sitzungsdauer&nbsp;<img src="images/info.gif" width="17" height="17" title="Die durchschnittliche Sitzungsdauer dieses Experiments in Minuten" alt="" /></label>
			<input type="text" name="session_duration" id="session_duration" value="" size="5" class="spinner" />
		</li>
	</ul>

    <?php
        if($auth->mayEditExpTimeFrame() || $auth->mayEditExpLab()) {
    ?>
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1">Automatisch</a></li>
			<li><a href="#tabs-2">Manuell</a></li>
		</ul>
		<div id="tabs-1" class="tabs">
			<ul>
				<li><label for="lab_id">Labore</label>
					<select name="lab_id[]" id="lab_id" size="5" style="width:200px;" multiple="multiple">
						<?php
						$sql = sprintf( '
							SELECT	id, label
							FROM	%1$s
							WHERE	active = "true"'
							, TABELLE_LABORE
						);
						$ergebnis = $mysqli->query($sql) OR die($mysqli->error);
						while($row = $ergebnis->fetch_assoc()) {
							echo sprintf('<option value="%1$d"%2$s>%3$s</option>', $row['id'], ($row['id'] == $data['lab_id'] ? ' selected="selected"' : '') ,$row['label']);
						}
						?>
					</select></li>
				<li><label for="exp_start">Beginn des Expepriments<img src="images/info.gif" width="17" height="17" title="Frühester Termin, der vergeben wird" alt="" /></label><input type="text" name="exp_start" id="exp_start" class="datepicker" size="11" maxlength="10"/></li>
				<li><label for="exp_end">Ende des Experiments<img src="images/info.gif" width="17" height="17" title="Spätester Termin, der vergeben wird" alt="" /></label><input type="text" name="exp_end" id="exp_end" class="datepicker" size="11" maxlength="10"/></li>
                <?php
                }
                else {
                    echo '<ul>';
                }
                ?>
				<li><label for="max_vp">Maximale Anzahl VP<img src="images/info.gif" width="17" height="17" title="Terminvergabe endet automatisch, wenn diese Anzahl an VP angemeldet ist" alt="" /></label><input type="text" name="max_vp" id="max_vp" size="11" maxlength="10" class="spinner"/></li>
				<li>
					<label for="max_simultaneous_sessions">Max. Zahl gleichzeitiger Sitzungen</label>
					<input name="max_simultaneous_sessions" id="max_simultaneous_sessions" type="text" size="5" maxlength="5" class="spinner" />
				</li>
			</ul>
        <?php
            if($auth->mayEditExpTimeFrame() || $auth->mayEditExpLab()) {
        ?>
		</div>
		<div id="tabs-2" class="tabs">
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
    <?php
    }
    else {
        echo '<br/>';
    }
    ?>
</div>

<div class="optionGroup">
	<h3>Angaben der Versuchspersonen</h3>

	<ul>
		<li>
			<input name="vpn_name" id="vpn_name" type="checkbox" value="1" checked="checked" onclick="return false" />
			<label for="vpn_name" class="nofloat">Name *</label>
		</li>
		<li>
			<input name="vpn_geschlecht" id="vpn_geschlecht" type="checkbox" value="1" />
			<label for="vpn_geschlecht" class="nofloat">Geschlecht</label>
		</li>
		<li>
			<input name="vpn_gebdat" id="vpn_gebdat" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_gebdat_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_gebdat_o').style.visibility='hidden'; };" />
			<label for="vpn_gebdat" class="nofloat">Geburtsdatum</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_gebdat_o" name="vpn_gebdat_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_fach" id="vpn_fach" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_fach_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_fach_o').style.visibility='hidden'; };" />
			<label for="vpn_fach" class="nofloat">Studienfach</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_fach_o" name="vpn_fach_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_semester" id="vpn_semester" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_semester_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_semester_o').style.visibility='hidden'; };" />
			<label for="vpn_semester" class="nofloat">Semesterzahl</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_semester_o" name="vpn_semester_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_adresse" id="vpn_adresse" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_adresse_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_adresse_o').style.visibility='hidden'; };" />
			<label for="vpn_adresse" class="nofloat">Anschrift</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_adresse_o" name="vpn_adresse_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_tele1" id="vpn_tele1" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_tele1_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_tele1_o').style.visibility='hidden'; };" />
			<label for="vpn_tele1" class="nofloat">Festnetznummer</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_tele1_o" name="vpn_tele1_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_tele2" id="vpn_tele2" type="checkbox" value="1" onclick="javascript: if ( this.checked==true ) { document.getElementById('vpn_tele2_o').style.visibility='inherit'; } else if( this.checked==false ) { document.getElementById('vpn_tele2_o').style.visibility='hidden'; };" />
			<label for="vpn_tele2" class="nofloat">Mobilnummer</label>
			&nbsp;&nbsp;&nbsp;
			<select id="vpn_tele2_o" name="vpn_tele2_o" style="visibility:hidden; ">
				<option value="0" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				<option value="1">*&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
			</select>
		</li>
		<li>
			<input name="vpn_email" id="vpn_email" type="checkbox" value="1" checked="checked" onclick="return false" />
			<label for="vpn_email" class="nofloat">E-Mail-Adresse *</label>
		</li>
		<li>
			<input name="vpn_ifreward" id="vpn_ifreward" type="checkbox" value="1" />
			<label for="vpn_ifreward" class="nofloat">Entscheidung: Geld oder VP-Stunden</label>
		</li>
		<li>
			<input name="vpn_ifbereits" id="vpn_ifbereits" type="checkbox" value="1" />
			<label for="vpn_ifbereits" class="nofloat">Frage: Hat die VP bereits an Experimenten diesesVLs teilgenommen?</label>
		</li>
		<li>
			<input name="vpn_ifbenach" id="vpn_ifbenach" type="checkbox" value="1" />
			<label for="vpn_ifbenach" class="nofloat">Frage: Möchte die VP über weitere Experimente benachrichtigt werden?</label>
		</li>
		<li>
			<i><font size="-2">(Felder markiert mit einem * müssen von den VPN ausgefüllt werden)</font></i>
		</li>
	</ul>

</div>

<br/>
<input name="createexp" type="submit" value="Experiment hinzufügen"/>

</form>

<?php
}   

require_once 'pageelements/footer.php';
