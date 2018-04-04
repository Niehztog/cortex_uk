<?php
require_once __DIR__ . '/include/functions.php';
require_once __DIR__ . '/include/config.php';
require_once __DIR__ . '/include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/include/class/calendar/CalendarDataProvider.class.php';
require_once __DIR__ . '/include/class/controller/SessionController.class.php';
require_once __DIR__ . '/include/class/ExperimentDataProvider.class.php';
require_once __DIR__ . '/include/class/settings/EmailBlocking.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

/************************/
/* LAUFENDE EXPERIMENTE */
/************************/

if(!isset($_GET['menu'])) {
	require_once 'pageelements/header.php';
	$edp = new ExperimentDataProvider(null);
	try {
        $laufendeExperimente = $edp->getExpListSignUpAllowed();
    }
    catch(Exception $e) {
        $laufendeExperimente = array();
    }
	if(count($laufendeExperimente)) {
		echo '<h1>Laufende Experimente</h1>';
	}
	else {
		echo '<h1>Derzeit laufen keine Experimente. Bitte schauen Sie zu einem späteren Zeitpunkt noch einmal vorbei.</h1>';
	}

	foreach($laufendeExperimente as $data) {
		$idObfuscated = md5($data['id'] . ID_OBFUSCATION_SALT);
		?>	
		<h2><a href="index.php?menu=experiment&amp;expid=<?php echo $idObfuscated;?>" style="color: #4A71D6;">&nbsp;&nbsp;<img src="images/arrow.gif" width="4" height="10" border="0" alt="" />&nbsp;<?php echo $data['exp_name']; ?></a></h2>
		<div id="mainframe_exp">&nbsp;&nbsp;&nbsp;<a href="index.php?menu=experiment&amp;expid=<?php echo $idObfuscated;?>"><?php echo htmlspecialchars(substr($data['exp_zusatz'],0,250)); ?> [...]</a></div>
		<?php
	}
}


/******************************/  
/* TERMINÜBERSICHT EXPERIMENT */  
/******************************/

