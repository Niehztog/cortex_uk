<?php
if(basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	header('HTTP/1.1 404 Not Found');
	exit;
}
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/calendar/CalendarDataProvider.class.php';
require_once __DIR__ . '/../include/class/controller/SessionController.class.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/ExperimentDataProvider.class.php';
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();
$auth = new AccessControl();

if(!isset($_GET['expid'])) {
	die('Expid missing');
}

/* WENN EXPERIMENT GELÖSCHT WURDE */
/* ------------------------------ */
if(isset($_POST['delexpq'])) {
    if(!$auth->mayEditExpTimeFrame()) {
        exit;
    }
	$delete = sprintf('DELETE FROM %1$s WHERE exp = %2$d', TABELLE_SITZUNGEN, $_GET['expid']);
	$ergebnis = $mysqli->query($delete) OR die($mysqli->error);

	$delete = sprintf('DELETE FROM %1$s WHERE exp_id = %2$d', TABELLE_EXP_TO_LAB, $_GET['expid']);
	$ergebnis = $mysqli->query($delete) OR die($mysqli->error);

	$update = "UPDATE ".TABELLE_EXPERIMENTE." SET visible = '2' WHERE id = '$_GET[expid]'";
	$ergebnis = $mysqli->query($update) OR die($mysqli->error);

	$location = sprintf(
		'http://%1$s%2$s'
		, $_SERVER['HTTP_HOST']
		, $_SERVER['PHP_SELF']
	);
	header('Location: ' . $location);
	exit;

}


/* WENN EXPERIMENT VERÄNDERT WURDE */
/* ------------------------------- */
if(isset($_POST['editexp'])) {

	prepareIncomingVariables();
	$success = validateExpData();
	
	if($success) {
		$update = sprintf( '
			UPDATE	`%1$s`
			SET		vl_name = \'%2$s\',
					vl_tele = \'%3$s\',
					vl_email = \'%4$s\',
					exp_name = \'%5$s\',
					exp_ort = \'%6$s\',
					exp_vps = \'%7$s\',
					exp_vpsnum = \'%8$s\',
					exp_geld = \'%9$s\',
					exp_geldnum = \'%10$s\',
					exp_zusatz = \'%11$s\',
					exp_mail = \'%13$s\',
					%34$s
					exp_start = \'%14$s\',
					exp_end = \'%15$s\',
					%35$s
					vpn_name = %16$d,
					vpn_geschlecht = %17$d,
					vpn_gebdat = %18$d,
					vpn_fach = %19$d,
					vpn_semester = %20$d,
					vpn_adresse = %21$d,
					vpn_tele1 = %22$d,
					vpn_tele2 = %23$d,
					vpn_email = %24$d,
					vpn_ifreward = %25$d,
					vpn_ifbereits = %26$d,
					vpn_ifbenach = %27$d,
					/* hier fehlt exp.visible*/
					max_vp = %28$d,
					%34$s
					terminvergabemodus = \'%29$s\',
					%35$s
					show_in_list = \'%30$s\',
					session_duration = %31$d,
					max_simultaneous_sessions = %32$d
			WHERE	id = %33$d'
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
			, /* 33 */ $_GET['expid']
            , /* 34 */ !$auth->mayEditExpTimeFrame() ? '/*' : ''
            , /* 35 */ !$auth->mayEditExpTimeFrame() ? '*/' : ''
		);
		$ergebnis = $mysqli->query($update) OR die($mysqli->error);

        if($auth->mayEditExpLab()){
            if('automatisch' === $_POST['terminvergabemodus'] && isset($_POST['lab_id'])) {
                $sql = sprintf( '
                    DELETE FROM %1$s WHERE exp_id = %2$d'
                    , TABELLE_EXP_TO_LAB
                    , $_GET['expid']
                );
                $ergebnis = $mysqli->query($sql) OR die($mysqli->error);

                foreach($_POST['lab_id'] as $labId) {
                    $eintrag = sprintf( '
                        INSERT IGNORE INTO %1$s (`exp_id`, `lab_id`)
                        VALUES (%2$d, %3$d)'
                        , TABELLE_EXP_TO_LAB
                        , $_GET['expid']
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
                            $_GET['expid'],
                            $temp_help,
                            $temp_session_s,
                            $temp_session_e,
                            $_POST['exp_teiln']
                        );
                    }
                }
            }
        }
    }
}


