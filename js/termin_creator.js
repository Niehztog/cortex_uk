var SessionCount = 0;
var DayCount = 0;
var cur_day;
var cur_sess;
var mousex;
var mousey;
var x=0,y=0,xm=0,ym=0; 
var klick = false;
var mklick = false; 
var tooltip;

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

function initCalendar(eventData, purpose) {
	
	var backgroundColorDefault = '#D2DBF8';
	var backgroundColorHover = '#6C91F8';
	var backgroundColorSelected = '#FFB2B2';
	
	var localOptions = {
		buttonText: {
			today: 'Heute',
			month: 'Monat',
			day: 'Tag',
			week: 'Woche'
		},
		firstDay: 1,
		monthNames: ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
		monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sept','Okt','Nov','Dez'],
		dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
		dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		axisFormat: 'H(:mm) \'Uhr\'',
		timeFormat: 'H:mm{ - H:mm} \'Uhr\'',
		columnFormat: {month: 'ddd',week: 'ddd, d.M.yyyy',day: 'dddd, d.M.yyyy'},
		titleFormat: {month: 'MMMM yyyy',week: "d.[ MMMM][ yyyy]{ '&#8212;' d. MMMM yyyy}",day: 'dddd, d. MMMM yyyy'}
	}
	
	var interactionOptions = {};
	
	if('manage' == purpose) {
		interactionOptions = {
			editable: true,
			selectable: true,
			selectHelper: true,
			select: function(start, end, allDay) {
				var start = $.fullCalendar.formatDate(start, "yyyy-MM-dd HH:mm:ss");
				var end = $.fullCalendar.formatDate(end, "yyyy-MM-dd HH:mm:ss");
				calendar.fullCalendar(
					'renderEvent',
					{
						title: '',
						start: start,
						end: end,
						allDay: allDay
					},
					true // make the event "stick"
				);
				calendar.fullCalendar('unselect');
			},
	
			eventClick: function(event) {
				if(event.editable != false) {
					var decision = confirm("Soll dieser Termin gelöscht werden?");
					if (decision) {
						if(event.id == undefined) {
							$('#calendar').fullCalendar('removeEvents', event._id);
						}
						else {
							$('#calendar').fullCalendar('removeEvents', event.id);
						}
					}
				}
				else {
					var $dialogTerminControl = $('<div id="control_'+event.id+'"></div>')
						.load('backend/ad_termin_change.php?terminid='+event.id)
						.dialog({
							title: 'Termin verwalten',
							autoOpen: false,
							width: 400,
							height: 250,
							modal: true,
							close: function (e, ui) { $(this).remove(); }
						});
					$dialogTerminControl.dialog('open');
				}
			},
			
			eventRender: function(event, element) {
				if(event.title != '') {
					var datum = $.fullCalendar.formatDate( event.start, 'dddd\', den\' d.M.yyyy', {dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag']} );
					var uhrzeit_von = $.fullCalendar.formatDate( event.start, 'H:mm' );
					var uhrzeit_bis = $.fullCalendar.formatDate( event.end, 'H:mm' );
					details = datum + ' ' + uhrzeit_von + ' - ' + uhrzeit_bis+' Uhr<br/>'+event.title;
					element.tooltip({ items: '*', content: details, track: false});
				}

				if(!$("input[id=show_allocation_chk]").is(':checked') && event.editable == false) {
					return false;
				}
			}
			
		}
	}
	else if('signup' == purpose) {
		interactionOptions = {
			eventMouseover: function(event, jsEvent, view) {
				if(event.backgroundColor != backgroundColorSelected) {
					$(this).css('background-color', backgroundColorHover);
				}

			},
			eventMouseout: function(event, jsEvent, view) {
				if(event.backgroundColor != backgroundColorSelected) {
					$(this).css('background-color', backgroundColorDefault);
				}

			},
				
			eventClick: function(event) {
				
				var datum = $.fullCalendar.formatDate( event.start, 'dddd\', den\' d.M.yyyy', {dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag']} );
				var uhrzeit_von = $.fullCalendar.formatDate( event.start, 'H(:mm)' );
				var uhrzeit_bis = $.fullCalendar.formatDate( event.end, 'H(:mm)' );
				
				var decision = confirm('Für die Teilnahme am ' + datum + ' von ' + uhrzeit_von + ' bis ' + uhrzeit_bis + ' Uhr anmelden?');
				
				if(decision) {
					$('<form action="'+window.location+'" method="POST">' +
						'<input type="hidden" name="chosendate" value="1">' +
						'<input type="hidden" name="id" value="' + event.id + '">' +
						'</form>')
						.appendTo($(document.body))
						.submit();
				}
				
				/*eventList = $('#calendar').fullCalendar('clientEvents');
				var index;
				for (index = 0; index < calendarData.length; ++index) {
					eventList[index].title = '';
					eventList[index].backgroundColor = '';
					$('#calendar').fullCalendar('updateEvent', eventList[index]);
				}
				
				event.backgroundColor = backgroundColorSelected;
				event.title = '>gewählt<'
				$('#calendar').fullCalendar('updateEvent', event);*/
			}
			
		}
	}
	
	var calendar = $('#calendar').fullCalendar($.extend(
		{
			theme: true,
			header: {
				left: 'prev,next, today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			allDaySlot: false,
			minTime: 8,
			maxTime: 19,
			defaultView: 'agendaWeek',
			hiddenDays: [0, 6],
			events: eventData,
			ignoreTimezone: true,
			eventTextColor: '#000000'
		}
		, localOptions
		, interactionOptions
	));
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
	
	var str=str.replace(/!termin!/g, document.getElementById("termin" + terminid).value);
	var str=str.replace(/!beginn!/g, document.getElementById("beginn" + terminid).value);
	var str=str.replace(/!ende!/g, document.getElementById("ende" + terminid).value);
	
	var str=str.replace(/!exp_name!/g, document.getElementById("exp_name").value);
	var str=str.replace(/!exp_ort!/g, document.getElementById("exp_ort").value.replace(/\n/g, '<br>'));
	
	var str=str.replace(/!vl_name!/g, document.getElementById("vl_name").value);
	var str=str.replace(/!vl_telefon!/g, document.getElementById("vl_tele").value);
	var str=str.replace(/!vl_email!/g, document.getElementById("vl_email").value);
	
	document.getElementById(targetDiv).innerHTML = unescape(str);
}
 
function sendmsg(terminid, msgBody, resultDiv) {
	expid = $.QueryString("expid");
	content = document.getElementById(msgBody).value.replace(/\n/g, '<br>');
	betreff = document.getElementById("betreff").value;
		
	$.get("service/mail.php?expid="+expid+"&termin="+terminid+"&content="+content+"&betreff="+betreff, 
	function(data){
		document.getElementById(resultDiv).innerHTML = "<br><font color=\"#FF0000\"><b>Nachricht versendet!<\/b><\/font>";
	}); 
}

function showEmailRecipients(expid, terminid) {
	$.get("service/mail.php?expid="+expid+"&termin="+terminid,
	function(data){
		document.getElementById("namelist").innerHTML = data;
	});
}

function terminchange() {
	expid = $.QueryString("expid");
	terminid = document.getElementById("termin").value;
	showEmailRecipients(expid, terminid);
	showEmailPreview('nachricht', terminid);
}
