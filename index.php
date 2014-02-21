<?php
require_once __DIR__ . '/include/functions.php';
require_once __DIR__ . '/include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/include/class/calendar/CalendarDataProvider.class.php';
require_once __DIR__ . '/include/class/controller/SessionController.class.php';
require_once __DIR__ . '/include/class/ExperimentDataProvider.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

/************************/
/* LAUFENDE EXPERIMENTE */
/************************/

if(!isset($_GET['menu'])) {
	require_once 'pageelements/header.php';
	$edp = new ExperimentDataProvider(null);
	$laufendeExperimente = $edp->getExpListSignUpAllowed();
	if(count($laufendeExperimente)) {
		echo '<h1>Laufende Experimente</h1>';
	}
	else {
		echo '<h1>Derzeit laufen keine Experimente. Bitte schauen Sie zu einem späteren Zeitpunkt noch einmal vorbei.</h1>';
	}

	foreach($laufendeExperimente as $data) {
		?>	
		<h2><a href="index.php?menu=experiment&amp;expid=<?= $data['id']?>" style="color: #4A71D6;">&nbsp;&nbsp;<img src="images/arrow.gif" width="4" height="10" border="0" alt="" />&nbsp;<?= $data['exp_name'] ?></a></h2>
		<div id="mainframe_exp">&nbsp;&nbsp;&nbsp;<a href="index.php?menu=experiment&amp;expid=<?= $data['id']?>"><?= htmlspecialchars(substr($data['exp_zusatz'],0,250)) ?> [...]</a></div>
		<? 
	}
}


/******************************/  
/* TERMINÜBERSICHT EXPERIMENT */  
/******************************/

elseif(($_GET['menu'] == 'experiment') && (isset($_GET['expid'])) && (!isset($_POST['chosendate']))&& (!isset($_POST['add']))) {
	
	$edp = new ExperimentDataProvider((int)$_GET['expid']);
	if(!$edp->isSignUpAllowed()) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Anmeldungen für dieses Experiment sind derzeit nicht möglich. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal.'));
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
		, $_GET['expid']
	);

	$erg4 = $mysqli->query($abfrage2);
	if(0 === $erg4->num_rows) {
		die('Experiment nicht gefunden');
	}
	$data2 = $erg4->fetch_assoc();  
	
	try {
		$cdp = new CalendarDataProvider();
		$calendarData = $cdp->getAvailableSessionsFor((int)$_GET['expid']);
	}
	catch(Exception $e) {
		trigger_error($e, E_USER_WARNING);
		echo '<!--' . $e . '-->';
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__)));
	}
	
	require_once 'pageelements/header.php';
	?>
	
	<script type="text/javascript">
		$( document ).ready(function() {
			calendarData = [<?php echo $calendarData;?>];
			initCalendar(calendarData, 'signup');
		});
	</script>
	
	
	<h1><?php echo $data2['exp_name']; ?></h1>
	
	<? # INCENTIVE # ?>
		
	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Informationen</h2></td>	
		</tr>
		<tr>
			<td width="550px"><?= nl2br($data2['exp_zusatz']) ?><br /><br />
	<? if ( $data2['exp_vps'] == 1  || $data2['exp_geld'] == 1 )
		{
	?>
	Sie erhalten für ihre Teilnahme
	<?
	if ( $data2['exp_vps'] == 1 && $data2['exp_geld'] == 1)
		  {
	?>	
	<b><?= $data2['exp_vpsnum']?> VP-Stunden</b> oder <b><?= $data2['exp_geldnum']?> EURO</b>.
	<?
		  }
	else if ( $data2['exp_geld'] == 1 && $data2['exp_vps'] < 1)
		  {
	?>	
	<b><?= $data2['exp_geldnum']?> EURO</b>.
	<?
		  }
	else if ( $data2['exp_vps'] == 1 && $data2['exp_geld'] < 1)
		  {
	?>	
	<b><?= $data2['exp_vpsnum']?> <?= $credit_points ?></b>.
	<?
		  }
		}
	?>
			</td>
		</tr>
	</table>
	
	<? # ORT # ?>
		
	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Adresse</h2></td>	
		</tr>
		<tr>
			<td width="550px">
				Das Experiment findet an folgender Adresse statt:<br />
				<b><?= nl2br($data2['exp_ort']) ?></b>
			</td>
		</tr>
	</table>
	
	<? # KONTAKT # ?>
	
	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Kontakt</h2></td>	
		</tr>
		<tr>
			<td><div><?= $data2['vl_name'] ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<a href="mailto:<?= $data2['vl_email'] ?>" id="mainframe_exp_link"><?= $data2['vl_email'] ?></a><? if ($data2['vl_tele'] != "") { ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?= $data2['vl_tele'] ?><? } ?><br /></div></td>
		</tr>
	</table>

	<h2>Termine</h2>
	<div id='calendar'></div>
	
	<?php 
	/*
	 # TERMINE #
	 ?>
		
	<table class="optionGroup">	
		<tr>
			<td colspan="5" ><h2 style="margin-bottom:10px;">Termine</h2></td>	
		</tr>
		<tr>
			<td width="100px"><div><b>Datum</b></div></td>
			<td width="100px"><div><b>Uhrzeit</b></div></td>
			<td width="110px"><div style="text-align:center"><b>Freie Plätze</b></div></td>
			<td width="100px"></td>
			<td></td>  
		</tr>
		<?
		
		$abfrage = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE `exp` = '$_GET[expid]' ORDER BY tag, session_s ASC" ;
		$erg = $mysqli->query($abfrage);
		while ($data = $erg->fetch_assoc()) {
			?>
			<tr>
				<td class="termin">
					<div>
						<?php echo formatMysqlDate($data['tag']);?>
					</div>
				</td>
				<td class="termin"><div><?=substr($data['session_s'], 0, 5) . '-' . substr($data['session_e'], 0, 5) ?></div></td>
				<?php
				$vpcur = 0; $tempcur = 0;  
				$abfrage3 = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE `exp` = '$_GET[expid]' AND `termin` = '$data[id]'";
				$erg3 = $mysqli->query($abfrage3);
				while ($data3 = $erg3->fetch_assoc()) {
					$vpcur = $tempcur + 1;
					$tempcur = $vpcur;
				} 
				
				$vpcur = $data['maxtn'] - $tempcur;
				$maxtnvar = $data['maxtn'];
				
				if ($_GET['expid'] == 41) {
					if ($tempcur < 2) { $vpcur++; $vpcur++; };
				};
				
				?>  
				<td class="termin"><div style="text-align:center"><?=$vpcur?></div></td> 
				<td class="termin">
					<? if ($vpcur > 0) {
					?>
					<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data">		
						  <input name="maxtn" type="hidden" value="<?= $data['maxtn'] ?>" />
						  <input name="id" type="hidden" value="<?= $data['id'] ?>" />
						  <input name="expid" type="hidden" value="<?= $_GET['expid'] ?>" />	  
						  <input name="chosendate" type="submit" value="Anmelden" />
					</form>	  
					<? }
					?>	 
				</td>
			</tr>
			<? 
		}
		?>
	</table> 
	
	<?php
	*/
}

