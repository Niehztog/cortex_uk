<?php
require_once __DIR__ . '/../include/functions.php';
require_once __DIR__ . '/../include/class/DatabaseFactory.class.php';
require_once __DIR__ . '/../include/class/ExperimentDataProvider.class.php';
$dbf = new DatabaseFactory();
$mysqli = $dbf->get();

$abfrage2 = sprintf( '
	SELECT		vp.id,
				exp.id AS exp,
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
				vp.anruf,
				termin.tag,
				termin.session_s,
				termin.session_e,
				IF(LENGTH(exp.exp_name) > 30,
					CONCAT(SUBSTRING(exp.exp_name, 1, 30), " [&hellip;]"),
					exp.exp_name) AS exp_name,
				exp.vpn_geschlecht,
				exp.vpn_gebdat,
				exp.vpn_tele1,
				exp.vpn_tele2,
				exp.vpn_email,
				exp.exp_ort,
				exp.vl_name,
				exp.vl_tele,
				exp.vl_email
	FROM		%1$s AS vp
	LEFT JOIN	%2$s AS termin
		ON		vp.termin = termin.id
	LEFT JOIN	%3$s AS exp
		ON		vp.exp = exp.id
	%4$s
	ORDER BY	vp.nachname ASC, vp.vorname ASC'
	, TABELLE_VERSUCHSPERSONEN
	, TABELLE_SITZUNGEN
	, TABELLE_EXPERIMENTE
	, isset($_GET['expid']) ? sprintf('WHERE vp.exp = %1$d', $_GET['expid']) : ''
);
$resultVp = $mysqli->query($abfrage2);
if(false === $resultVp) {
	trigger_error($mysqli->error, E_USER_WARNING);
}

