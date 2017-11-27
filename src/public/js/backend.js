var SessionCount = 0;
var DayCount = 0;
var cur_day;
var cur_sess;

function initDatepicker() {
	$('input.datepicker').datepicker({
		prevText: '&#x3c;zurück', prevStatus: '',
		prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
		nextText: 'Vor&#x3e;', nextStatus: '',
		nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
		currentText: 'heute', currentStatus: '',
		todayText: 'heute', todayStatus: '',
		clearText: '-', clearStatus: '',
		closeText: 'schließen', closeStatus: '',
		monthNames: ['Januar','Februar','März','April','Mai','Juni',
			'Juli','August','September','Oktober','November','Dezember'],
		monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
			'Jul','Aug','Sep','Okt','Nov','Dez'],
		dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
		dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		showMonthAfterYear: false,
		showOn: 'both',
		buttonImage: 'images/calender.png',
		buttonImageOnly: true,
		dateFormat:'dd.mm.yy',
		weekHeader: "W",
		yearSuffix: ""
	});
}

function getToday(addDays) {
	var date = new Date();
	var tag = date.getDate() + addDays;
	var monat = date.getMonth();
	monat++;
	var jahr = date.getFullYear();
	if ( tag < 10 ) { tag = '0' + tag; };
	if ( monat < 10 ) { monat = '0' + monat; };
	var gesamt = tag + '.' + monat + '.' + jahr;
	return gesamt;
}

function addDay( pNode ) {

	DayCount++;
	var cur_day = 'exp_tag' + DayCount;

	if(DayCount>1) {
		//dies ist nicht der erste hinzugefügte tag
		letzterNeuerTag = $('#exp_tag'+(DayCount-1));
		pNode = letzterNeuerTag.parent();
	}

	li = $('<li/>');
	$('<label>&nbsp;</label>').appendTo(li);

	$('<input/>').attr({
		type: 'text',
		id: cur_day,
		name: cur_day,
		size: '11',
		maxlength: '10',
		value: getToday(DayCount),
		class: 'datepicker'
	}).appendTo(li);

	li.insertAfter(pNode);
	initDatepicker();

	$('#days').val(DayCount);

}


function addSession( pNode ) {

	SessionCount++;
	var cur_sess = 'exp_session' + SessionCount;

	li = $('<li/>');
	$('<label>&nbsp;</label>').appendTo(li);

	li.append("Von: ");

	$('<input/>').attr({
		type: 'text',
		id: cur_sess + 's',
		name: cur_sess + 's',
		size: '7',
		maxlength: '5',
		value: '00:00',
		class: 'timepicker'
	}).appendTo(li);

	li.append("&nbsp;");
	li.append("Bis: ");

	$('<input/>').attr({
		type: 'text',
		id: cur_sess + 'e',
		name: cur_sess + 'e',
		size: '7',
		maxlength: '5',
		value: '00:00',
		class: 'timepicker'
	}).appendTo(li);

	li.insertAfter(pNode);
	$(".timepicker").timePicker({step:30, startTime:"08:00", endTime:"19:00"});

	$('#sessions').val(SessionCount);

}


/* MSG window & preview functions */

function show_preview( mobj, tobj ) {
	var str = document.getElementById(mobj).value.replace(/\n/g, '<br>');
	var str=str.replace(/!liebe\/r!/g, 'Lieber');
	var str=str.replace(/!vp_vorname!/g, 'Sigmund');
	var str=str.replace(/!vp_nachname!/g, 'Freud');

	var currentTime = new Date()
	var month = currentTime.getMonth() + 1
	var day = currentTime.getDate()
	var year = currentTime.getFullYear()
	var hours = currentTime.getHours()
	var minutes = currentTime.getMinutes()

	if ( day < 10 ) { day = '0' + day; };
	if ( month < 10 ) { month = '0' + month; };
	if ( hours < 10 ) { hours = '0' + hours; };
	if ( minutes < 10 ) { minutes = '0' + minutes; };

	var str=str.replace(/!termin!/g, '' + day + '.' + month + '.' + year);
	var str=str.replace(/!beginn!/g, '' + hours + ':' + minutes);
	var str=str.replace(/!ende!/g, '24:00');

	var str=str.replace(/!exp_name!/g, document.getElementById("exp_name").value);
	var str=str.replace(/!exp_ort!/g, document.getElementById("exp_ort").value.replace(/\n/g, '<br>'));

	var str=str.replace(/!vl_name!/g, document.getElementById("vl_name").value);
	var str=str.replace(/!vl_telefon!/g, document.getElementById("vl_tele").value);
	var str=str.replace(/!vl_email!/g, document.getElementById("vl_email").value);

	document.getElementById(tobj).innerHTML = unescape(str);
}