/*********************************************/
/* ANMELDEFORMULAR FÜR EINEN TERMIN ANZEIGEN */
/*********************************************/	
  
elseif (isset($_POST['chosendate'])) {

	$abfrage = sprintf( '
		SELECT	*
		FROM	%1$s
		WHERE	id = %2$d
			AND	visible = "1"'
		, TABELLE_EXPERIMENTE
		, $_GET['expid']
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
	<table style="margin-left:15px; margin-top:-20px;">
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
	$abfrage2 = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE `exp` = '$_GET[expid]' ORDER BY tag, session_s ASC" ;
	$erg2 = $mysqli->query($abfrage2);
	while ($data2 = $erg2->fetch_assoc()) {
	
	$vpcur = 0; $tempcur = 0;  
	$abfrage3 = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE `exp` = '$_GET[expid]' AND `termin` = '$data2[id]' ";
	$erg3 = $mysqli->query($abfrage3);
	while ($data3 = $erg3->fetch_assoc()) {
	   $vpcur = $tempcur + 1;
	   $tempcur = $vpcur;
	}
	
	
		if ( $data2['maxtn'] > $vpcur ) {
		?>
		  <option value="<?= $data2['id'] ?>" <? if ($data2['id'] == $_POST['id']) {?>selected<? } ?>><?php echo formatMysqlDate($data2['tag']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . substr($data2['session_s'], 0, 5) . '-' . substr($data2['session_e'], 0, 5);?></option>
		<?php
		}
	}
	?>
		</select>
		
		<?php
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
		<?
	};
	if ( $data['vpn_gebdat'] > 0 ) {
		?>  
		<tr>					
			<td class="eintragen"><b>Geburtsdatum</b> <? if ( $data['vpn_gebdat'] == 2 ) { ?>*<? } ?></td>
			<td class="eintragen">
				<input type="text" name="date2" class="datepicker" size="12" maxlength="10" />&nbsp;&nbsp;&nbsp;
			</td>
			<td class="eintragen"><img src="images/info.gif" width="17" height="17" title="<b>Geburtsdatum</b><br />Bitte im Format <b>tt.mm.jjjj</b> angeben,<br />also z.B. 24.06.1984" alt="" />	</td>	
		</tr>
		<?
	};
	if ( $data['vpn_fach'] > 0 )
		{  
	?>  
	<tr>					
		<td class="eintragen"><b>Studienfach</b> <? if ( $data['vpn_fach'] == 2 ) { ?>*<? } ?></td>
		<td class="eintragen"><input name="fach" type="text" size="45" maxlength="255" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>
	<?
		};
	if ( $data['vpn_semester'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Semester</b> <? if ( $data['vpn_semester'] == 2 ) { ?>*<? } ?></td>
		<td class="eintragen"><input name="semester" type="text" size="45" maxlength="10" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>
	<?
		};
	if ( $data['vpn_adresse'] > 0 )
		{  
	?>	
	<tr>					
		<td class="eintragen"><b>Adresse</b> <? if ( $data['vpn_adresse'] == 2 ) { ?>*<? } ?></td>
		<td class="eintragen"><textarea name="anschrift" rows="3" cols="40"></textarea><br /><br /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>		
	</tr>  
	 <?
		};
	if ( $data['vpn_tele1'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Telefon 1</b> <? if ( $data['vpn_tele1'] == 2 ) { ?>*<? } ?></td>
		<td class="eintragen"><input name="telefon1" type="text" size="45" maxlength="20" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr> 
	<?
		};
	if ( $data['vpn_tele2'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Telefon</b> <? if ( $data['vpn_tele2'] == 2 ) { ?>*<? } ?></td>
		<td class="eintragen"><input name="telefon2" type="text" size="45" maxlength="20" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>  
	<?
		};
	if ( $data['vpn_email'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Email-Adresse</b> * </td>
		<td class="eintragen"><input name="email" type="text" size="45" maxlength="200" /></td>
		<td class="eintragen"></td>
		<td class="eintragen"></td>	
	</tr>  
	<?
		};
	if ( $data['vpn_ifreward'] > 0 )
		{  
	?>	  
	<tr>					
		<td class="eintragen"><b>Geld oder VP-Stunden?</b></td>
		<td class="eintragen"><select name="geldvps"><option value="Geld">Geld</option><option value="VP-Stunden">VP-Stunden</option></select></td>
		<td class="eintragen"></td>	
		<td class="eintragen"></td>	
	</tr>  
	<?
		};
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
	<?
		}
	?>  
	  
	<tr><td><br /></td></tr>
	  
	<tr><td colspan="2"><font size="-2" color="#FF0000"><i>* Pflichtfeld</i></font><br /><br /></td></tr>  
	
	<tr>
		<td></td>
		<td colspan="2" class="eintragen">
			<input name="expid" type="hidden" value="<?php echo $_GET['expid']; ?>" />
			<input name="maxtn" type="hidden" value="<?php echo $_POST['maxtn']; ?>" />
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
	
	<?
}


/************************************/
/* ANMELDUNG IN DATENBANK EINTRAGEN */  
/************************************/

elseif(isset($_POST['add']) && !isset($_POST['admin'])) {
	
	$edp = new ExperimentDataProvider((int)$_GET['expid']);
	if(!$edp->isSignUpAllowed()) {
		storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, 'Anmeldungen für dieses Experiment sind derzeit nicht möglich. Bitte versuchen Sie es zu einem späteren Zeitpunkt noch einmal.'));
		require_once 'pageelements/header.php';
		require_once 'pageelements/footer.php';
		exit;
	}

	# CHECK: PFLICHTFELDER / FEHLER #
	
	$Fehler="N";
	
	$abfrage = sprintf( '
		SELECT	*
		FROM	%1$s
		WHERE	id = %2$d
			AND	visible = "1"'
		, TABELLE_EXPERIMENTE
		, $_GET['expid']
	);
	$erg6 = $mysqli->query($abfrage);
	$data = $erg6->fetch_assoc();
	
	$exp_name=$data['exp_name'];
	
	if ( $_POST['vorname'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $forename . "<br />";  };
	if ( $_POST['nachname'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $surname . "<br />";  };
	if ( $_POST['email'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $email_address . "<br />"; }
	
	if ( $data['vpn_gebdat'] == 2 ) { if ( $_POST['gebdat'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $dob . "<br />"; } };
	if ( $data['vpn_fach'] == 2 ) { if ( $_POST['fach'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $field_study . "<br />"; } };
	if ( $data['vpn_semester'] == 2 ) { if ( $_POST['semester'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $semester . "<br />"; } };
	if ( $data['vpn_adresse'] == 2 ) { if ( $_POST['anschrift'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $address . "<br />"; } };
	if ( $data['vpn_tele1'] == 2 ) { if ( $_POST['telefon1'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $phone . "1<br />"; } };
	if ( $data['vpn_tele2'] == 2 ) { if ( $_POST['telefon2'] == "" ) { $Fehler ="Y"; $Feld   = $Feld."&bull; " . $phone . "2<br />"; } };
	
	require_once 'pageelements/header.php';
	
	if($Fehler == "Y") {
		?>
		<table> 
		<tr>
			<td><h1><?= $exp_name ?></h1></td>
		</tr>
		<tr>
			<td>
		<table style="margin-left:15px; margin-top:-20px;">
		<tr>
			<td class="ad_edit_headline"><h2>Anmeldung</h2></td>
		</tr>
		<tr>
			<td>
		<?  
		echo $field_error . "<br /><br />";
		echo "$Feld<br /><br />";
		echo $go_back;
		?>
			</td>
		</tr>
		</table>
			</td>
		</tr>
		</table>
		<?
		exit();
	}
	  
	if ( $data['vpn_gebdat'] == 2 ) {
		$Fehler = "N";
		
		if (substr_count($_POST[gebdat], '.') != 2) {
			$Fehler = "Y";
		}
		if (strlen($_POST[gebdat]) != 10 ) {
			$Fehler = "Y";
		}
		
		if ($Fehler == "Y") {
			?>  
			<table> 
			<tr>
				<td><h1><?= $exp_name ?></h1></td>
			</tr>
			<tr>
				<td>
			<table style="margin-left:15px; margin-top:-20px;">
			<tr>
				<td class="ad_edit_headline"><h2>Anmeldung</h2></td>
			</tr>
			<tr>
				<td>
			<?php
			echo "Ihr Geburtsdatum ist in einem falschen Format angegeben. Bitte beachten Sie, dass Ihr Geburtsdatum unbedingt im Format <b>tt.mm.jjjj</b> angegeben werden muss." . "<br /><br />";
			echo "Bitte gehen Sie <a href=\"javascript: history.back();\">zurück</a> und überprüfen Sie ihre Eingabe.";
			?>
				</td>
			</tr>
			</table>
				</td>
			</tr>
			</table>
			<?  
			exit();
		}
	}
	
	$Fehler = "N";
	
	if (!strstr($_POST['email'],"@")) {
		$Fehler = "Y";
	}
	if (!strstr($_POST['email'],".")) {
		$Fehler = "Y";
	}
	
	if($Fehler == "Y") {
		?>  
		<table> 
		<tr>
			<td><h1><?= $exp_name ?></h1></td>
		</tr>
		<tr>
			<td>
		<table style="margin-left:15px; margin-top:-20px;">
		<tr>
			<td class="ad_edit_headline"><h2>Anmeldung</h2></td>
		</tr>
		<tr>
			<td>
		<?  
		echo "Die angegebene Mailadresse <b>" . (isset($_POST['email'])?$_POST['email']:'') . "</b> ist fehlerhaft." . "<br /><br />";
		echo "Bitte gehen Sie <a href=\"javascript: history.back();\">zurück</a> und überprüfen Sie ihre Eingabe.";
		?>
			</td>
		</tr>
		</table>
			</td>
		</tr>
		</table>  
		<?
		exit();
	} 
	
	
	
	/* CHECK: PLÄTZE FREI */ 
	
	if('automatisch' === $data['terminvergabemodus']) {
		try {
			$termin = TimeSlotTemporary::getInstanceByTemporaryId($_POST['termin']);
			$terminStart = $termin->getStart();
			$terminEnd = $termin->getEnd();
			
			$cdp = new CalendarDataProvider();
			$labId = $cdp->getFirstLabWhereTimeSlotFits($_GET['expid'], $terminStart, $terminEnd);
			
			$sc = new SessionController();
			$terminId = $sc->create(
				$_GET['expid'],
				$terminStart->format('d.m.Y'),
				$terminStart->format('H:i'),
				$terminEnd->format('H:i'),
				1,
				$labId
			);
			$vpcur = 1;
		}
		catch(Exception $e) {
			trigger_error($e, E_USER_WARNING);
			storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__)));
		}
		

	}
	else {
		$vpcur = 0; $tempcur = 0;  
		$abfrage3 = "SELECT * FROM ".TABELLE_VERSUCHSPERSONEN." WHERE termin = '$_POST[termin]'";
		$erg3 = $mysqli->query($abfrage3);
		  
		while ($data3 = $erg3->fetch_assoc())
		  {
		   $vpcur = $tempcur + 1;
		   $tempcur = $vpcur;
		  } 
		
		$vpcur = $_POST['maxtn'] - $tempcur;
		
		$terminId = (int)$_POST['termin'];
	}
	
	
	
	/* ANMELDUNG: PLÄTZE FREI! */  
	
	if ($vpcur > 0)
	{
	
		if(isset($_POST['gebdat'])) {
			$gebdat = formatDateForMysql($_POST['gebdat']);
		}
		
		$eintrag = sprintf( '
			INSERT INTO `%1$s`
			(`exp`, `vorname`, `nachname`, `geschlecht`, `gebdat`, `fach`, `semester`, `anschrift`, `telefon1`, `telefon2`, `email`, `geldvps`, `andere`, `weitere`, `termin`)
			VALUES (%2$d, \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', \'%8$s\', \'%9$s\', \'%10$s\', \'%11$s\', \'%12$s\', \'%13$s\', \'%14$s\', \'%15$s\', %16$d)'
			, /*  1 */ TABELLE_VERSUCHSPERSONEN
			, /*  2 */ $_GET['expid']
			, /*  3 */ isset($_POST['vorname'])?$mysqli->real_escape_string($_POST['vorname']):''
			, /*  4 */ isset($_POST['nachname'])?$mysqli->real_escape_string($_POST['nachname']):''
			, /*  5 */ isset($_POST['geschlecht'])?$mysqli->real_escape_string($_POST['geschlecht']):''
			, /*  6 */ isset($_POST['gebdat'])?$mysqli->real_escape_string($gebdat):'0000-00-00'
			, /*  7 */ isset($_POST['fach'])?$mysqli->real_escape_string($_POST['fach']):''
			, /*  8 */ isset($_POST['semester'])?$mysqli->real_escape_string($_POST['semester']):''
			, /*  9 */ isset($_POST['anschrift'])?$mysqli->real_escape_string($_POST['anschrift']):''
			, /* 10 */ isset($_POST['telefon1'])?$mysqli->real_escape_string($_POST['telefon1']):''
			, /* 11 */ isset($_POST['telefon2'])?$mysqli->real_escape_string($_POST['telefon2']):''
			, /* 12 */ isset($_POST['email'])?$mysqli->real_escape_string($_POST['email']):''
			, /* 13 */ isset($_POST['geldvps'])?$mysqli->real_escape_string($_POST['geldvps']):''
			, /* 14 */ isset($_POST['andere'])?$mysqli->real_escape_string($_POST['andere']):''
			, /* 15 */ isset($_POST['weitere'])?$mysqli->real_escape_string($_POST['weitere']):''
			, /* 16 */ $terminId
		);
		$ergebnis = $mysqli->query($eintrag);
		if(false === $ergebnis) {
			if( 1062 === $mysqli->errno) {
				storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Anmeldung nicht möglich. Für die Email Adresse %1$s liegt bereits eine Anmeldung für dieses Experiment vor.', $_POST['email'])));
			}
			else {
				storeMessageInSession(sprintf(MESSAGE_BOX_ERROR, sprintf('Interner Fehler bei der Anmeldung. Bitte wenden Sie sich per Email an den Versuchsleiter. (%1$d)', __LINE__)));
			}
		} 
		
		$abfrage = "SELECT exp_name FROM ".TABELLE_EXPERIMENTE." WHERE `id` = '$_GET[expid]'" ;
		$erg = $mysqli->query($abfrage);
		while ($data = $erg->fetch_assoc())
			{
		?>
		<table> 
		<tr>
			<td><h1><?php echo $data['exp_name']; ?></h1></td>
		</tr>
		<?  } ?>  
		<tr>
			<td>
		<table style="margin-left:15px; margin-top:-20px;">
		<tr>
			<td class="ad_edit_headline"><h2>Anmeldung</h2></td>
		</tr>
		<tr>
			<td>
		<? $abfrage = "SELECT * FROM ".TABELLE_EXPERIMENTE." WHERE `id` = '$_POST[expid]'" ;
		$erg = $mysqli->query($abfrage);
		while ($data2 = $erg->fetch_assoc())
			{  
		?>
		Vielen Dank für Ihre Anmeldung!<br />
		Sie werden in Kürze eine Terminbestätigung in ihrem Email-Postfach finden.<br /><br />
		Sollten sie noch weitere Fragen haben, können Sie sich gerne per Telefon an uns wenden oder eine Email schreiben.<br /><br />
		<?
		
		$abfrage2 = "SELECT * FROM ".TABELLE_SITZUNGEN." WHERE `id` = '$_POST[termin]'" ;
		$erg2 = $mysqli->query($abfrage2);
		while ($data3 = $erg2->fetch_assoc())
			  {  
		
		$termin = formatMysqlDate($data3['tag']);
		
		$letter = htmlspecialchars_decode($data2['exp_mail'], ENT_QUOTES);
		
		
		if (isset($_POST['geschlecht']) && $_POST['geschlecht'] == "männlich") { $letter = str_replace("!liebe/r!","Lieber",$letter); }
		else { $letter = str_replace("!liebe/r!","Liebe",$letter); }
		
		$letter = str_replace("!vp_vorname!",$_POST['vorname'],$letter);
		$letter = str_replace("!vp_nachname!",$_POST['nachname'],$letter);
		
		$letter = str_replace("!termin!",$termin,$letter);
		$letter = str_replace("!beginn!",substr($data3['session_s'],0,5),$letter);
		$letter = str_replace("!ende!",substr($data3['session_e'],0,5),$letter);
		
		$letter = str_replace("!exp_name!",htmlspecialchars_decode($data2['exp_name'], ENT_QUOTES),$letter);
		$letter = str_replace("!exp_ort!",htmlspecialchars_decode($data2['exp_ort'], ENT_QUOTES),$letter);
		
		$letter = str_replace("!vl_name!",htmlspecialchars_decode($data2['vl_name'], ENT_QUOTES),$letter);
		$letter = str_replace("!vl_telefon!",htmlspecialchars_decode($data2['vl_tele'], ENT_QUOTES),$letter);
		$letter = str_replace("!vl_email!",htmlspecialchars_decode($data2['vl_email'], ENT_QUOTES),$letter);
	
		$header = "From:" . $data2['vl_email'] . "\r\n" . "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";
		mail($_POST['email'],'Terminbestätigung',$letter,$header);
		}
		}
		?>
		</td>
		</tr>
		<tr>
			<td>
			  <form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="content">
			  <input name="expid" type="hidden" value="<?= $_POST[expid] ?>" />
			  <input name="addcancel" type="submit" value="Zurück" />		
			  </form>
			</td>
		</tr>
		</table>  
		</td>
		</tr>
		</table>
		<?
	}
		  
		  
	
	/* ANMELDUNG KEINE PLÄTZE FREI! */  	  
		  
	else {
		$abfrage = "SELECT exp_name FROM ".TABELLE_EXPERIMENTE." WHERE `id` = '$_GET[expid]'" ;
		$erg = $mysqli->query($abfrage);
		while ($data = $erg->fetch_assoc()) {
			?>
			<table> 
			<tr>
				<td><h1><?= $data['exp_name'] ?></h1></td>
			</tr>
			<?
		}
		?>  
		<tr>
			<td>
			<table style="margin-left:15px; margin-top:-20px;">
				<tr>
					<td class="ad_edit_headline">
						<h2>Anmeldung</h2>
					</td>
				</tr>
				<tr>
					<td>
						Anmeldung fehlgeschlagen!<br />
						Die Anmeldung ist fehlgeschlagen. Bitte stellen Sie sicher, dass der Termin, für den Sie sich anmelden wollte, nicht bereits voll belegt ist.<br />
						<br />
					</td>
				</tr>
				<tr>
					<td>
						<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="content">
						<input name="expid" type="hidden" value="<?= $_POST[expid] ?>" />
						<input name="addcancel" type="submit" value="Zurück" />
						</form>
					</td>
				 </tr>
			</table> 
			</td>
		</tr>
		</table>
		<?
	}
}
require_once 'pageelements/footer.php';
?>