/* EXPERIMENT AKTIVIEREN/DEAKTIVIEREN */
/* --------------------------- */
if(isset($_POST['changevis'])) {
	$update = "UPDATE ".TABELLE_EXPERIMENTE." SET visible = '$_POST[visible]' WHERE id = '$_GET[expid]'";
	$ergebnis = $mysqli->query($update) OR die($mysqli->error);

	$location = sprintf(
		'http://%1$s%2$s?expid=%3$d'
		, $_SERVER['HTTP_HOST']
		, $_SERVER['PHP_SELF']
		, $_GET['expid']
	);
	header('Location: ' . $location);
	exit;
}


/* WENN TERMIN GELÖSCHT WURDE */
/* -------------------------- */
if(isset($_POST['del']) && isset($_GET['expid']) && 'admin.php' === basename($_SERVER['PHP_SELF'])) {
	$sc = new SessionController();
	$sc->delete($_POST['id']);
}



/* WENN TERMIN VERÄNDERT WURDE */
/* --------------------------- */
elseif(isset($_POST['editsess']) && 'admin.php' === basename($_SERVER['PHP_SELF']) && isset($_GET['expid'])) {
	$sc = new SessionController();
	$sc->update(
		$_POST['change_id'],
		$_GET['expid'],
		$_POST['change_termin'],
		$_POST['change_session_s'],
		$_POST['change_session_e'],
		$_POST['change_maxtn']
	);
}


/* WENN SITZUNG HINZUGEFÜGT WURDE  */
/* (dann wird sie hier eingetragen)*/
/* ------------------------------- */
elseif (isset($_POST['addsess']) && 'admin.php' === basename($_SERVER['PHP_SELF']) && isset($_GET['expid'])) {
	$sc = new SessionController();
	$sc->create(
		$_GET['expid'],
		$_POST['hinzu_termin'],
		$_POST['hinzu_session_s'],
		$_POST['hinzu_session_e'],
		$_POST['hinzu_maxtn']
	);
}

/* ANZEIGE EXPERIMENT */
/* ------------------ */
$abfrage= sprintf( '
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
				COUNT(ses.id) AS exp_sessions,
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
	LEFT JOIN	%2$s AS ses
		ON		exp.id = ses.exp
	WHERE		exp.id = %3$d
	GROUP BY	exp.id'
	, TABELLE_EXPERIMENTE
	, TABELLE_SITZUNGEN
	, $_GET['expid']
);
$erg = $mysqli->query($abfrage) or die($mysqli->error);
$data = $erg->fetch_assoc();
if(!is_array($data)) {
	die('Fehler beim Laden der Daten.');
}

$vl_name = $data['vl_name'];
$vl_email = $data['vl_email'];
$vl_tele = $data['vl_tele'];
$exp_name = $data['exp_name'];
$exp_ort = $data['exp_ort'];

