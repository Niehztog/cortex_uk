function initCalendar(eventData, purpose) {
	
	var backgroundColorDefault = '#D2DBF8';
	var backgroundColorHover = '#6C91F8';
	var backgroundColorSelected = '#FFB2B2';

	var interactionOptions = {};
	
	if('manage' == purpose) {
		interactionOptions = {
			editable: true,
			selectable: true,
			selectHelper: false,
			select: function(start, end, jsEvent, view) {
				calendar.fullCalendar(
					'renderEvent',
					{
						title: '',
						start: start,
						end: end
					},
					true // make the event "stick"
				);
				calendar.fullCalendar('unselect');
			},
	
			eventClick: function(event, jsEvent, view) {
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
			
			eventRender: function(event, element, view) {
				if(event.title != '') {
					var datum = event.start.format('dddd, [den] d.M.YYYY');
					var uhrzeit_von = event.start.format('H:mm');
					var uhrzeit_bis = event.end.format('H:mm');
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
				
			eventClick: function(event, jsEvent, view) {
				var datum = event.start.format('dddd, [den] d.M.YYYY');
				var uhrzeit_von = event.start.format('H(:mm)');
				var uhrzeit_bis = event.end.format('H(:mm)');
				
				var decision = confirm('Für die Teilnahme am ' + datum + ' von ' + uhrzeit_von + ' bis ' + uhrzeit_bis + ' Uhr anmelden?');
				
				if(decision) {
					$('<form action="'+window.location+'" method="POST">' +
						'<input type="hidden" name="chosendate" value="1">' +
						'<input type="hidden" name="id" value="' + event.id + '">' +
						'</form>')
						.appendTo($(document.body))
						.submit();
				}
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
			minTime: '8:00:00',
			maxTime: '19:00:00',
			defaultView: 'agendaWeek',
			weekends: false,
			events: eventData,
            timezone: false,
			eventTextColor: '#000000',
			defaultDate: today.toISOString(),
			lang: 'de',
            height: 'auto'
		}
		, interactionOptions
	));


}


