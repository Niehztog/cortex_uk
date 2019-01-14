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

?>

<script type="text/javascript">
    $( document ).ready(function() {
        $('input[type="button"], input[type="submit"]').button();

        oTable2 = $("#session_list").dataTable({
            "bFilter": true,
            "sDom": '<"H"lr>t<"F"ip>',
            "bJQueryUI": false,
            "bAutoWidth": false,
            "bPaginate": false,
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
            "aaSorting": [],
            "sAjaxSource": 'service/session.php?action=list&expid=<?php echo $_GET['expid'];?>',
            "aoColumnDefs":[
                {
                    "sTitle":"<input type=\"checkbox\" name=\"checkall_session\" id=\"checkall_session\">"
                    , "aTargets": [ 0 ]
                    , "sWidth": "20px"
                    , "bSortable": false
                    , "mRender": function ( vpid, type, full ) {
                        return '<input type="checkbox" name="vpid[]" value="'+vpid+'">';
                    }
                }
                ,{
                    "sTitle":"Datum"
                    , "aTargets": [ 1 ]
                    , "sWidth": "90px"
                    , "bSortable": true
                    , "mRender": function ( datum, type, full ) {
                        return datum;
                    }
                }
                ,{
                    "sTitle":"Beginn"
                    , "aTargets": [ 2 ]
                    , "sWidth": "60px"
                    , "bSortable": true
                    , "mRender": function ( beginn, type, full ) {
                        return beginn;
                    }
                }
                ,{
                    "sTitle":"Ende"
                    , "aTargets":[ 3 ]
                    , "sWidth": "60px"
                    , "bSortable": true
                    , "iDataSort": 7
                    , "mRender": function ( ende, type, full ) {
                        return ende;
                    }
                }
                ,{
                    "sTitle":"Ort"
                    , "aTargets": [ 4 ]
                    //, "sWidth": "120px"
                    , "bSortable": true
                    , "mRender": function ( ort, type, full ) {
                        return !ort ? '<span style="font-style:italic;color:#d3d3d3">[manueller&nbsp;Termin]</span>' : ort;
                    }
                }
                ,{
                    "sTitle":"VP"
                    , "aTargets": [ 5 ]
                    , "sWidth": "40px"
                    , "bSortable": true
                    , "mRender": function ( vp, type, full ) {
                        return vp;
                    }
                }
                ,{
                    "sTitle":"Teilnehmer"
                    , "aTargets": [ 6 ]
                    , "sWidth": "130px"
                    , "bSortable": true
                    , "mRender": function ( namesOnly, type, full ) {
                        var teilnehmer = full[7];
                        var output = '';
                        $.each( teilnehmer, function( key, value ){
                            if(key > 0) {
                                output += '<br />';
                            }
                            var title = '';
                            $.each( value['title'], function( tkey, tvalue ){
                                if(tkey === 'Name') {
                                    title += '<b><u>' + tvalue + '</b></u>';
                                }
                                else {
                                    title += '<br/><b>' + tkey + ':</b> ' + tvalue;
                                }
                            });
                            output += '<span title="'+title+'">' + value['name'] + '</span>';
                        });
                        return output;
                    }
                }
                ,{
                    "sTitle":"Aktionen"
                    , "aTargets": [ 7 ]
                    , "sWidth": "75px"
                    , "bSortable": true
                    , "mRender": function ( teilnehmer, type, full ) {
                        return '' +
                            '<a href="" class="link_session_delete" title="Termin löschen" data-id="'+full[0]+'"><span class="ui-icon ui-icon-trash"></span></a>' +
                            '<a href="" class="link_session_change" title="Termin ändern" data-id="'+full[0]+'"><span class="ui-icon ui-icon-wrench"></span></a>\n' +
                            (teilnehmer.length > 0 ? '<a href="" class="link_send_mail" title="E-Mail an Teilnehmer senden" data-id="'+full[0]+'"><span class="ui-icon ui-icon-mail-closed"></span></a>' : '') + '\n';
                    }
                }
            ],
            "drawCallback": function( settings ) {
                $("[title]").each(function(){
                    $(this).tooltip({ content: $(this).attr("title")});
                });
                initClickEvents();
            },
            "footerCallback": function ( row, data, start, end, display ) {
                var api = this.api(), data;

                // Remove the formatting to get integer data for summation
                var intVal = function ( i ) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '')*1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // Total over all pages
                total = api
                    .column( 5 )
                    .data()
                    .reduce( function (a, b) {
                        if(typeof b === 'string') {
                            b = b.substring(0, b.indexOf('/'));
                        }

                        return intVal(a) + intVal(b);
                    }, 0 );

                var maxVp = '<?php echo $data['max_vp'];?>';
                var terminvergabemodus = '<?php echo  $data['terminvergabemodus']; ?>';
                if(terminvergabemodus === 'manuell') {
                    var maxVp = api
                        .column( 5 )
                        .data()
                        .reduce( function (a, b) {
                            if(typeof b !== 'string' || b.indexOf('/') == -1) {
                                return a;
                            }
                            b = b.substring(b.indexOf('/')+1);
                            return intVal(a) + intVal(b);
                        }, 0 );
                }

                // Update footer
                $( api.column( 5 ).footer() ).html(total + '/'+maxVp);

                var column4empty = true;
                api.column( 4 ).data().each(function( index ) {
                    if(index !== '') {
                        column4empty = false;
                    }
                });
                api.column( 4 ).visible(!column4empty);

            }
        }).columnFilter({
            aoColumns: [
                null,
                { type: "select" },
                { type: "select" },
                { type: "select" },
                { type: "select" },
                null,
                { type: "text" }
            ]
        });

        $('#checkall_session').click(function(e) {
            var chk = $(this).prop('checked');
            $('input', oTable2.$('tr', {"filter": "applied"} )).prop('checked',chk);
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

        initClickEvents();

    });

    function initClickEvents() {
        $('a.link_send_mail').on('click', function(e){
            e.preventDefault();

            var sessionId = $(this).data("id");

            var $dialogSendMsg = $('<div id="send_msg_'+sessionId+'"></div>')
                .load('backend/send_msg.php?expid=<?php echo $_GET['expid'];?>&terminid='+sessionId)
                .dialog({
                    title: 'E-Mail senden',
                    autoOpen: false,
                    width: 668,
                    height: 490,
                    modal: true,
                    close: function (e, ui) { $(this).remove(); }
                });
            $dialogSendMsg.dialog('open');
        });

        $('a.link_session_change').on('click', function(e){
            e.preventDefault();

            var sessionId = $(this).data("id");

            var $dialogTerminEdit = $('<div class="d_termin_change_'+sessionId+'"></div>')
                .load('backend/ad_termin_change.php?expid=<?php echo $_GET['expid'];?>&terminid='+sessionId)
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

        $('a.link_session_delete').on('click', function(e){
            e.preventDefault();

            var sessionId = $(this).data("id");
            if(!confirm('Termin wirklich endgültig löschen?')) {
                return;
            }
            var tableRow = $(this).closest('tr');

            $.ajax({
                type: "POST",
                url: "service/session.php",
                data: {
                    'action': 'delete',
                    'id': sessionId
                },
                dataType: "json",
                success: function(data) {
                    if(data === 'success') {
                        tableRow.remove();
                    }
                    else {
                        alert(data);
                    }
                }
            });
        });
    }
</script>

<br />
<table id="session_list" style="width:852px;table-layout:fixed;" class="display compact">
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
        <th></th>
        <th></th>
        <th></th>
    </tr>
    </tfoot>
</table>
<br/>


<?php
if('manuell'===$data['terminvergabemodus']) {
    ?>
    <br />
    <input name="add" type="button" value="Termin hinzufügen" />
    <?php
}?>