function showEmailPreview( areaId, terminid, targetDiv ) {

	var str = document.getElementById(areaId).value.replace(/\n/g, '<br>');
	var str=str.replace(/!liebe\/r!/g, 'Lieber');
	var str=str.replace(/!vp_vorname!/g, 'Sigmund');
	var str=str.replace(/!vp_nachname!/g, 'Freud');

	var input_termin = $("#termin" + terminid);
	if(input_termin.length) {
		str = str.replace(/!termin!/g, input_termin.val());
	}
	else {
		str = str.replace(/!termin!/g, '05.01.1961');
	}

	var input_beginn = $("#beginn" + terminid);
	if(input_beginn.length) {
		str = str.replace(/!beginn!/g, input_beginn.val());
	}
	else {
		str = str.replace(/!beginn!/g, '13:00');
	}

	var input_ende = $("#ende" + terminid);
	if(input_ende.length) {
		str = str.replace(/!ende!/g, input_ende.val());
	}
	else {
		str = str.replace(/!ende!/g, '14:00');
	}

	var input_exp_name = $("#exp_name");
	if(input_exp_name.length) {
		str = str.replace(/!exp_name!/g, input_exp_name.val());
	}
	else {
		str = str.replace(/!exp_name!/g, 'Milgram-Experiment');
	}

	var input_exp_ort = $("#exp_ort");
	if(input_exp_ort.length) {
		str = str.replace(/!exp_ort!/g, input_exp_ort.val().replace(/\n/g, '<br>'));
	}
	else {
		str = str.replace(/!exp_ort!/g, 'Raum 001');
	}

	var input_vl_name = $("#vl_name");
	if(input_vl_name.length) {
		str = str.replace(/!vl_name!/g, input_vl_name.val());
	}
	else {
		str = str.replace(/!vl_name!/g, 'Stanley Milgram');
	}

	var input_vl_tele = $("#vl_tele");
	if(input_vl_tele.length) {
		str = str.replace(/!vl_telefon!/g, input_vl_tele.val());
	}
	else {
		str = str.replace(/!vl_telefon!/g, '0221 99499');
	}

	var input_vl_email = $("#vl_email");
	if(input_vl_email.length) {
		str = str.replace(/!vl_email!/g, input_vl_email.val());
	}
	else {
		str = str.replace(/!vl_email!/g, 'methexplabor@googlemail.com');
	}

	document.getElementById(targetDiv).innerHTML = unescape(str);
}

function sendmsg(vpIdList, msgBody, resultDiv) {
	document.getElementById(resultDiv).innerHTML = "lade...";
	content = document.getElementById(msgBody).value.replace(/\n/g, '<br>');
	betreff = document.getElementById("betreff").value;

	$.ajax({
		type: 'GET',
		url: 'service/mail.php',
		data: {
			'vpIdList': vpIdList,
			'content': content,
			'betreff': betreff
		},
		dataType: "json",
		success: function( data ) {
			document.getElementById(resultDiv).innerHTML = "Nachricht versendet!";
		},
		error: function( data ) {
			document.getElementById(resultDiv).innerHTML = "Fehler!";
		}
	});
}

function showEmailRecipients(expid, terminid) {
	document.getElementById("namelist").innerHTML = 'lade...';
	$.ajax({
		type: 'GET',
		url: 'service/mail.php?expid='+expid+'&termin='+terminid,
		dataType: "json",
		success: function( data ) {
			var nameList = '';
			var idList = '';
			$.each(data, function(i, item) {
				nameList = nameList + ', ' + item.name + ' (' + item.email + ')';
				idList = idList + ', ' + item.id;
			});
			if(nameList.length > 0) {
				nameList = nameList.substring(2);
				idList = idList.substring(2);
			}
			inputField = getInputFieldVpId();
			inputField.val(idList);
			document.getElementById("namelist").innerHTML = nameList;
		}
	});
}

function terminchange() {
	expid = QueryString.get("expid");
	terminid = document.getElementById("termin").value;
	showEmailRecipients(expid, terminid);
	showEmailPreview('nachricht', terminid, 'terminmailpreview');
}

function getInputFieldVpId() {
	var input = $('input[id="vpIdHidden"]');
	if(0 == input.length) {
		input = $('<input>', {'name': 'vpid', 'type': 'hidden', 'id': 'vpIdHidden'}).appendTo("body");
	}
	return input;
}