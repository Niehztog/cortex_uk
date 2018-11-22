<?php
/* TERMIN HINZUFÜGEN */
/* ----------------- */

  
?>
<html>
<head>
<script type="text/javascript">
$( document ).ready(function() {
	$("[title]").each(function(){
		$(this).tooltip({ content: $(this).attr("title")});
	});    // It allows html content to tooltip.
	$('input[type="button"]').button();
	initDatepicker();
	$(".timepicker").timePicker({step:30, startTime:"08:00", endTime:"19:00"});

    $('#addcancel').on('click', function(e){
        e.preventDefault();
        window.parent.$('[id=add]').dialog('close');
    });

    $('#addsess').on('click', function(e){
        e.preventDefault();

        var expid = $(this).data("id");

        $.ajax({
            type: "POST",
            url: "service/session.php",
            data: {
                'action': 'add',
                'expid': expid,
                'hinzu_termin': $("input[name=hinzu_termin]").val(),
                'hinzu_session_s': $("input[name=hinzu_session_s]").val(),
                'hinzu_session_e': $("input[name=hinzu_session_e]").val(),
                'hinzu_maxtn': $("input[name=hinzu_maxtn]").val()
            },
            dataType: "json",
            success: function( data ) {
                if(data === 'success') {
                    window.parent.$('[id=add]').dialog('close');
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

<table>
  <tr>
	<td colspan="2" width="200px" class="ad_edit_headline"><b>Datum</b></td>
	<td width="130px" class="ad_edit_headline">
	<input type="text" class="datepicker" id="hinzu_termin" name="hinzu_termin" size="12" maxlength="10" />
	</td>
	<td width="30px" class="ad_edit_headline"><img src="images/info.gif" width="17" height="17" title="Bitte nur im Format <b>tt.mm.jjjj</b> angeben."></td>
  </tr>
  <tr><td><br /></td></tr>
  <tr>
	<td width="150px"><b>Uhrzeit</b></td>
	<td width="50px" class="ad_edit_headline"><div style="text-align:right">Von: &nbsp;</div> </td>
	<td class="ad_edit_headline"><div><input type="text" class="timepicker" name="hinzu_session_s" value="00:00" size="20" maxlength="5" /></div></td>
	<td class="ad_edit_headline"><div><img src="images/info.gif" width="17" height="17" title="Bitte nur im Format <b>hh:mm</b> angeben."></div></td>
  </tr>
  <tr>
	<td class="ad_edit_headline"></td>
	<td width="50px" class="ad_edit_headline"><div style="text-align:right">Bis: &nbsp;</div></td>
	<td class="ad_edit_headline"><div><input type="text" class="timepicker" name="hinzu_session_e" value="00:00" size="20" maxlength="5" /></div></td>
  </tr>
  <tr><td><br /></td></tr>
  <tr>
	<td colspan="2" class="ad_edit_headline"><b>Max.&nbsp;Teilnehmerzahl</b></td>
	<td class="ad_edit_headline"><div><input type="text" name="hinzu_maxtn" value="" size="20" maxlength="255" /></div></td>
	<td class="ad_edit_headline"></td>
  </tr>
  <tr><td><br /></td></tr>
  <tr>
	<td colspan="5" class="ad_edit_headline">
		<div style="text-align:center">
			<input name="addsess" id="addsess" type="button" value="Termin hinzufügen" data-id="<?php echo $_GET['expid'];?>" />&nbsp;&nbsp;
			<input name="addcancel" id="addcancel" type="button" value="Abbrechen" />
		</div>
	</td>
  </tr>
</table>

</body>
</html>