require_once 'pageelements/header.php';
?>
<script type="text/javascript">
$( document ).ready(function() {
	calendarData = [<?php
		try {
			$cdp = new CalendarDataProvider();
			$calendarData = $cdp->getBookedSessionsFor((int)$_GET['expid']);
		}
		catch(Exception $e) {
			trigger_error($e, E_USER_WARNING);
			storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Fehler beim Lesen der Belegdaten.'));
		}
		if(!empty($calendarData)) {
			$first = true;
			foreach($calendarData as $termin) {
				if($first) {
					$first = false;
				}
				else {
					echo ',';
				}
				echo (string) $termin;
			}
		}
	?>];

	initCalendar(calendarData, 'viewexp');
	
	$( "#tabs" ).tabs();

	$("input[name=expedit]").on('click', function() {
		var $dialogExpExit = $('<div id="changeexp"></div>')
			.load('backend/ad_exp_change.php?expid=<?php echo $_GET['expid'];?>')
			.dialog({
				title: 'Experiment Ändern',
				autoOpen: false,
				width: 768,
				height: 500,
				modal: true,
				close: function (e, ui) { $(this).remove(); }
			});
		$dialogExpExit.dialog('open');
	});
	$("input[name=add]").on('click', function() {
		var $dialogTerminAdd = $('<div id="add"></div>')
			.load('backend/ad_termin_add.php?expid=<?php echo $_GET['expid'];?>')
			.dialog({
				title: 'Termin hinzufügen',
				autoOpen: false,
				width: 412,
				height: 270,
				modal: true,
				close: function (e, ui) { $(this).remove(); }
			});
		$dialogTerminAdd.dialog('open');
	});
	$("input[name^=edit_termin_]").on('click', function() {
		var termin_id = $(this).attr("name").substring(12);
		var $dialogTerminEdit = $('<div class="d_termin_change_'+termin_id+'"></div>')
			.load('backend/ad_termin_change.php?terminid='+termin_id)
			.dialog({
				title: 'Termin ändern',
				autoOpen: false,
				width: 412,
				height: 300,
				modal: true,
				close: function (e, ui) { $(this).remove(); }
			});
		$dialogTerminEdit.dialog('open');
	});
	$("input[name^=btn_send_msg_]").on('click', function() {
		var termin_id = $(this).attr("name").substring(13);
		var $dialogSendMsg = $('<div id="send_msg_'+termin_id+'"></div>')
			.load('backend/send_msg.php?expid=<?php echo $_GET['expid'];?>&terminid='+termin_id)
			.dialog({
				title: 'Nachricht senden',
				autoOpen: false,
				width: 668,
				height: 490,
				modal: true,
				close: function (e, ui) { $(this).remove(); }
			});
		$dialogSendMsg.dialog('open');
	});

});
</script>

<h1 style="float:left;"><?= $data['exp_name'] ?></h1>
<input id="vl_name" type="hidden" value="<?= $vl_name ?>" />
<input id="vl_tele" type="hidden" value="<?= $vl_tele ?>" />
<input id="vl_email" type="hidden" value="<?= $vl_email ?>" />
<input id="exp_name" type="hidden" value="<?= $exp_name ?>" />
<input id="exp_ort" type="hidden" value="<?= $exp_ort ?>" />
<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" style="float:left;">
	<? if ( $data['visible'] == 1 ) { ?>
	<input type="hidden" name="visible" value="0" />  
	<input name="changevis" type="submit" value="Deaktivieren" style="margin-left:10px;" />
	<? } else { ?>
	<input type="hidden" name="visible" value="1" />  
	<input name="changevis" type="submit" value="Aktivieren" style="margin-left:10px;" />
	<? } ?>
    <?php
    if($auth->mayEditExpTimeFrame()) {
        ?>
        <input name="delexpq" type="submit" value="Löschen" onclick="return confirm('Experiment wirklich löschen?');" style="margin-left:10px;"/>
        <?php
    }
    ?>
</form>

<div style="clear:both;"></div>

