<?php
/* EINZELTERMIN-ANSICHT/EDIT */

require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

if(!isset($_GET['terminid'])) {
	die('Terminid fehlt');
}

//prüfen ob termin automatisch oder manuell erstellt wurde
$sql = sprintf('SELECT lab_id FROM %1$s WHERE id = %2$d', TABELLE_SITZUNGEN, $_GET['terminid']);
$erg = $mysqli->query($sql) or die($mysqli->error);
if($erg->num_rows < 1) {
	die('Termin nicht gefunden');
}
$preCheck = $erg->fetch_assoc();
$terminManuell = empty($preCheck['lab_id']);

/* WENN TERMIN MANUELL ERSTELLT WURDE */
/* ---------------------------------- */
if($terminManuell) {
	$abfrage = sprintf( '
		SELECT		termin.`id`,
					termin.`exp`,
					termin.`tag`,
					termin.`session_s`,
					termin.`session_e`,
					termin.`maxtn`,
					COUNT(vp.id) AS count
		FROM		%1$s AS termin
		LEFT JOIN	%2$s AS vp
			ON		termin.exp = vp.exp
			AND		vp.termin = %3$d
		WHERE		termin.`id` = %3$d
		LIMIT		1'
		, TABELLE_SITZUNGEN
		, TABELLE_VERSUCHSPERSONEN
		, $_GET['terminid']
	);
	$erg = $mysqli->query($abfrage) or die($mysqli->error);
	$termin = $erg->fetch_assoc();
	$terminDatum = formatMysqlDate($termin['tag']);
	$beginn = substr($termin['session_s'], 0, 5);
	$ende = substr($termin['session_e'], 0, 5);
	
	?>
	<html>
	<head>
	<script type="text/javascript">
	$( document ).ready(function() {
		$("[title]").each(function(){
			$(this).tooltip({ content: $(this).attr("title")});
		});	// It allows html content to tooltip.
		$('input[type="button"]').button();
		initDatepicker();
		$(".timepicker").timePicker({step:30, startTime:"08:00", endTime:"19:00"});

        $('#editcancel').on('click', function(e){
            e.preventDefault();
            window.parent.$('[class^=d_termin_change_]').dialog('close');
        });

        $('#editsess').on('click', function(e){
            e.preventDefault();

            var expid = $(this).data("expid");
            var sessionId = $(this).data("id");

            $.ajax({
                type: "POST",
                url: "service/session.php",
                data: {
                    'action': 'change',
                    'change_id': sessionId,
                    'expid': expid,
                    'change_termin': $("input[name=change_termin]").val(),
                    'change_session_s': $("input[name=change_session_s]").val(),
                    'change_session_e': $("input[name=change_session_e]").val(),
                    'change_maxtn': $("input[name=change_maxtn]").val()
                },
                dataType: "json",
                success: function( data ) {
                    if(data === 'success') {
                        window.parent.$('[class^=d_termin_change_]').dialog('close');
                        $('#session_list').DataTable().ajax.reload();
                    }
                    else {
                        alert(data);
                    }
                }
            });
        });
	});
	</script>
	</head>
	<body>
	<table style="margin-left:15px; margin-top:10px;">
	<tr>
		<td colspan="2" width="200px" class="ad_edit_headline"><b>Datum</b></td>
		<td width="130px" class="ad_edit_headline">
		<input type="text" name="change_termin" class="datepicker" value="<?php echo $terminDatum;?>" size="12" maxlength="10" />
		</td>
		<td width="30px" class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Bitte nur im Format <b>tt.mm.jjjj</b> angeben." alt="" /></td>
	</tr>
	<tr>
		<td width="150px" class="ad_edit_headline"><b>Uhrzeit</b></td>
		<td width="50px" class="ad_edit_headline">
			<div style="text-align:right">Von: &nbsp;</div>
		</td>
		<td class="ad_edit_headline">
			<input type="text" class="timepicker" name="change_session_s" value="<?php echo $beginn;?>" size="20" maxlength="5" />
		</td>
		<td class="ad_edit_headline">
			<img src="images/info.gif" width="17" height="17" title="Bitte nur im Format <b>hh:mm</b> angeben." alt="" />
		</td>	
	</tr>
	<tr>
		<td class="ad_edit_headline"></td>
		<td class="ad_edit_headline">
			<div style="text-align:right">Bis: &nbsp;</div>
		</td>
		<td class="ad_edit_headline">
			<input type="text" class="timepicker" name="change_session_e" value="<?php echo $ende;?>" size="20" maxlength="5" />
		</td>
	</tr>
	<tr>
		<td colspan="2" class="ad_edit_headline">
			<b>Max. Teilnehmerzahl</b>
		</td>
		<td class="ad_edit_headline">
			<input type="text" name="change_maxtn" value="<?php echo $termin['maxtn'];?>" size="20" maxlength="255" />
		</td>
		<td></td>	
	</tr>
	<tr>
		<td colspan="2" class="ad_edit_headline"><b>bisher angemeldet</b></td>
		<td class="ad_edit_headline"><?php echo $termin['count'];?></td>
		<td  class="ad_edit_headline"></td>	
	</tr>
	
	<tr>
		<td colspan="5">
		 <div style="text-align:center">
		 <br />
		<input type="button" name="editsess" id="editsess" value="Ändern" data-id="<?php echo $termin['id'];?>" data-expid="<?php echo $_GET['expid'];?>" />&nbsp;&nbsp;
		<input type="button" name="editcancel" id="editcancel" value="Abbrechen" />
		</div>
		</td>  
	</tr> 
	
	</table>

	</body>
	</html>
	<?php
}

