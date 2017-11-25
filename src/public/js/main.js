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

	//check if today is weekend, if yes start calendar for next week
	var today = new Date();
	if(today.getDay() == 6) {
		today.setDate(today.getDate()+2);
	}
	else if(today.getDay() == 0) {
		today.setDate(today.getDate()+1);
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
			//hiddenDays: [0, 6],
			weekends: false,
			events: eventData,
			ignoreTimezone: true,
			eventTextColor: '#000000',
			year: today.getFullYear(),
			month: today.getMonth(),
			date: today.getDate()
		}
		, localOptions
		, interactionOptions
	));


}