<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Informationen</a></li>
		<li><a href="#tabs-2">Versuchspersonen</a></li>
		<li><a href="#tabs-3">Termine</a></li>
	</ul>
	<div id="tabs-1" class="tabs">
		<table>
			<tr>
				<td colspan="3"><img src="images/arrow.gif" width="4" height="10" alt="" /> <b>Daten zum Versuchsleiter</b></td>
			</tr>
			<tr>
				<td><i>Name</i></td>
				<td colspan="2"><?= $data['vl_name'] ?></td>
			</tr>  
			<tr>
				<td><i>Telefonnummer</i></td>
				<td colspan="2"><?= $data['vl_tele'] ?></td>
			</tr> 
			<tr>
				<td><i>Email-Adresse</i></td>
				<td colspan="2"><?= $data['vl_email'] ?></td>
			</tr>   
			
			<tr><td><br /></td></tr>  
			
			<tr>
				<td colspan="3"><img src="images/arrow.gif" width="4" height="10" alt="" /> <b>Daten zum Experiment</b></td>
			</tr>
			<tr>
				<td><i>Name</i></td>
				<td colspan="2"><?= $data['exp_name'] ?></td>
			</tr>  
			<tr>
				<td><i>Beschreibung</i></td>
				<td colspan="2"><?= substr(nl2br($data['exp_zusatz']),0,150) ?>...</td>
			</tr>  
			<tr>
				<td><i>Ort</i></td>
				<td colspan="2"><?= nl2br($data['exp_ort']) ?></td>
			</tr> 
			<tr>
				<td><i>Bestätigungsmail</i></td>
				<td colspan="2"><?= substr(nl2br($data['exp_mail']),0,150) ?>...</td>
			</tr>  
			<tr>
				<td><i>VP-Stunden</i></td>
				<td colspan="2"><? if ( $data['exp_vps'] == 1 ) { echo $data['exp_vpsnum']; } else { ?><span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>	
			<tr>
				<td><i>Geld</i></td>
				<td colspan="2"><? if ( $data['exp_geld'] == 1 ) { echo $data['exp_geldnum']; } else { ?><span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?> EUR</td>
			</tr>
			
			<tr><td><br /></td></tr> 
			
			<tr>
				<td colspan="3"><img src="images/arrow.gif" width="4" height="10" alt="" /> <b>Terminvergabe</b></td>
			</tr>
			<tr>
				<td><i>In Liste anzeigen</i></td>
				<td colspan="2">
					<?php
					$idObfuscated = md5($data['id'] . ID_OBFUSCATION_SALT);
					if ( 'true' === $data['show_in_list'] ) {
						?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><?
					}
					else {
						?><span style="font-weight:bold;color:#FF0000;">&#x2717;</span><?php
						echo sprintf('&nbsp;<a href="index.php?menu=experiment&amp;expid=%1$s" style="color: #4A71D6;" target="_blank">(Geheimer Link)</a>', $idObfuscated);
					}
					?>
				</td>
			</tr>
			<tr>
				<td><i>Sitzungsdauer</i></td>
				<td colspan="2"><?= $data['session_duration'] ?> Minuten</td>
			</tr>
			<tr>
				<td><i>Modus</i></td>
				<td colspan="2"><?= $data['terminvergabemodus'] ?></td>
			</tr>
			<?php
			if('automatisch'===$data['terminvergabemodus']) {
				$abfrage = sprintf( '
					SELECT		lab.id,
								lab.label
					FROM		%1$s AS e2l
					INNER JOIN	%2$s AS lab
						ON		e2l.lab_id = lab.id
					WHERE		e2l.exp_id = %3$d'
					, TABELLE_EXP_TO_LAB
					, TABELLE_LABORE
					, $_GET['expid']
				);
				$erg_labore = $mysqli->query($abfrage) or die ($mysqli->error);
			?>
			<tr>
				<td><i>Terminvergabe&nbsp;&nbsp;&nbsp;von</i></td>
				<td colspan="2"><?php if(is_numeric($data['exp_start'])) {echo date('d.m.Y', $data['exp_start']); } else { echo $data['exp_start'];} ?></td>
			</tr>
			<tr>
				<td style="text-align:right;padding-right:20px;"><i>bis</i></td>
				<td colspan="2"><?php if(is_numeric($data['exp_end'])) {echo date('d.m.Y', $data['exp_end']); } else { echo '<i>'.$data['exp_end'].'</i>';} ?></td>
			</tr>
			<tr>
				<td><i>Max VP Anzahl</i></td>
				<td colspan="2"><?= $data['max_vp'] ?></td>
			</tr>
			<tr>
				<td><i>Gleichzeitige Sitzungen</i></td>
				<td colspan="2"><?= $data['max_simultaneous_sessions'] ?></td>
			</tr>
			<tr>
				<td><i>Zugewiesene Labore</i></td>
				<td colspan="2">
					<?php
						$first = true;
						while($labor = $erg_labore->fetch_assoc()) {
							if($first) {
								$first = false;
							}
							else {
								echo ', ';
							}
							echo sprintf('<a href="admin.php?menu=lab&id=%1$d">%2$s</a>', $labor['id'], $labor['label']);
						}
					?>
				</td>
			</tr>
			
			<?php
			}
			?>
			
			<tr><td><br /></td></tr>  
			
			<tr>
				<td colspan="3"><img src="images/arrow.gif" width="4" height="10" alt="" /> <b>Angaben der Versuchspersonen</b></td>
			</tr>
			<tr>
				<td><i>Name</i></td>
				<td colspan="2"><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font></td>
			</tr>  
			<tr>
				<td><i>Geschlecht</i></td>
				<td colspan="2"><? if ( $data['vpn_geschlecht'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Geburtsdatum</i></td>
				<td colspan="2"><? if ( $data['vpn_gebdat'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_gebdat'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Studienfach</i></td>
				<td colspan="2"><? if ( $data['vpn_fach'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_fach'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Semester</i></td>
				<td colspan="2"><? if ( $data['vpn_semester'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_semester'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Adresse</i></td>
				<td colspan="2"><? if ( $data['vpn_adresse'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_adresse'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Telefon (Festznetz)</i></td>
				<td colspan="2"><? if ( $data['vpn_tele1'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_tele1'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Telefon (Mobil)</i></td>
				<td colspan="2"><? if ( $data['vpn_tele2'] == 2 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font><? } else if ( $data['vpn_tele2'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Email-Adresse</i></td>
				<td colspan="2"><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><font color="#00FF00">*</font></td>
			</tr>  
			<tr>
				<td><i>Geld/VP-Stunden?</i></td>
				<td colspan="2"><? if ( $data['vpn_ifreward'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Bereits?</i></td>
				<td colspan="2"><? if ( $data['vpn_ifbereits'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>  
			<tr>
				<td><i>Liste?</i></td>
				<td colspan="2"><? if ( $data['vpn_ifbenach'] == 1 ) { ?><span style="font-weight:bold;color:#00FF00;">&#x2713;</span><? } else { ?> <span style="font-weight:bold;color:#FF0000;">&#x2717;</span><? } ?></td>
			</tr>
		</table>
		<br />
		<input name="expedit" type="button" value="Bearbeiten" />
	</div>
	
	<div id="tabs-2" class="tabs">
		<?php
		require_once 'backend/ad_vp_list.php';
		?>
	</div>
	
	<div id="tabs-3" class="tabs">
		<div id="calendar"></div>
		
		Anzahl Sitzungen:&nbsp;<?php echo $data['exp_sessions'];?><br/>
		
		<table class="optionGroup">
			<tr>
				<td class="termin"><b>Datum</b></td>
				<td class="termin"><b>Beginn</b></td>
				<td class="termin"><b>Ende</b></td>
				<td class="termin"><b>Ort</b></td>
				<td class="termin"><b>VP</b></td>
				<td class="termin"><b>Teilnehmer</b></td>
				<td class="termin">&nbsp;</td>
			</tr>
			<?php
			
			$abfrage = sprintf( '
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
				ORDER BY	ses.tag ASC, ses.session_s ASC'
				, TABELLE_SITZUNGEN
				, TABELLE_LABORE
				, $_GET['expid']
			);
			$erg = $mysqli->query($abfrage);
			while ($termin = $erg->fetch_assoc()) {
				$terminDatum = formatMysqlDate($termin['tag']);
				$beginn = substr($termin['session_s'], 0, 5);
				$ende = substr($termin['session_e'], 0, 5);
				$maxtn = $termin['maxtn'];
				
				$abfrage3 = sprintf(
					'SELECT gebdat, vorname, nachname, geschlecht, telefon1, telefon2, email, vorname, nachname FROM %1$s WHERE `exp` = %2$d AND termin = %3$d'
					, TABELLE_VERSUCHSPERSONEN
					, $_GET['expid']
					, $termin['id']
				);
				$erg3 = $mysqli->query($abfrage3);
                if('automatisch'===$data['terminvergabemodus'] && 0 === $erg3->num_rows) {
                    continue;
                }
				?>
				<tr>
				<td class="termin">
					<input type="hidden" id="termin<?php echo $termin['id'];?>" value="<?php echo $terminDatum;?>"  />
					<input type="hidden" id="beginn<?php echo $termin['id'];?>" value="<?php echo $beginn;?>"  />
					<input type="hidden" id="ende<?php echo $termin['id'];?>" value="<?php echo $ende;?>"  />
					<input type="hidden" id="maxtn<?php echo $termin['id'];?>" value="<?php echo $maxtn;?>"  />
					<?php echo $terminDatum;?>
				</td>
				<td class="termin">
					<?php echo $beginn;?>
				</td>
				<td class="termin">
					<?php echo $ende;?>
				</td>
				<td class="termin">
					<?php
                    if(empty($termin['ort'])) {
                        echo '<span style="font-style:italic;color:#d3d3d3">[manueller&nbsp;Termin]</span>';
                    }
                    else {
                        echo $termin['ort'];
                    }
                    ?>
				</td>
				<td class="termin">
					<?php
						$edp = new ExperimentDataProvider((int)$_GET['expid']);
						$vpcur = $erg3->num_rows;
						$vpmax = $edp->getSignUpCountMax();
						
						echo $vpcur;
						
						if($vpmax > 0) {
							echo '/' . $vpmax;
						}
					?>	
				</td>
				<td class="termin">
					<?php
		
					
					$gebdat = null;
					while ($termin3 = $erg3->fetch_assoc()) {
						if(!empty($gebdat)) {
							echo '<br/>';
						}
						$gebdat = formatMysqlDate($termin3['gebdat']);  
						?><span title="<b><u><?php echo $termin3['vorname'] . ' ' . $termin3['nachname']; ?></u></b><br /><?php if ( $data['vpn_geschlecht'] > 0 ) { ?><b>Geschlecht: </b><?php echo $termin3['geschlecht'] . "<br />"; }; ?><?php if ( $data['vpn_gebdat'] > 0 ) { ?><b>Geburtsdatum: </b><?php echo $gebdat  . "<br />"; }; ?><?php if ( $data['vpn_tele1'] > 0 ) { ?><b>Telefon 1: </b><?php echo $termin3['telefon1'] . "<br />"; }; ?><?php if ( $data['vpn_tele2'] > 0 ) { ?><b>Telefon 2: </b><?php echo $termin3['telefon2'] . "<br />"; }; ?><?php if ( $data['vpn_email'] > 0 ) { ?><b>Email: </b><?php echo $termin3['email']; }; ?>">
						<?php
						echo substr($termin3['vorname'],0,1) . '. ' . $termin3['nachname']; 
						?>
						</span>
						<?php
					}

					?>&nbsp;
				</td>
				<td class="termin">
					<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data">
						<input type="button" name="btn_send_msg_<?php echo $termin['id'];?>" value="Nachricht">	
						<input type="hidden" name="id" value="<?php echo $termin['id']; ?>" />
						<input type="hidden" name="expid" value="<?php echo $_GET['expid'];?>" />
						<input type="hidden" name="admin" value="1" />  
						<input type="button" name="edit_termin_<?php echo $termin['id']; ?>" value="Ändern" />
						<input type="submit" name="del" value="Löschen" onclick="return confirm('Termin wirklich löschen?');" />
					</form>
				</td>
			</tr>
			<?
			}
			?>
		</table>
		<?php
		if('manuell'===$data['terminvergabemodus']) {
			?>
			<br />
			<input name="add" type="button" value="Termin hinzufügen" />
			<?php
		}?>
	</div>
</div>
<?php

require_once 'pageelements/footer.php';
