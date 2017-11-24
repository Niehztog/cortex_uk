<?php
if(basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	header('HTTP/1.1 404 Not Found');
	exit;
}
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/calendar/CalendarDataProvider.class.php';
require_once __DIR__ . '/../include/class/controller/SessionController.class.php';
require_once __DIR__ . '/../include/class/user/AccessControl.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();
$auth = new AccessControl();

function insertJavaScript(TimeSlotPermanentCollection $calendarData = null) {
	?>
		<script type="text/javascript">
			$( document ).ready(function() {
				$(".timepicker").timePicker({step:30, startTime:"08:00", endTime:"19:00"});
				$(".spinner").spinner({
					spin: function( event, ui ) {
						if ( ui.value < 1 ) {
							$( this ).spinner( "value", 1 );
							return false;
						}
					}
				});

				calendarData = [<?php
					if(null !== $calendarData) {
						echo $calendarData;
					}
				?>];
				
				initCalendar(calendarData, 'manage');
				$( "#tabs" ).tabs();
				$("form[id=create],form[id=change]").on('submit', function( event ) {
					//event.preventDefault();
					//seen = []
					calendarData = $('#calendar').fullCalendar('clientEvents');
					var index;
					for (index = 0; index < calendarData.length; ++index) {
						if(calendarData[index].editable == false) {
							//delete calendarData[index];
							calendarData.splice(index, 1);
							index--;
						}
						else {
							var curdate = calendarData[index];
							curdate.start.setHours(curdate.start.getHours() - curdate.start.getTimezoneOffset() / 60);
							curdate.end.setHours(curdate.end.getHours() - curdate.end.getTimezoneOffset() / 60);
						}
					}
					calendarDataJson = JSON.stringify(calendarData, function(key, val) {
						if(key=='source'||key[0]=='_') return undefined;
						return val
					});

					$(this).append('<input type="hidden" name="calendar" value="'+encodeURIComponent(calendarDataJson)+'" />');
					return true;
				});
			});
		</script>
	<?php
}
/**
 * öffnungszeiten format validieren,
 * validieren, dass textfelder nicht leer gelassen wurden
 */
function validateInputData() {
	$ergebnis = true;
	foreach(array('label','address','room_number','capacity') as $parameter) {
		$pruefwert = trim($_POST[$parameter]);
		if(empty($pruefwert)) {
			$nachricht = sprintf('Das Feld "%1$s" darf nicht leer gelassen werden.', $parameter);
			storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, $nachricht));
			$ergebnis = false;
		}
	}
	return $ergebnis;
}