elseif(($_GET['menu'] == 'experiment') && (isset($_GET['expid'])) && (!isset($_POST['chosendate']))&& (!isset($_POST['add']))) {
	try {
		$edp = ExperimentDataProvider::getInstanceByEncryptedId($_GET['expid']);
		$expId = $edp->getExpId();
		if(!$edp->isSignUpAllowed()) {
			storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Anmeldungen für dieses Experiment sind derzeit nicht möglich. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal.'));
			require_once 'pageelements/header.php';
			require_once 'pageelements/footer.php';
			exit;
		}
	}
	catch(Exception $e) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Interner Fehler. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal.'));
		require_once 'pageelements/header.php';
		require_once 'pageelements/footer.php';
		exit;
	}

	$abfrage2 = sprintf( '
		SELECT	*
		FROM	%1$s
		WHERE	id = %2$d
			AND	visible = "1"'
		, TABELLE_EXPERIMENTE
		, $expId
	);

	$erg4 = $mysqli->query($abfrage2);
	if(0 === $erg4->num_rows) {
		die('Experiment nicht gefunden');
	}
	$data2 = $erg4->fetch_assoc();  
	
	try {
		$cdp = new CalendarDataProvider();
		$calendarData = $cdp->getAvailableSessionsFor($expId);
	}
	catch(Exception $e) {
		trigger_error($e, E_USER_WARNING);
		echo '<!--' . $e . '-->';
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__)));
        $calendarData = '';
	}
	
	require_once 'pageelements/header.php';
	?>
	
	<script type="text/javascript">
		$( document ).ready(function() {
			calendarData = [<?php echo $calendarData;?>];
			initCalendar(calendarData, 'signup');
			$( "button#cal_prev" ).button({
				icons: {
					primary: "ui-icon-triangle-1-w"
				},
				text: false
			});
			$( "button#cal_next" ).button({
				icons: {
					primary: "ui-icon-triangle-1-e"
				},
				text: false
			});

			$( "button#cal_prev" ).on('click', function() {
				$('#calendar').fullCalendar('prev');
			});
			$( "button#cal_next" ).on('click', function() {
				$('#calendar').fullCalendar('next');
			});

		});
	</script>
	
	
	<h1><?php echo $data2['exp_name']; ?></h1>

	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Informationen</h2></td>	
		</tr>
		<tr>
			<td width="550px"><?php echo nl2br($data2['exp_zusatz']); ?><br /><br />
	<?php if ( $data2['exp_vps'] == 1  || $data2['exp_geld'] == 1 )
		{
	?>
	Sie erhalten für ihre Teilnahme
	<?php 	if ( $data2['exp_vps'] == 1 && $data2['exp_geld'] == 1)
		  {
	?>	
	<b><?php echo $data2['exp_vpsnum']; ?> VP-Stunden</b> oder <b><?php echo $data2['exp_geldnum']; ?> EURO</b>.
	<?php 		  }
	else if ( $data2['exp_geld'] == 1 && $data2['exp_vps'] < 1)
		  {
	?>	
	<b><?php echo $data2['exp_geldnum']; ?> EURO</b>.
	<?php 		  }
	else if ( $data2['exp_vps'] == 1 && $data2['exp_geld'] < 1)
		  {
	?>	
	<b><?php echo $data2['exp_vpsnum']; ?> VP-Stunden</b>.
	<?php 		  }
		}
	?>
			</td>
		</tr>
	</table>

	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Adresse</h2></td>	
		</tr>
		<tr>
			<td width="550px">
				Das Experiment findet an folgender Adresse statt:<br />
				<b><?php echo nl2br($data2['exp_ort']); ?></b>
			</td>
		</tr>
	</table>

	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Kontakt</h2></td>	
		</tr>
		<tr>
			<td><div><?php echo $data2['vl_name']; ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="mailto:<?php echo $data2['vl_email']; ?>" id="mainframe_exp_link"><?php echo $data2['vl_email']; ?></a><?php if ($data2['vl_tele'] != "") { ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $data2['vl_tele']; ?><?php } ?><br /></div></td>
		</tr>
	</table>

	<h2>Termine</h2>
	<div id='calendar'></div>

	<div id="calendar_footer">
		<button id="cal_prev" style="height:20px;"></button>
		<button id="cal_next" style="height:20px;"></button>
	</div>
	
	<?php 
}

/*********************************************/
/* ANMELDEFORMULAR FÜR EINEN TERMIN ANZEIGEN */
/*********************************************/	
  