/* WENN TERMIN AUTOMATISCH ERSTELLT WURDE */
/* -------------------------------------- */
else {
	$sql = sprintf( '
		SELECT		termin.`id`,
					termin.`tag`,
					termin.`session_s`,
					termin.`session_e`,
					exp.id AS exp_id,
					exp.`exp_name`,
					exp.vpn_geschlecht,
					exp.vpn_gebdat,
					exp.vpn_tele1,
					exp.vpn_tele2,
					exp.vpn_email,
					lab.id AS lab_id,
					lab.label AS lab_name
		FROM		%1$s AS termin
		INNER JOIN	%2$s AS exp
			ON		termin.exp = exp.id
		INNER JOIN	%3$s AS lab
			ON		termin.lab_id = lab.id
		WHERE		termin.`id` = %4$d
		LIMIT		1'
			, TABELLE_SITZUNGEN
			, TABELLE_EXPERIMENTE
			, TABELLE_LABORE
			, $_GET['terminid']
	);
	$erg = $mysqli->query($sql) or die($mysqli->error);
	$termin = $erg->fetch_assoc();
	
	$terminDatum = formatMysqlDate($termin['tag']);
	$beginn = substr($termin['session_s'], 0, 5);
	$ende = substr($termin['session_e'], 0, 5);
	
	$sql = sprintf(
		'SELECT gebdat, vorname, nachname, geschlecht, telefon1, telefon2, email, vorname, nachname FROM %1$s WHERE termin = %2$d'
		, TABELLE_VERSUCHSPERSONEN
		, $_GET['terminid']
	);
	$erg = $mysqli->query($sql) or die($mysqli->error);
	
	?>
	<html>
	<head>
	
	<script type="text/javascript">
	$(document).ready(function() {
		$("[title]").each(function(){
			$(this).tooltip({ content: $(this).attr("title")});
		});    // It allows html content to tooltip.
		$('input[type="button"], input[type="submit"]').button();
	});
	</script>
	
	</head>
	<body>
		<h3>
			<?php echo $terminDatum;?>&nbsp;<?php echo $beginn;?>&nbsp;&#8212;&nbsp;<?php echo $ende;?>&nbsp;Uhr
		</h3>
		<div class="optionGroup">
			<div>
				<span class="h3 optionLabel">Experiment:&nbsp;</span>
				<a href="admin.php?expid=<?php echo $termin['exp_id'];?>"><?php echo $termin['exp_name'];?></a>
			</div>
			<div style="clear:both;"></div>
			<div>
				<span class="h3 optionLabel">Raum:&nbsp;</span>
				<a href="admin.php?menu=lab&id=<?php echo $termin['lab_id'];?>"><?php echo $termin['lab_name'];?></a>
			</div>
		</div>
	
		<div style="clear:left;"></div>
		
		<div style="margin-top:20px;">
			<h3>Anmeldungen:</h3>
		<?php
			while($versuchsPerson = $erg->fetch_assoc()) {
				if(!empty($gebdat)) {
					echo '<br/>';
				}
				$gebdat = formatMysqlDate($versuchsPerson['gebdat']);
				?><span title="<b><u><?php echo $versuchsPerson['vorname'] . ' ' . $versuchsPerson['nachname']; ?></u></b><br /><?php if ( $termin['vpn_geschlecht'] > 0 ) { ?><b>Geschlecht: </b><?php echo $versuchsPerson['geschlecht'] . "<br />"; }; ?><?php if ( $termin['vpn_gebdat'] > 0 ) { ?><b>Geburtsdatum: </b><?php echo $gebdat  . "<br />"; }; ?><?php if ( $termin['vpn_tele1'] > 0 ) { ?><b>Telefon 1: </b><?php echo $versuchsPerson['telefon1'] . "<br />"; }; ?><?php if ( $termin['vpn_tele2'] > 0 ) { ?><b>Telefon 2: </b><?php echo $versuchsPerson['telefon2'] . "<br />"; }; ?><?php if ( $termin['vpn_email'] > 0 ) { ?><b>Email: </b><?php echo $versuchsPerson['email']; }; ?>">
				<?php
				echo substr($versuchsPerson['vorname'],0,1) . '. ' . $versuchsPerson['nachname']; 
				?>
				</span>
				<?php
			}
		?>
		</div>
	
	
	</body>
	</html>
	<?php
}
?>