/* LABOR HINZUFÜGEN */
/* -------------------------- */
if(isset($_GET['action']) && 'create' === $_GET['action'] && !isset($_POST['createlab'])) {
    if(!$auth->mayEditLabInfo()) {
        exit;
    }
	require_once 'pageelements/header.php';
	//displayMessagesFromSession();
	insertJavaScript();
	?>
	
	<form action="admin.php?menu=lab" method="post" enctype="multipart/form-data" name="create" id="create">

	<h1>Labor hinzufügen</h1>

	<div class="optionGroup">
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1">Informationen</a></li>
				<li><a href="#tabs-2">Öffnungszeiten</a></li>
			</ul>
			<div id="tabs-1" class="tabs">
				<ul>
					<li><label for="label">Name des Labors</label><INPUT type="text" name="label" id="label" size="43"></li>
					<li><label for="address">Adresse</label><textarea name="address" id="address" rows="5" cols="40"></textarea></li>
					<li><label for="room_number">Zimmernummer</label><INPUT type="text" name="room_number" id="room_number" size="43"></li>
					<li><label for="capacity">Anzahl der Arbeitsplätze</label><INPUT type="text" name="capacity" id="capacity" class="spinner" size="40"></li>
					<li><label for="active">Aktiviert</label><input type="checkbox" name="active" id="active"/></li>
				</ul>
			</div>
			<div id="tabs-2" class="tabs">
				<div id='calendar'></div>
			</div>
		</div>
	</div>
	
	<br />
	
	<!-- >i><font size="-2">(Felder markiert mit einem * müssen ausgefüllt werden)</font></i-->
	
	<input type="submit" name="createlab" value="Labor hinzufügen"/>

	</form>
	
	
	<?php 	require_once 'pageelements/footer.php';
}
elseif( isset($_POST['createlab']) ) {
    if(!$auth->mayEditLabInfo()) {
        exit;
    }
	$calendarData = json_decode(urldecode($_POST['calendar']));

	//daten validieren
	if( true === validateInputData() && null !== $calendarData) {
		
		$create = sprintf( '
			INSERT INTO %1$s
			(label,address,room_number,capacity,active)
			VALUES(\'%2$s\',\'%3$s\',\'%4$s\',%5$d,\'%6$s\')'
			, /* 1 */ TABELLE_LABORE
			, /* 2 */ $mysqli->real_escape_string($_POST['label'])
			, /* 3 */ $mysqli->real_escape_string($_POST['address'])
			, /* 4 */ $mysqli->real_escape_string($_POST['room_number'])
			, /* 5 */ $_POST['capacity']
			, /* 6 */ isset($_POST['active']) ? 'true' : 'false'
		);
		$ergebnis = $mysqli->query($create);
		if(!$ergebnis) {
			die($mysqli->error);
		}
		
		$labId = $mysqli->insert_id;
		
		$sc = new SessionController();
		
		foreach($calendarData as $termin) {

			$start = strtotimeIgnoreTimeZone($termin->start);
			$end = strtotimeIgnoreTimeZone($termin->end);

			$sc->create(
				0, //exp id
				date('d.m.Y', $start),
				date('H:i:s', $start),
				date('H:i:s', $end),
				0, //max tn
				$labId
			);

		}

		storeMessageInSession(sprintf(MESSAGE_BOX_SUCCESSR, 'Labor erfolgreich eingetragen.'));
		header(
			sprintf(
				'Location: http://%1$s%2$s?id=%3$d%4$s'
				, $_SERVER['HTTP_HOST']
				, $_SERVER['PHP_SELF']
				, $labId
				, isset($_GET['menu']) ? '&menu=' . $_GET['menu'] : ''
			)
		);

	}
	elseif(null === $calendarData) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Interner Fehler beim Dekodieren der Kalendardaten.'));
	}
	else {
		//eingegebene daten validieren nicht, daher url parameter in lokales array übertragen
		//zur späteren anzeige
		$daten = $_POST;
		$daten['active'] = isset($_POST['active']) ? 'true' : 'false';
	}
}
elseif(isset($_POST['changelab'])) {

	$calendarData = json_decode(urldecode($_POST['calendar']));

	//daten validieren
	if( (!$auth->mayEditLabInfo() || true === validateInputData()) && null !== $calendarData) {
        if($auth->mayEditLabInfo()) {
            $update = sprintf( '
                UPDATE	%1$s
                SET		label = \'%2$s\',
                        address = \'%3$s\',
                        room_number = \'%4$s\',
                        capacity = %5$d,
                        active = \'%6$s\'
                WHERE	id = %7$d'
                , /* 1 */ TABELLE_LABORE
                , /* 2 */ $mysqli->real_escape_string($_POST['label'])
                , /* 3 */ $mysqli->real_escape_string($_POST['address'])
                , /* 4 */ $mysqli->real_escape_string($_POST['room_number'])
                , /* 5 */ $_POST['capacity']
                , /* 6 */ isset($_POST['active']) ? 'true' : 'false'
                , /* 7 */ $_GET['id']
            );
            $result = $mysqli->query($update);
            if(!$result) {
                die($mysqli->error);
            }
        }
		$sc = new SessionController();
		$terminIds = array();

		foreach($calendarData as $termin) {

			$start = strtotimeIgnoreTimeZone($termin->start);
			$end = strtotimeIgnoreTimeZone($termin->end);

			if(empty($termin->id)) {
				//neuer termin
				$terminId = $sc->create(
					0, //exp id
					date('d.m.Y', $start),
					date('H:i:s', $start),
					date('H:i:s', $end),
					0, //max tn
					$_GET['id']
				);
			}
			else {
				//vorhandenen termin ändern
				$terminId = $termin->id;
				$sc->update(
					$terminId,
					0, //exp id
					date('d.m.Y', $start),
					date('H:i:s', $start),
					date('H:i:s', $end),
					0, //max tn
					$_GET['id']
				);
			}
			$terminIds[] = $terminId;
		}

        if(!empty($terminIds)) {
            $sql = sprintf('
			DELETE FROM %1$s WHERE lab_id = %2$d AND exp = 0 AND id NOT IN (%3$s)'
                , TABELLE_SITZUNGEN
                , $_GET['id']
                , implode(',', $terminIds)
            );

            $result = $mysqli->query($sql);
        }
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim Löschen von Sitzungen für Labor id %1$d', $_GET['id'])));
		}
		else {
			storeMessageInSession(sprintf(MESSAGE_BOX_SUCCESSR, 'Labor erfolgreich geändert.'));
		}
		
		header(
			sprintf(
				'Location: http://%1$s%2$s?id=%3$d%4$s'
				, $_SERVER['HTTP_HOST']
				, $_SERVER['PHP_SELF']
				, $_GET['id']
				, isset($_GET['menu']) ? '&menu=' . $_GET['menu'] : ''
			)
		);

	}
	elseif(null === $calendarData) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Interner Fehler beim Dekodieren der Kalendardaten.'));
	}
	else {
		//eingegebene daten validieren nicht, daher url parameter in lokales array übertragen
		//zur späteren anzeige
		$daten = $_POST;
		$daten['active'] = isset($_POST['active']) ? 'true' : 'false';
	}
}
elseif(isset($_POST['dellab']) && isset($_GET['id'])) {
    if(!$auth->mayEditLabInfo()) {
        exit;
    }
	$delete = sprintf( '
		DELETE FROM %1$s WHERE id = %2$d LIMIT 1'
		, /* 1 */ TABELLE_LABORE
		, /* 2 */ $_GET['id']
	);
	$ergebnis = $mysqli->query($delete);
	if(!$ergebnis) {
		die($mysqli->error);
	}
	
	$sql = sprintf( '
		DELETE FROM %1$s WHERE lab_id = %2$d AND exp = 0'
		, TABELLE_SITZUNGEN
		, $_GET['id']
	);
	$result = $mysqli->query($sql);
	if(false === $result) {
		trigger_error($mysqli->error, E_USER_WARNING);
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim Löschen von Sitzungen für Labor id %1$d', $_GET['id'])));
	}
	
	$sql = sprintf( '
		UPDATE %1$s SET lab_id = 0 WHERE lab_id = %2$d'
		, TABELLE_SITZUNGEN
		, $_GET['id']
	);
	$result = $mysqli->query($sql);
	if(false === $result) {
		trigger_error($mysqli->error, E_USER_WARNING);
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Fehler beim Löschen der Laborzuordnung von Sitzungen für Labor id %1$d', $_GET['id'])));
	}
	else {
		storeMessageInSession(sprintf(MESSAGE_BOX_SUCCESSR, 'Labor erfolgreich gelöscht.'));
	}
	header(
		sprintf(
			'Location: http://%1$s%2$s%3$s'
			, $_SERVER['HTTP_HOST']
			, $_SERVER['PHP_SELF']
			, isset($_GET['menu']) ? '?menu=' . $_GET['menu'] : ''
		)
	);
}
elseif(!empty($_GET['id'])) {
	//vorhandenes Labor zum verändern anzeigen

	$abfrage = sprintf('
		SELECT	id,
				label,
				address,
				room_number,
				capacity,
				active
		FROM	%1$s
		WHERE	id = %2$d'
			, TABELLE_LABORE
			, $_GET['id']
	);
	$ergebnis = $mysqli->query($abfrage) OR die($mysqli->error);
	$daten = $ergebnis->fetch_assoc();
	if( !is_array($daten)) {
		die('Konnte Daten zu Labor nicht finden.');
	}
	
	try {
		$cdp = new CalendarDataProvider();
		$calendarDaten = $cdp->getOpeningHoursFor($_GET['id']);
		$calendarDaten->unite(
			$cdp->getAllocationData(
				$_GET['id'],
				null,
				array(
					'setEditable' => false,
					'setBackgroundColor' => '#F3F781'
				)
			)
		);
	}
	catch(Exception $e) {
		trigger_error($e, E_USER_WARNING);
		die('Konnte Daten zu Labor nicht laden.');
	}
}
else {
	require_once 'pageelements/header.php';
	//displayMessagesFromSession();
	require_once 'pageelements/footer.php';
}

