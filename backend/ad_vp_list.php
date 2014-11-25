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
				echo PHP_EOL . sprintf(
					'["%1$d","%2$s","%3$s","%4$s","%5$s","%6$d","%7$d","%8$s"],'
					, /* 1 */ $dataVp['id']
					, /* 2 */ $dataVp['nachname'] . ", " . $dataVp['vorname']
					, /* 3 */ $dataVp['email']
					, /* 4 */ !empty($dataVp['tag']) && !empty($dataVp['session_s']) && !empty($dataVp['session_e']) ? formatMysqlDate($dataVp['tag']) . '&nbsp;&nbsp;&nbsp;' . substr($dataVp['session_s'], 0, 5) . '-' . substr($dataVp['session_e'], 0, 5) : ''
					, /* 5 */ $dataVp['exp_name']
					, /* 6 */ $dataVp['exp']
					, /* 7 */ $dataVp['termin']
					, /* 8 */ $dataVp['tag'] . $dataVp['session_s']
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
                    , "iDataSort": 7
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
					<?php if(isset($_GET['expid'])) { echo ', "bVisible": false';}?>
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
                ,{
                    "sTitle":""
                    , "bVisible": false
                    , "aTargets": [ 7 ]
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
			if(count == 0) {
				alert('Es sind keine Personen ausgewählt.');
				return;
			}
			if(!confirm(count + ' Versuchspersonen wirklich löschen?')) {
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
				alert('Es sind keine Personen ausgewählt.');
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

<?php $qString = http_build_query($_GET);?>

<table id="vp_list" style="width:<?php echo (isset($_GET['expid']) && 'admin.php' === basename($_SERVER['PHP_SELF'])) ? 852 : 900;?>px;table-layout:auto;">
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
		<?php if(!isset($_GET['expid'])) { echo '<th></th>';}?>
	</tr>
	</tfoot>
</table>
<br/>
<button id="delvp">Löschen</button>
<button id="sendmail">E-Mail</button>