if(!isset($_GET['expid'])) {
	?>
	<script type="text/javascript">
		var oTable;
	
		$( document ).ready(function() {
			$('input[type="button"], input[type="submit"]').button();
			
			oTable = $("#vp_list").dataTable({
				"bFilter": true,
				"sDom": '<"H"lr>t<"F"ip>',
				"bJQueryUI": true,
				"bAutoWidth": false,
				"bPaginate": true,
				"sPaginationType": "full_numbers",
				"oLanguage": {
					"sProcessing":   "Bitte warten...",
					"sLengthMenu":   "_MENU_ Einträge anzeigen",
					"sZeroRecords":  "Keine Einträge vorhanden.",
					"sInfo":		 "_START_ bis _END_ von _TOTAL_ Einträgen",
					"sInfoEmpty":	"0 bis 0 von 0 Einträgen",
					"sInfoFiltered": "(gefiltert von _MAX_  Einträgen)",
					"sInfoPostFix":  "",
					"sSearch":	   "Suchen",
					"sUrl":		  "",
					"oPaginate": {
						"sFirst":	"Erster",
						"sPrevious": "Zurück",
						"sNext":	 "Weiter",
						"sLast":	 "Letzter"
					}
				},
				"aaData":[<?
				while($dataVp = $resultVp->fetch_assoc()) {
					//$gebdat = formatMysqlDate($dataVp['gebdat']);
					
					echo PHP_EOL . sprintf(
						'["%1$d","%2$s","%3$s","%4$s","%5$s","%6$d","%7$d"],'
						, /* 1 */ $dataVp['id']
						, /* 2 */ $dataVp['nachname'] . ", " . $dataVp['vorname']
						, /* 3 */ $dataVp['email']
						, /* 4 */ !empty($dataVp['tag']) && !empty($dataVp['session_s']) && !empty($dataVp['session_e']) ? formatMysqlDate($dataVp['tag']) . '&nbsp;&nbsp;&nbsp;' . substr($dataVp['session_s'], 0, 5) . '-' . substr($dataVp['session_e'], 0, 5) : ''
						, /* 5 */ $dataVp['exp_name']
						, /* 6 */ $dataVp['exp']
						, /* 7 */ $dataVp['termin']
					);
				}
				?>],
				"aoColumnDefs":[
					{
						"sTitle":"<input type=\"checkbox\" name=\"checkall\" value=\"checkall\" id=\"checkall\">"
						, "aTargets": [ 0 ]
						, "bSortable": false
						, "mRender": function ( vpid, type, full ) {
							return '<input type="checkbox" name="vpid[]" value="'+vpid+'">';
						}
					}
					,{
						"sTitle":"Name"
						, "aTargets": [ 1 ]
						, "bSortable": true
						, "mRender": function ( name, type, full ) {
							return '<a class="vp_edit" href="backend/vp_edit.php?editvp='+full[0]+'&expid='+full[5]+'">'+name+'</a>';
						}
					}
					,{
						"sTitle":"Email"
						, "aTargets": [ 2 ]
						, "bSortable": true
						, "mRender": function ( email, type, full ) {
							return '<a href="mailto:'+email+'">' + email + '</a>';
						}
					}
					,{
						"sTitle":"Termin"
						, "aTargets":[ 3 ]
						, "bSortable": true
						, "mRender": function ( termin, type, full ) {
							return '<a class="termin_change_'+full[6]+'" href="backend/ad_termin_change.php?terminid='+full[6]+'">'+termin+'</a>';
						}
					}
					,{
						"sTitle":"Experiment"
						, "aTargets": [ 4 ]
						, "bSortable": true
						, "mRender": function ( exp, type, full ) {
							return '' != exp ? '<a href="admin.php?expid='+full[5]+'">' + exp + '</a>' : '';
						}
					}
					,{
						"sTitle":""
						, "bVisible": false
						, "aTargets": [ 5 ]
					}
					,{
						"sTitle":""
						, "bVisible": false
						, "aTargets": [ 6 ]
					}
				]
			}).columnFilter({
				aoColumns: [
					null,
					{ type: "text" },
					{ type: "text" },
					{ type: "select" },
					{ type: "select" }
				]
			});

			$('#checkall').click(function(e) {
				var chk = $(this).prop('checked');
				$('input', oTable.$('tr', {"filter": "applied"} )).prop('checked',chk);
			});

			$("a.vp_edit").live('click', function (e) {
				e.preventDefault();
				var page = $(this).attr("href");
				var vp_id = $(this).closest('td').prev().children('input').first().val();
				var $dialogVpEdit = $('<div id="editvp_'+vp_id+'"></div>')
					.load(page)
					.dialog({
						title: 'Versuchspersonendaten ändern',
						autoOpen: false,
						width: 446,
						height: 474,
						modal: true,
						close: function (e, ui) { $(this).remove(); }
					});
				$dialogVpEdit.dialog('open');
			});
			$("a[class^=termin_change_]").live('click', function (e) {
				e.preventDefault();
				var page = $(this).attr("href");
				var termin_id = $(this).attr("class").substring(14);
				var $dialogTerminEdit = $('<div class="d_termin_change_'+termin_id+'"></div>')
					.load(page)
					.dialog({
						title: 'Termin ändern',
						autoOpen: false,
						width: 446,
						modal: true,
						close: function (e, ui) { $(this).remove(); }
					});
				$dialogTerminEdit.dialog('open');
			});
			$('button#delvp').on('click', function(e){
				e.preventDefault();
				var inputNodes = $('input[type=checkbox]:checked', oTable.fnGetNodes());
				var count = inputNodes.length;
				if(count == 0 || !confirm(count + ' Versuchspersonen wirklich löschen?')) {
					return;
				}
				var newForm = $('<form>', { 'method': 'post', 'action': 'vpview.php'});
				$('<input>', {'name': 'action', 'value': 'delete'}).appendTo(newForm);
				inputNodes.appendTo(newForm);
				newForm.appendTo('body');
				newForm.submit();
			});
			$("button#sendmail").on('click', function(e) {
				e.preventDefault();
				var inputNodes = $('input[type=checkbox]:checked', oTable.fnGetNodes());
				if(inputNodes.length == 0) {
					return;
				}
				var $dialogSendMsg = $('<div class="send_msg"></div>')
					.load('backend/send_msg.php?'+inputNodes.serialize())
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
	
	<h1>Versuchspersonen</h1>
	
	<?php $qString = http_build_query($_GET);?>

	<table id="vp_list" style="width:900px;table-layout:auto;">
		<thead>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</tfoot>
	</table>
	<br/>
	<button id="delvp">Löschen</button>
	<button id="sendmail">E-Mail</button>

	<?
}
else {
	/* ALLE VERSUCHSPERSONEN EINES EXPERIMENTES ANZEIGEN */  
	/*----------------------------------------------------*/
	$dataVp = $resultVp->fetch_assoc();
	?>
	<script type="text/javascript">
	$( document ).ready(function() {
		 $( "#accordion" ).accordion({
			header: "h3",
			collapsible: true,
			heightStyle: "content",
			active: false
		});
		$('input[type="button"], input[type="submit"]').button();
		$("input[name=editvp]").on('click', function() {
			var vp_id = $(this).siblings("input[name=vpid]").first().val();
			var $dialogVpEdit = $('<div id="editvp_'+vp_id+'"></div>')
				.load('backend/vp_edit.php?editvp='+vp_id+'&expid='+$.QueryString("expid"))
				.dialog({
					title: 'Versuchspersonendaten ändern',
					autoOpen: false,
					width: 446,
					height: 474,
					modal: true,
					close: function (e, ui) { $(this).remove(); }
				});
			$dialogVpEdit.dialog('open');
		});
	});
	</script>
	
	<input type="hidden" id="exp_name" value="<?= $dataVp['exp_name']?>" />	
	<input type="hidden" id="exp_ort" value="<?= $dataVp['exp_ort']?>" />	
	<input type="hidden" id="vl_name" value="<?= $dataVp['vl_name']?>" />	
	<input type="hidden" id="vl_tele" value="<?= $dataVp['vl_tele']?>" />	
	<input type="hidden" id="vl_email" value="<?= $dataVp['vl_email']?>" />	
	
	<div id="accordion">
		<h3>Nachricht senden</h3>
		<div>
			<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="vpmsg">
			<table>
				<tr>
					<td class="ad_edit_headline" width="110px"><b>Absender:</b></td>
					<td class="ad_edit_headline">
						<?= $dataVp['vl_email'] ?>
						<input name="absender" type="hidden" value="<?= $dataVp['vl_email'] ?>" />
					</td>
				</tr>	
				<tr>
					<td class="ad_edit_headline"><b>Betreff:</b></td>
					<td class="ad_edit_headline"><input name="betreff" type="text" size="67" /></td>
				</tr>	  
				<tr>
					<td class="ad_edit_headline">
						<b>Nachricht:</b>
						<p style="font-variant: small-caps; font-size: 12px; margin-bottom: 3px; margin-left: 3px; ">Variablen</p>
						<div style="font-size: 11px; cursor:pointer; font-style:italic; margin-left:6px; ">
							<a onclick="javascript:$('#nachricht').insertAtCaret('!liebe/r!');" title="Geschlechtsspezifische Anrede<br /><br /><font color=#FF0000><b>Nur verwenden, wenn VP bei der Anmeldung<br />ihr Geschlecht angeben müssen!</b></font>">!liebe/r!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!vp_vorname!');" title="Vorname der Versuchsperson">!vp_vorname!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!vp_nachname!');" title="Nachname der Versuchsperson">!vp_nachname!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!termin!');" title="Von der VP ausgewähltes Datum; <b>dd.mm.yyyy</b>">!termin!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!beginn!');" title="Beginn der von der VP ausgewählten Session; <b>hh:mm</b>">!beginn!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!ende!');" title="Ende der von der VP ausgewählten Session; <b>hh:mm</b>">!ende!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!exp_name!');" title="Name des Experiments">!exp_name!</a><br />
							<a onclick="javascript:$('#nachricht').insertAtCaret('!exp_ort!');" title="Ort des Experiments">!exp_ort!</a><br />	
							<a onclick="javascript:$('#nachricht').insertAtCaret('!vl_name!');" title="Name des Versuchsleiters">!vl_name!</a><br />	
							<a onclick="javascript:$('#nachricht').insertAtCaret('!vl_telefon!');" title="Telefonnummer des Versuchsleiters">!vl_telefon!</a><br />	
							<a onclick="javascript:$('#nachricht').insertAtCaret('!vl_email!');" title="E-Mail-Adresse des Versuchsleiters">!vl_email!</a><br /><br />	
						</div>
					</td>
					<td class="ad_edit_headline">
						<textarea name="nachricht" id="nachricht" cols="65" rows="12"></textarea><br><br>
						<input name="expid" type="hidden" value="<?= $_GET['expid'] ?>">	
						<input name="vpmsgbut" type="submit" value="Nachricht senden" />
						<input type="button" onclick="javascript:show_preview('nachricht','expmailpreview');" value="Vorschau" />
						<? if ( isset($_POST['vpmsgbut'])) { ?>&nbsp;&nbsp;&nbsp;<font color="#ff0000"><b>Nachricht versendet!</b></font><? } ?>
						<br /><br /><br />
					</td>
				</tr>
			</table>
			<div id="expmailpreview" style="width:180px; font-size:11px; "></div>
			</form>
		</div>
	</div>
	
	Versuchspersonen:&nbsp;
	<?php
	try {
		$edp = new ExperimentDataProvider((int)$_GET['expid']);
		$vpcur = $edp->getSignUpCountCurrent();
		$vpmax = $edp->getSignUpCountMax();
		
		echo $vpcur;
		
		if($vpmax > 0) {
			echo '/' . $vpmax;
		}
	}
	catch(Exception $e) {
		echo '<!--' . $e . '-->';
	}
	?>
	
	<table>
		<tr>
			<td style="padding:3px;"><b>Name</b></td>  
			<td style="padding:3px;"><b>Email</b></td>  
			<td style="padding:3px;"><b>Termin</b></td>  
		</tr>
		
		
		<?
		$resultVp->data_seek(0);
		while($dataVp = $resultVp->fetch_assoc()) {
			$gebdat = formatMysqlDate($dataVp['gebdat']);
			
			?>
			<tr>
				<td class="termin" style="padding:3px;">
					<span title="<b><u><?php echo $dataVp['vorname'] . ' ' . $dataVp['nachname']; ?></u></b><br><?php if ( $dataVp['vpn_geschlecht'] > 0 ) { ?><b>Geschlecht: </b><?php echo $dataVp['geschlecht'];  ?><br><?php }; if ( $dataVp['vpn_gebdat'] > 0 ) { ?><b>Geburtsdatum: </b><?php echo $gebdat;  ?><br><?php }; if ( $dataVp['vpn_tele1'] > 0 ) { ?><b>Telefon 1: </b><?php echo $dataVp['telefon1']; ?><br><?php }; if ( $dataVp['vpn_tele2'] > 0 ) { ?><b>Telefon 2: </b><?php echo $dataVp['telefon2']; ?><br><?php }; if ( $dataVp['vpn_email'] > 0 ) { ?><b>Email: </b><?php echo $dataVp['email']; }; ?>">
					<?
					echo $dataVp['nachname'] . ", " . $dataVp['vorname']; 
					
					?>
					</span>
				</td>
				<td class="termin" style="padding:3px;">
					<a href="mailto:<?= $dataVp['email']?>"  id="mainframe_exp_link"><?= $dataVp['email']; ?>&nbsp;</a>
				</td>
				<td class="termin" style="padding:3px;">
					<?
					if(!empty($dataVp['tag']) && !empty($dataVp['session_s']) && !empty($dataVp['session_e'])) {
						echo formatMysqlDate($dataVp['tag']) . '&nbsp;&nbsp;&nbsp;' . substr($dataVp['session_s'], 0, 5) . '-' . substr($dataVp['session_e'], 0, 5) . '&nbsp;&nbsp;&nbsp;';
					
						if ( isset($_POST['vpmsgbut'])) {
							$termin = formatMysqlDate($dataVp['tag']);
							$letter = $_POST['nachricht'];
							
							if ($dataVp['geschlecht'] == "männlich") { $letter = str_replace("!liebe/r!","Lieber",$letter); }
							else { $letter = str_replace("!liebe/r!","Liebe",$letter); }
							
							$letter = str_replace("!vp_vorname!",$dataVp['vorname'],$letter);
							$letter = str_replace("!vp_nachname!",$dataVp['nachname'],$letter);
							
							$letter = str_replace("!termin!",$termin,$letter);
							$letter = str_replace("!beginn!",substr($dataVp['session_s'],0,5),$letter);
							$letter = str_replace("!ende!",substr($dataVp['session_e'],0,5),$letter);
							
							$letter = str_replace("!exp_name!",$dataVp['exp_name'],$letter);
							$letter = str_replace("!exp_ort!",$dataVp['exp_ort'],$letter);
							
							$letter = str_replace("!vl_name!",$dataVp['vl_name'],$letter);
							$letter = str_replace("!vl_telefon!",$dataVp['vl_tele'],$letter);
							$letter = str_replace("!vl_email!",$dataVp['vl_email'],$letter);
							
							
							mail($dataVp['email'],$_POST['betreff'],$letter,"from:" . $_POST['absender']);
						}
					}
					?>
				</td>
				<td class="termin" style="padding:3px;">
					<form action="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);?>" method="post" enctype="multipart/form-data" name="vpedit">
					<input type="hidden" name="vpid" value="<?= $dataVp['id'] ?>" />
					<input type="button" name="editvp" value="Ändern" /><br />
					<input type="submit" name="delvp" value="Löschen" />
					</form>	
				</td>
			</tr>
			<?
		}
		?>
	</table>
	<?
}