elseif (isset($_POST['chosendate'])) {

	$edp = ExperimentDataProvider::getInstanceByEncryptedId($_GET['expid']);
	$expId = $edp->getExpId();

	$abfrage = sprintf( '
		SELECT	*
		FROM	%1$s
		WHERE	id = %2$d
			AND	visible = "1"'
		, TABELLE_EXPERIMENTE
		, $expId
	);
	$erg5 = $mysqli->query($abfrage);
	$data = $erg5->fetch_assoc(); 
	
	require_once 'pageelements/header.php';
	?>	
		
	<table> 
	<tr>
		<td><h1><?php echo $data['exp_name']; ?></h1></td>
	</tr>
	<tr>
		<td>
	<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="create">
	<table style="margin-left:15px;">
	<tr>
		<td colspan="4" class="ad_edit_headline"><h2>Anmeldung</h2></td>
	</tr>
	<tr>					
		<td width="180px" class="termin"><b>Termin</b></td>
		<td width="220px" class="termin">
	
	<?php
		if('automatisch' === $data['terminvergabemodus']) {
			try {
				$termin = TimeSlotTemporary::getInstanceByTemporaryId($_POST['id']);
			}
			catch(Exception $e) {
				trigger_error($e, E_USER_WARNING);
				die('interner fehler');
			}
			$terminStart = $termin->getStart();
			$terminEnd = $termin->getEnd();
			echo $terminStart->format('d.m.Y') . '&nbsp;' . $terminStart->format('H:i') . '&nbsp;' . '&#8212;' . '&nbsp;' . $terminEnd->format('H:i') . '&nbsp;Uhr';
			echo sprintf('<input type="hidden" name="termin" value="%1$s">', htmlspecialchars($_POST['id']));
		}
		else {
	?>
		<select name="termin">
	<?php
	$abfrage2 = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE `exp` = '$expId' ORDER BY tag, session_s ASC" ;
	$erg2 = $mysqli->query($abfrage2);
	$chosenDateFound = false;
	while ($data2 = $erg2->fetch_assoc()) {
		$abfrage3 = "SELECT COUNT(termin) AS count FROM ".TABELLE_VERSUCHSPERSONEN." WHERE `exp` = '$expId' AND `termin` = '$data2[id]'";
		$erg3 = $mysqli->query($abfrage3);
		$data3 = $erg3->fetch_assoc();
		$signUpCount = (int)$data3['count'];

		if ( $data2['maxtn'] > $signUpCount ) {
		?>
		  <option value="<?php echo $data2['id']; ?>" <?php if ($data2['id'] == $_POST['id']) {$chosenDateFound = true;?>selected<?php } ?>><?php echo formatMysqlDate($data2['tag']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . substr($data2['session_s'], 0, 5) . '-' . substr($data2['session_e'], 0, 5);?></option>
		<?php
		}
	}
	?>
		</select>
		
		<?php
			if(!$chosenDateFound) {
				?>
				<br/><font size="-2" color="#FF0000"><i>Achtung: Der gewählte Termin ist nichtmehr verfügbar.</i></font>
				<?php
			}

			}
		?>
		
		</td>
		<td width="70px" class="termin"></td>
		<td class="termin"></td>	
	</tr>
	<tr><td><br /></td></tr>
	<tr>					
		<td class="eintragen"><b>Vorname</b> *</td>
		<td class="eintragen"><input name="vorname" type="text" size="45" maxlength="255" />
		<td class="eintragen"></td>
		<td class="eintragen"></td>
	</tr>
	<tr>					
		<td class="eintragen"><b>Nachname</b> *</td>
		<td class="eintragen"><input name="nachname" type="text" size="45" maxlength="255" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>
	</tr>
	<?php
	if ( $data['vpn_geschlecht'] > 0 ) {
		?>
		<script type="text/javascript">
			$( document ).ready(function() {
				initDatepicker();
			});
		</script>
		<tr>					
			<td class="eintragen"><b>Geschlecht</b></td>
			<td class="eintragen">
				<select name="geschlecht">
					<option value="männlich">männlich</option>
					<option value="weiblich">weiblich</option>
				</select>
			</td>
			<td class="eintragen"></td>	
		</tr>
		<?php 	};
	if ( $data['vpn_gebdat'] > 0 ) {
		?>  
		<tr>					
			<td class="eintragen"><b>Geburtsdatum</b> <?php if ( $data['vpn_gebdat'] == 2 ) { ?>*<?php } ?></td>
			<td class="eintragen">
				<input type="text" name="date2" class="datepicker" size="12" maxlength="10" />&nbsp;&nbsp;&nbsp;
			</td>
			<td class="eintragen"><img src="images/info.gif" width="17" height="17" title="<b>Geburtsdatum</b><br />Bitte im Format <b>tt.mm.jjjj</b> angeben,<br />also z.B. 24.06.1984" alt="" />	</td>	
		</tr>
		<?php 	};
	if ( $data['vpn_fach'] > 0 )
		{  
	?>  
	<tr>					
		<td class="eintragen"><b>Studienfach</b> <?php if ( $data['vpn_fach'] == 2 ) { ?>*<?php } ?></td>
		<td class="eintragen"><input name="fach" type="text" size="45" maxlength="255" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>
	<?php 		};
	if ( $data['vpn_semester'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Semester</b> <?php if ( $data['vpn_semester'] == 2 ) { ?>*<?php } ?></td>
		<td class="eintragen"><input name="semester" type="text" size="45" maxlength="10" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>
	<?php 		};
	if ( $data['vpn_adresse'] > 0 )
		{  
	?>	
	<tr>					
		<td class="eintragen"><b>Adresse</b> <?php if ( $data['vpn_adresse'] == 2 ) { ?>*<?php } ?></td>
		<td class="eintragen"><textarea name="anschrift" rows="3" cols="40"></textarea><br /><br /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>		
	</tr>  
	 <?php 		};
	if ( $data['vpn_tele1'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Telefon 1</b> <?php if ( $data['vpn_tele1'] == 2 ) { ?>*<?php } ?></td>
		<td class="eintragen"><input name="telefon1" type="text" size="45" maxlength="20" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr> 
	<?php 		};
	if ( $data['vpn_tele2'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Telefon</b> <?php if ( $data['vpn_tele2'] == 2 ) { ?>*<?php } ?></td>
		<td class="eintragen"><input name="telefon2" type="text" size="45" maxlength="20" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>  
	<?php 		};
	if ( $data['vpn_email'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Email-Adresse</b> * </td>
		<td class="eintragen"><input name="email" type="email" size="45" maxlength="200" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>  
	<?php 		};
	if ( $data['vpn_ifreward'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Geld oder VP-Stunden?</b></td>
		<td class="eintragen"><select name="geldvps"><option value="Geld">Geld</option><option value="VP-Stunden">VP-Stunden</option></select></td>
		<td class="eintragen"></td>	
		<td class="eintragen"></td>	
	</tr>  
	<?php 		};
	if ( $data['vpn_ifbereits'] > 0 )
		{  
	?>	  
	<tr>					
		<td colspan="2" class="eintragen"><b>Haben Sie bereits an Experimenten dieses Versuchsleiters teilgenommen?</b></td>
		<td class="eintragen"><select name="andere"><option value="1">Ja</option><option value="0">Nein</option></select></td>
		<td class="eintragen"></td>	
	</tr>   
	<?php
		};
	if ( $data['vpn_ifbenach'] > 0 )
		{  
	?>	  
	<tr>					
		<td colspan="2" class="eintragen"><b>Möchten Sie über weitere Experimente benachrichtigt werden?</b></td>
		<td class="eintragen"><select name="weitere"><option value="0" selected>&nbsp;</option><option value="1">Ja</option><option value="0">Nein</option></select></td>
		<td class="eintragen"></td>	
	</tr>	 
	<tr>					
		<td colspan=3 class="eintragen"><font size="-2" style="line-height:12px;">sollten Sie bereits eine persönliche Einladung per Mail zu diesem Experiment bekommen haben, lassen Sie dieses Feld leer</font></td>	
	</tr>	 
	<?php 		}
	?>  
	  
	<tr><td><br /></td></tr>
	  
	<tr><td colspan="2"><font size="-2" color="#FF0000"><i>* Pflichtfeld</i></font><br /><br /></td></tr>  
	
	<tr>
		<td></td>
		<td colspan="2" class="eintragen">
			<input name="expid" type="hidden" value="<?php echo $_GET['expid']; ?>" />
			<input name="add" type="submit" value="Anmelden" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input name="addcancel" type="submit" value="Abbrechen" onclick="javascript:history.back();" />		
		</td>
		<td><br /> <br /><br /><br /></td>
	</tr>
	</table>
	</form>
		</td>
	</tr>
	</table>
	
	<?php }


/************************************/
/* ANMELDUNG IN DATENBANK EINTRAGEN */  
/************************************/

elseif(isset($_POST['add']) && !isset($_POST['admin'])) {

    try {
        $edp = ExperimentDataProvider::getInstanceByEncryptedId($_GET['expid']);
        $expId = $edp->getExpId();

        if (!$edp->isSignUpAllowed()) {
            throw new RuntimeException('Anmeldungen für dieses Experiment sind derzeit nicht möglich. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal.');
        }

        $errorFields = array();

        $abfrage = sprintf('
		SELECT	*
		FROM	%1$s
		WHERE	id = %2$d
			AND	visible = "1"'
            , TABELLE_EXPERIMENTE
            , $expId
        );
        $erg6 = $mysqli->query($abfrage);
        $data = $erg6->fetch_assoc();

        $exp_name = $data['exp_name'];

        if (empty(trim($_POST['vorname']))) {
            $errorFields[] = 'Vorname';
        };
        if (empty(trim($_POST['nachname']))) {
            $errorFields[] = 'Nachname';
        };
        if (empty(trim($_POST['email']))) {
            $errorFields[] = 'Email-Adresse';
        }

        if ($data['vpn_gebdat'] == 2) {
            if (empty($_POST['gebdat'])) {
                $errorFields[] = 'Geburtsdatum';
            }
        };
        if ($data['vpn_fach'] == 2) {
            if (empty($_POST['fach'])) {
                $errorFields[] = 'Studienfach';
            }
        };
        if ($data['vpn_semester'] == 2) {
            if (empty($_POST['semester'])) {
                $errorFields[] = 'Semester';
            }
        };
        if ($data['vpn_adresse'] == 2) {
            if (empty($_POST['anschrift'])) {
                $errorFields[] = 'Adresse';
            }
        };
        if ($data['vpn_tele1'] == 2) {
            if (empty($_POST['telefon1'])) {
                $errorFields[] = 'Telefon1';
            }
        };
        if ($data['vpn_tele2'] == 2) {
            if (empty($_POST['telefon2'])) {
                $errorFields[] = 'Telefon2';
            }
        };
        
        if (!empty($errorFields)) {
            throw new RuntimeException('Die folgenden Pflichtfelder wurden nicht ausgefüllt: ' . implode(', ', $errorFields));
        }

        if ($data['vpn_gebdat'] == 2) {
            if (substr_count($_POST['gebdat'], '.') != 2 || strlen($_POST['gebdat']) != 10) {
                throw new RuntimeException('Ihr Geburtsdatum ist in einem falschen Format angegeben. Bitte beachten Sie, dass Ihr Geburtsdatum unbedingt im Format tt.mm.jjjj angegeben werden muss.');
            }
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(sprintf('Die angegebene Mailadresse %1$s ist fehlerhaft.', isset($_POST['email']) ? $_POST['email'] : ''));
        }

        $blockClass = new EmailBlocking();
        if ($blockClass->isBlocked($_POST['email'])) {
            throw new RuntimeException(sprintf('Die Anmeldung für dieses Experiment ist leider zur Zeit nicht möglich. Bitte setzen sie sich mit dem Versuchsleiter in Verbindung.'));
        }

        /* CHECK: PLÄTZE FREI */
        $freeSlotsAvailable = true;
        if ('automatisch' === $data['terminvergabemodus']) {
            try {
                $termin = TimeSlotTemporary::getInstanceByTemporaryId($_POST['termin']);
                $terminStart = $termin->getStart();
                $terminEnd = $termin->getEnd();

                $cdp = new CalendarDataProvider();
                $labId = $cdp->getFirstLabWhereTimeSlotFits($expId, $terminStart, $terminEnd);

                $sc = new SessionController();
                $terminId = $sc->create(
                    $expId,
                    $terminStart->format('d.m.Y'),
                    $terminStart->format('H:i'),
                    $terminEnd->format('H:i'),
                    1,
                    $labId
                );
                $vpcur = 1;
            } catch (Exception $e) {
                trigger_error($e, E_USER_WARNING);
                $freeSlotsAvailable = false;
            }


        } else {
            $terminId = (int)$_POST['termin'];

            $sqlSes = "SELECT maxtn FROM " . TABELLE_SITZUNGEN . " WHERE `exp` = '$expId' AND id = $terminId";
            $resultSes = $mysqli->query($sqlSes);
            $dataSes = $resultSes->fetch_assoc();

            $abfrage3 = "SELECT COUNT(termin) AS count FROM " . TABELLE_VERSUCHSPERSONEN . " WHERE termin = '$terminId'";
            $erg3 = $mysqli->query($abfrage3);

            $data3 = $erg3->fetch_assoc();
            $signUpCount = (int)$data3['count'];

            $freeSlots = (int)$dataSes['maxtn'] - $signUpCount;
            if ($freeSlots <= 0) {
                $freeSlotsAvailable = false;
            }

        }

        if (!$freeSlotsAvailable) {
            throw new RuntimeException('Bitte stellen Sie sicher, dass der Termin, für den Sie sich anmelden wollte, nicht bereits voll belegt ist.');
        }

        if (isset($_POST['gebdat'])) {
            $gebdat = formatDateForMysql($_POST['gebdat']);
        }

        $eintrag = sprintf('
        INSERT INTO `%1$s`
        (`exp`, `vorname`, `nachname`, `geschlecht`, `gebdat`, `fach`, `semester`, `anschrift`, `telefon1`, `telefon2`, `email`, `geldvps`, `andere`, `weitere`, `termin`)
        VALUES (%2$d, \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\', \'%15$s\', %16$d)'
            , /*  1 */ TABELLE_VERSUCHSPERSONEN
            , /*  2 */ $expId
            , /*  3 */ isset($_POST['vorname']) ? $mysqli->real_escape_string($_POST['vorname']) : ''
            , /*  4 */ isset($_POST['nachname']) ? $mysqli->real_escape_string($_POST['nachname']) : ''
            , /*  5 */ isset($_POST['geschlecht']) ? $mysqli->real_escape_string($_POST['geschlecht']) : ''
            , /*  6 */ isset($_POST['gebdat']) ? $mysqli->real_escape_string($gebdat) : '0000-00-00'
            , /*  7 */ isset($_POST['fach']) ? $mysqli->real_escape_string($_POST['fach']) : ''
            , /*  8 */ isset($_POST['semester']) ? $mysqli->real_escape_string($_POST['semester']) : ''
            , /*  9 */ isset($_POST['anschrift']) ? $mysqli->real_escape_string($_POST['anschrift']) : ''
            , /* 10 */ isset($_POST['telefon1']) ? $mysqli->real_escape_string($_POST['telefon1']) : ''
            , /* 11 */ isset($_POST['telefon2']) ? $mysqli->real_escape_string($_POST['telefon2']) : ''
            , /* 12 */ isset($_POST['email']) ? $mysqli->real_escape_string($_POST['email']) : ''
            , /* 13 */ isset($_POST['geldvps']) ? $mysqli->real_escape_string($_POST['geldvps']) : ''
            , /* 14 */ isset($_POST['andere']) ? $mysqli->real_escape_string($_POST['andere']) : ''
            , /* 15 */ isset($_POST['weitere']) ? $mysqli->real_escape_string($_POST['weitere']) : ''
            , /* 16 */ $terminId
        );
        $ergebnis = $mysqli->query($eintrag);
        if (false === $ergebnis) {
            if (1062 === $mysqli->errno) {
                throw new RuntimeException(sprintf('Für die Email Adresse %1$s liegt bereits eine Anmeldung für dieses Experiment vor.', $_POST['email']));
            }
            throw new RuntimeException(sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__));
        }

        require_once 'pageelements/header.php';

        $abfrage = "SELECT exp_name FROM " . TABELLE_EXPERIMENTE . " WHERE `id` = '$expId'";
        $erg = $mysqli->query($abfrage);
        while ($data = $erg->fetch_assoc()) {
            ?>
            <table>
            <tr>
                <td><h1><?php echo $data['exp_name']; ?></h1></td>
            </tr>
        <?php } ?>
        <tr>
            <td>
                <table style="margin-left:15px;">
                    <tr>
                        <td class="ad_edit_headline"><h2>Anmeldung</h2></td>
                    </tr>
                    <tr>
                        <td>
                            <?php $abfrage = "SELECT * FROM " . TABELLE_EXPERIMENTE . " WHERE `id` = '$expId'";
                            $erg = $mysqli->query($abfrage);
                            while ($data2 = $erg->fetch_assoc()) {
                                ?>
                                Vielen Dank für Ihre Anmeldung!<br/>
                                Sie werden in Kürze eine Terminbestätigung in ihrem Email-Postfach finden.<br/><br/>
                                Sollten sie noch weitere Fragen haben, können Sie sich gerne per Telefon an uns wenden oder eine Email schreiben.
                                <br/><br/>
                                <?php
                                $abfrage2 = "SELECT * FROM " . TABELLE_SITZUNGEN . " WHERE `id` = '$terminId'";
                                $erg2 = $mysqli->query($abfrage2);
                                while ($data3 = $erg2->fetch_assoc()) {
                                    $termin = formatMysqlDate($data3['tag']);

                                    $letter = htmlspecialchars_decode($data2['exp_mail'], ENT_QUOTES);


                                    if (isset($_POST['geschlecht']) && $_POST['geschlecht'] == "männlich") {
                                        $letter = str_replace("!liebe/r!", "Lieber", $letter);
                                    } else {
                                        $letter = str_replace("!liebe/r!", "Liebe", $letter);
                                    }

                                    $letter = str_replace("!vp_vorname!", $_POST['vorname'], $letter);
                                    $letter = str_replace("!vp_nachname!", $_POST['nachname'], $letter);

                                    $letter = str_replace("!termin!", $termin, $letter);
                                    $letter = str_replace("!beginn!", substr($data3['session_s'], 0, 5), $letter);
                                    $letter = str_replace("!ende!", substr($data3['session_e'], 0, 5), $letter);

                                    $letter = str_replace("!exp_name!", htmlspecialchars_decode($data2['exp_name'], ENT_QUOTES), $letter);
                                    $letter = str_replace("!exp_ort!", htmlspecialchars_decode($data2['exp_ort'], ENT_QUOTES), $letter);

                                    $letter = str_replace("!vl_name!", htmlspecialchars_decode($data2['vl_name'], ENT_QUOTES), $letter);
                                    $letter = str_replace("!vl_telefon!", htmlspecialchars_decode($data2['vl_tele'], ENT_QUOTES), $letter);
                                    $letter = str_replace("!vl_email!", htmlspecialchars_decode($data2['vl_email'], ENT_QUOTES), $letter);

                                    $header = "From:" . $data2['vl_email'] . "\r\n" . "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";
                                    mail($_POST['email'], 'Terminbestätigung', $letter, $header);
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET); ?>"
                                  method="post" enctype="multipart/form-data" name="content">
                                <input name="expid" type="hidden" value="<?php echo $_POST['expid']; ?>"/>
                                <input name="addcancel" type="submit" value="Zurück"/>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <?php     }
    catch(RuntimeException $e) {
        require_once 'pageelements/header.php';

        $abfrage = "SELECT exp_name FROM " . TABELLE_EXPERIMENTE . " WHERE `id` = '$expId'";
        $erg = $mysqli->query($abfrage);
        while ($data = $erg->fetch_assoc()) {
            ?>
            <table>
            <tr>
                <td><h1><?php echo $data['exp_name']; ?></h1></td>
            </tr>
            <?php         }
        ?>
        <tr>
            <td>
                <table style="margin-left:15px;">
                    <tr>
                        <td class="ad_edit_headline">
                            <h2>Anmeldung fehlgeschlagen!</h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo $e->getMessage();?><br/>
                            <br/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET); ?>"
                                  method="post" enctype="multipart/form-data" name="content">
                                <input name="expid" type="hidden" value="<?php echo $_POST['expid']; ?>"/>
                                <input name="addcancel" type="submit" value="Zurück"/>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        <?php     }
}

require_once 'pageelements/footer.php';