if( isset( $daten ) ) {
	//anzeige des formulars, entweder um vorhandenes labor zu ändern oder neues einzutragen
	$versucheAktion = isset($_GET['id']) ? 'ändern' : 'hinzufügen';
	$action = isset($_GET['id']) ? 'change' : 'create';
	require_once 'pageelements/header.php';
	//displayMessagesFromSession();
	insertJavaScript(!empty($calendarDaten)?$calendarDaten:null);
	
	?>
	<script type="text/javascript">
		$( document ).ready(function() {
			$("input[id=show_allocation_chk]").on('change', function() {
				$('#calendar').fullCalendar('rerenderEvents');
			});
			
		});
	</script>
	
	<form action="admin.php?menu=lab&id=<?php echo (int)$_GET['id'];?>" method="post" enctype="multipart/form-data" name="<?php echo $action;?>" id="<?php echo $action;?>">

	<h1 style="display:inline;">Labor <?php if(isset($daten['label'])){echo '"'.$daten['label'].'" ';}?><?php echo $versucheAktion;?></h1>
	<?php if(isset($_GET['id']) && $auth->mayEditLabInfo()) {?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="dellab" type="submit" value="Löschen" onclick="return confirm('Labor wirklich löschen?');" /><?php }?>
	
	<div class="optionGroup">
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1">Informationen</a></li>
				<li><a href="#tabs-2">Öffnungszeiten</a></li>
				<li><a href="#tabs-3">Experimente</a></li>
			</ul>
			<div id="tabs-1" class="tabs">
				<ul>
					<li><label for="label">Name des Labors</label><?php if($auth->mayEditLabInfo()){?><INPUT type="text" name="label" id="label" size="43" value="<?php }echo $daten['label']; if($auth->mayEditLabInfo()){?>"><?php }?></li>
					<li><label for="address">Adresse</label><?php if($auth->mayEditLabInfo()){?><textarea name="address" id="address" rows="5" cols="40"><?php }echo $daten['address']; if($auth->mayEditLabInfo()){?></textarea><?php }?></li>
					<li><label for="room_number">Zimmernummer</label><?php if($auth->mayEditLabInfo()){?><INPUT type="text" name="room_number" id="room_number" size="43" value="<?php }echo $daten['room_number']; if($auth->mayEditLabInfo()){?>"><?php }?></li>
					<li><label for="capacity">Anzahl der Arbeitsplätze</label><?php if($auth->mayEditLabInfo()){?><INPUT type="text" name="capacity" id="capacity" size="40" value="<?php }echo $daten['capacity']; if($auth->mayEditLabInfo()){?>" class="spinner"><?php }?></li>
					<li><label for="active">Aktiviert</label><input type="checkbox" name="active" id="active"<?php if('true'===$daten['active']){echo ' checked="checked"';}?>/></li>
				</ul>
			</div>
			<div id="tabs-2" class="tabs">
				<div id='calendar'></div>
				<span>
					<input type="checkbox" id="show_allocation_chk" checked="checked" />
					<label for="show_allocation_chk" style="width:auto;float:none;">Anmeldungen in Kalender anzeigen</label>
				</span>
			</div>
			<div id="tabs-3" class="tabs">
				<ul>
				<?php 
					$sql = sprintf( '
						SELECT		exp.id,
									exp.exp_name,
									exp.exp_start,
									exp.exp_end,
									exp.terminvergabemodus,
									exp.session_duration,
									exp.max_simultaneous_sessions
						FROM		%1$s AS exp
						LEFT JOIN	%2$s AS e2l
							ON		e2l.exp_id = exp.id
						WHERE		e2l.lab_id = %3$d'
						, TABELLE_EXPERIMENTE
						, TABELLE_EXP_TO_LAB
						, $_GET['id']
					);
					$ergebnis = $mysqli->query($sql) OR die($mysqli->error);
					
					if(0 === $ergebnis->num_rows) {
						echo '<li><i>(noch keine)</i></li>';
					}
					
					while($row = $ergebnis->fetch_assoc()) {
						if('automatisch' === $row['terminvergabemodus']) {
							$laborStr = '<li style="white-space:normal;"><a href="admin.php?expid=%1$d">%2$s</a>&nbsp;(von %3$s bis %4$s): %5$se Terminvergabe, Sitzungsdauer %6$d Minuten, max. %7$d gleichzeitige Sitzungen</li>';
						}
						else {
							$laborStr = '<li style="white-space:normal;"><a href="admin.php?expid=%1$d">%2$s</a>: %5$se Terminvergabe, Sitzungsdauer %6$d Minuten</li>';
						}
						echo sprintf(
							$laborStr
							, /* 1 */	$row['id']
							, /* 2 */$row['exp_name']
							, /* 3 */$row['exp_start']
							, /* 4 */$row['exp_end']
							, /* 5 */$row['terminvergabemodus']
							, /* 6 */$row['session_duration']
							, /* 7 */$row['max_simultaneous_sessions']
						);
					}
				?>
				</ul>
			</div>
		</div>
	</div>

	<br />
	
	<!-- i><font size="-2">(Felder markiert mit einem * müssen ausgefüllt werden)</font></i-->
	
	<br /><br />
	
	<input name="<?php echo $action;?>lab" type="submit" value="Labor <?php echo $versucheAktion;?>"/>

	</form>
	
	<?php
    require_once 'pageelements/footer.php';
}