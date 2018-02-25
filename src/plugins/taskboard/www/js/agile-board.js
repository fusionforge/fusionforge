/**
 * FusionForge Documentation Manager
 *
 * Previous Copyright to FusionForge Team
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * Copyright 2016-2017, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

var aTaskboardUserStories = [];

function cleanMessages() {
	jQuery('#messages').html('').hide();
}

function showMessage( msg_text, msg_class) {
	jQuery('#messages').removeClass().addClass( msg_class ).html( msg_text ).show();
}

function loadTaskboard( group_id ) {
	var assigned_to = jQuery('select[name="_assigned_to"]').val();
	var release_val = jQuery('select[name="_release"]').val();
	var data = {
			action   : 'load_taskboard',
			group_id : group_id,
			taskboard_id : gTaskboardId,
			assigned_to : assigned_to,
			release : release_val
		};

	if( release_val && gReleases[release_val] ) {
		jQuery('#taskboard-release-id').val( gReleases[release_val].id );
		jQuery('#taskboard-release-description').html(
				gReleases[release_val].startDate + ' - ' + gReleases[release_val].endDate
		);
		jQuery('#taskboard-release-snapshot').show();
		jQuery( "input[name='snapshot_date']" ).datepicker( {
			"dateFormat" : "yy-mm-dd",
			"minDate" : gReleases[release_val].startDate,
			"maxDate" : gReleases[release_val].endDate
		});
	} else {
		jQuery('#taskboard-release-id').val('');
		jQuery('#taskboard-release-description').html('');
		jQuery('#taskboard-release-snapshot').hide();
	}

	jQuery.ajax({
		type: 'POST',
		url: gAjaxUrl,
		dataType: 'json',
		data : data,
		async: false
	}).done(function( answer ) {
		jQuery('#agile-board tbody').html('');
		jQuery('#agile-board-progress').html( '' );

		if(answer['message']) {
			showMessage(answer['message'], 'error');
		}

		aUserStories = answer['user_stories'];
		aPhases = answer['phases'];

		if( aUserStories.length ) {
			jQuery( "#agile-board" ).append(
				drawUserStories()
			);

			jQuery( window ).resize( function() {
				jQuery('.agile-sticker').each( function( i, e) {
					jQuery(e).css('width', 'auto' );
				});
				fixCardSize();
			} );

			jQuery( ".agile-toolbar-add-task" ).click( function(e) {
				jQuery('#new-task-dialog input[name="user_story_id"]').val( jQuery(this).attr('user_story_id') );
				jQuery('#new-task-dialog').dialog('open');
				jQuery('.ui-widget-overlay').height( jQuery( document ).height() );
				e.preventDefault();
			});

			for(var i=0 ; i<aUserStories.length ; i++) {
				drawUserStory( aUserStories[i] );
			};

			drawBoardProgress();

			initUserStories();
			initEditable();
			fixCardSize();

			jQuery( ".agile-minimize-column" ).click( function() {
				var phase_id = jQuery(this).attr('phase_id');
				if( $(this).hasClass('minimized') ) {
					maximizeColumn(phase_id);
				} else {
					minimizeColumn(phase_id);
				}
				$(this).toggleClass('minimized');
			});
		} else {
			jQuery( "#agile-board" ).append(
					'<tr><td colspan="'  + aPhases.length + '" style="text-align: center;">' + gMessages['notasks'] + '</td></tr>'
				);
		}

		if ($('#filter-tasks-chk').is(':checked')) {
			applyTaskFilter();
		}
		if ($('#hide-unlinked-task-chk').is(':checked')) {
			hideUnlinkedTasks();
		}
	});
}

function fixCardSize () {
	// set fixed with in pixels for preventing bad view when d'n'd
	jQuery('.agile-sticker').each( function( i, e) {
		jQuery(e).css('width',jQuery(e).width() );
	});
}

function drawBoardProgress() {
	var start= bShowUserStories ? 1 : 0;

	var totalTasks = 0;
	var totalCostEstimated = 0;
	var totalCostRemaining = 0;

	var lastPhaseWithTasks = 0;
	for( var j=start; j<aPhases.length; j++) {
		aPhases[j].progressTasks = 0;
		aPhases[j].progressCost = 0;
		for( var i=0; i<aUserStories.length; i++ ) {
			for( var t=0; t<aUserStories[i].tasks.length; t++ ) {
				if( taskInPhase( aUserStories[i].tasks[t], aPhases[j].id ) ) {
					aPhases[j].progressTasks ++;
					totalTasks ++;

					if( aUserStories[i].tasks[t].estimated_dev_effort ) {
						totalCostEstimated += parseFloat( aUserStories[i].tasks[t].estimated_dev_effort );
					}

					if( aUserStories[i].tasks[t].remaining_dev_effort ) {
						totalCostRemaining += parseFloat( aUserStories[i].tasks[t].remaining_dev_effort );
					}

					lastPhaseWithTasks = j;
				}
			}
		}
	}

	var html = '<table>';
	html += '<tr><td style="padding: 0; width: '+ parseInt( 100 / aPhases.length ) +'%">' + gMessages.progressByTasks + ':</td><td style="padding: 0;">';

	var buf = 0;
	for( var j=start; j<aPhases.length; j++) {
		if( aPhases[j].progressTasks ) {
			var wt = parseInt(  parseInt( aPhases[j].progressTasks ) / totalTasks * 100 );
			if( j == lastPhaseWithTasks ) {
				// to avoid bad presentation when sum of rounded percemts less then 100
				wt = 100 - buf;
			}
			buf += wt;

			var back = '';
			if( aPhases[j].titlebackground ) {
				back = 'background-color:' + aPhases[j].titlebackground;
			}
			html += '<div class="agile-board-progress-bar" style="width: ' + wt + '%; ' + back + '" ' + 'title="' + aPhases[j].title + '">' +
				aPhases[j].progressTasks +
				'</div>';
		}
	}

	html += '</td></tr><table>';

	if( parseFloat(totalCostEstimated) > 0 ) {
		var totalCostCompleted = totalCostEstimated - totalCostRemaining;
		var wt = parseInt( totalCostCompleted/totalCostEstimated * 100);
		// show progress by cost
		html += '<table>';
		html += '<tr><td style="padding: 0; width: '+ parseInt( 100 / aPhases.length ) +'">' + gMessages.progressByCost + ':</td><td style="padding: 0;">';
		html += '<div class="agile-board-progress-bar-done" style="width: ' + wt + '%;" title="' + gMessages['completedCost'] + '">' + totalCostCompleted + '</div>';
		html += '<div class="agile-board-progress-bar-remains" style="width: ' + ( 100 - wt ) + '%;" title="' + gMessages['remainingCost'] + '">' + totalCostRemaining + '</div>';
		html += '</td></tr><table>';
	}

	jQuery('#agile-board-progress').html( html );
}

function drawUserStories() {
	var l_sHtml = '';

	for( var i=0; i<aUserStories.length; i++ ) {
		var start=0;
		var us=aUserStories[i];
		evenOdd = 'even';
		if (i % 2) {
			evenOdd = 'odd';
		}
		l_sHtml += "<tr class='agile-user-story-"+ us.id +" "+evenOdd+"' class='top'>\n";
		if( bShowUserStories ) {
			start=1;
			l_sHtml += '<td class="agile-phase agile-user-stories">';
			l_sHtml += '<div class="agile-sticker-container">';
			l_sHtml += '<div class="agile-sticker agile-sticker-user-story" id="user-story-' + us.id + '">';
			l_sHtml += '<div class="agile-sticker-header">';
			if( us.id==0) {
				l_sHtml += '<a>[#' + us.id + ']</a>';
			} else {
				l_sHtml += '<a href="' + us.url + '" target="_blank">[#' + us.id + ']</a>';
			}
			l_sHtml += '<div class="agile-toolbar-minimize minimized"></div>\n';
			l_sHtml += '<div class="agile-toolbar-add-task" user_story_id="' +us.id+ '"></div>\n';
			l_sHtml += '</div>\n';
			l_sHtml += '<div class="agile-sticker-body">'
			l_sHtml += '<div class="agile-sticker-name">' + us.title + "</div>\n";
			l_sHtml += '<div class="agile-sticker-description">' + us.description + "</div>\n";
			l_sHtml += "</div>\n"
			l_sHtml += "</div>\n";
			l_sHtml += "</div>\n";
			l_sHtml += "</td>\n";
		}

		for( var j=start; j<aPhases.length; j++) {
			var ph=aPhases[j];
			var style = '';
			if( ph.background ) {
				style = ' style="background-color:' + ph.background + ';"';
			}
			l_sHtml += '<td id="' + ph.id + '-' + us.id + '" class="agile-phase agile-tasks agile-phase-' + ph.id + '"' + style + '>';
			l_sHtml += "</td>\n";

			// initialize progress counters
			aPhases[j].progressTasks = 0;
			aPhases[j].progressCost = 0;
		}

		l_sHtml += "</tr>\n";
	}

	return l_sHtml;
}

function helperTaskStart ( event, ui ) {
	jQuery(this).css('opacity', 0.5);
}

function helperTaskStop ( event, ui ) {
	jQuery(this).css('opacity', 1);
}

function helperTaskDrop ( event, ui ) {
	var d = jQuery( ui.draggable );
	if( d.length ) {
		var l_nUserStoryId = d.data('user_story_id');
		var l_nTaskId      = d.data('task_id');
		var l_nTargetPhaseId     = jQuery(this).data('phase_id');

		setPhase( l_nUserStoryId, l_nTaskId, l_nTargetPhaseId );
	}
}

function setPhase( nUserStoryId, nTaskId, nTargetPhaseId ) {
	var l_oUserStory;
	var l_oTargetPhase;
	var l_nSourcePhaseId;

	for( var i=0; i<aPhases.length ; i++ ) {
		if( aPhases[i].id == nTargetPhaseId ) {
			l_oTargetPhase = aPhases[i];
		}
	}

	for( var i=0; i<aUserStories.length ; i++ ) {
		if( aUserStories[i].id == nUserStoryId ) {
			l_oUserStory = aUserStories[i];
			for( var j=0; j<aUserStories[i].tasks.length ; j++ ) {
				if( aUserStories[i].tasks[j].id == nTaskId ) {
					l_nSourcePhaseId = aUserStories[i].tasks[j].phase_id;

					if( l_oTargetPhase && l_nSourcePhaseId != nTargetPhaseId ) {
							// try to drop card
							jQuery.ajax({
								type: 'POST',
								url: gAjaxUrl,
								dataType: 'json',
								data : {
									action   : 'drop_card',
									group_id : gGroupId,
									taskboard_id : gTaskboardId,
									task_id : nTaskId,
									target_phase_id : nTargetPhaseId
								},
								async: false
							}).done(function( answer ) {
								if(answer['message']) {
									showMessage(answer['message'], 'error');
								}

								if(answer['alert']) {
									alert( answer['alert'] );
								}

								if(answer['action'] == 'reload') {
									// reload whole board
									loadTaskboard( gGroupId );
								} else {
									if( answer['task'] ) {
										// change particular task data
										aUserStories[i].tasks[j] = answer['task'];
									}

									if( l_oUserStory ) {
										drawUserStory(l_oUserStory);
									}

									fixCardSize();

									jQuery('#agile-board-progress').html( '' );
									drawBoardProgress();

									initEditable();
								}
							});
					}
				}
			}
		}
	}
}

function initUserStories() {
	for( var i=0; i<aUserStories.length; i++ ) {
		initUserStory( aUserStories[i] );
	}
	jQuery( ".agile-toolbar-minimize" ).click( function() {
		if( $(this).hasClass('minimized') ) {
			$(this).parent().parent().css('height','auto').find('.agile-sticker-description').show();
		} else {
			$(this).parent().parent().css('height','80px').find('.agile-sticker-description').hide();
		}
		$(this).toggleClass('minimized');
	});
}

function initUserStory( oUserStory ) {
	for( var i=0; i<aPhases.length ; i++ ) {
		if( aPhases[i].id != 'user-stories') {
			var sPhaseId = "#" + aPhases[i].id + "-" + oUserStory.id;

			//make phase droppable
			if( gIsTechnician ) {
				jQuery( sPhaseId )
					.data('phase_id', aPhases[i].id)
					.droppable( {
						accept: '.agile-sticker-task-' + oUserStory.id,
						hoverClass: 'agile-phase-hovered',
						drop: helperTaskDrop
					} );
			}

			if( aPhases[i].background ) {
				jQuery("#" + aPhases[i].id + "-" + oUserStory.id).css('background-color', aPhases[i].background );
			}
		}
	}

	jQuery('#user-story-' + oUserStory.id).data('user_story_id', oUserStory.id);
}

function drawUserStory( oUserStory ) {

	for( var i=0; i<aPhases.length ; i++ ) {
		if( aPhases[i].id != 'user-stories') {
			var sPhaseId = "#" + aPhases[i].id + "-" + oUserStory.id;
			jQuery( sPhaseId ).html(
					drawTasks( oUserStory, aPhases[i].id )
			);
		}
	}

	// only technician can move tasks
	if( gIsTechnician ) {
		for(var j=0 ; j<oUserStory.tasks.length ; j++) {
			jQuery('#task-' + oUserStory.tasks[j].id)
				.data('task_id', oUserStory.tasks[j].id)
				.data('user_story_id', oUserStory.id)
				.draggable( {
					containment: '#agile-board',
					cursor: 'move',
					stack: 'div',
					revert: true,
					start: helperTaskStart,
					stop: helperTaskStop,
					helper: "clone",
					distance: 10
				} );
		}
	}
}

function drawTasks( oUserStory, sPhaseId ) {
	var l_sHtml = '' ;

	for( var i=0; i<oUserStory.tasks.length; i++ ) {
		tsk = oUserStory.tasks[i];
		if( taskInPhase( tsk, sPhaseId ) ) {
			l_sHtml += '<div class="agile-sticker-container">';
			l_sHtml += '<div class="agile-sticker agile-sticker-task agile-sticker-task-' + tsk.user_story + '" id="task-' + tsk.id + '" >';
			l_sHtml += '<div class="agile-sticker-header" style="background-color: ' + tsk.background + ';">';
			l_sHtml += '<a href="' + tsk.url  +  '" target="_blank">[#' + tsk.id + ']</a>';
			l_sHtml += '<div class="agile-toolbar-minimize minimized"></div>\n';
			l_sHtml += "</div>\n";
			l_sHtml += '<div class="agile-sticker-body">';
			l_sHtml += '<div class="agile-sticker-name">' + tsk.title +'</div>';
			l_sHtml += '<div class="agile-sticker-description">' + tsk.description +'</div>';
			if( tsk.assigned_to_face ) {
				l_sHtml += '<div class="agile-sticker-assignee-face">' + tsk.assigned_to_face + '</div>';
			}
			l_sHtml += '</div>';
			l_sHtml += '<div class="agile-sticker-footer">';
			if( tsk.assigned_to != "Nobody" ) {
				l_sHtml += '<div class="agile-sticker-assignee">Assigned: ' + tsk.assigned_to + '</div>';
			} else {
				l_sHtml += '<div class="agile-sticker-assignee">Unassigned</div>';
			}
			if( tsk.estimated_dev_effort ) {
				l_sHtml += '<div class="agile-sticker-effort">' + tsk.remaining_dev_effort + '/' + tsk.estimated_dev_effort + '</div>';
			}
			l_sHtml += '</div>';
			l_sHtml += "</div></div>\n";
		}
	}
	l_sHtml += '<div class="agile-phase-' + sPhaseId + '-more agile-phase-more">...</div>'
	return l_sHtml;
}

function applyTaskFilter() {
	var sFilter = $('#task-filter')[0].value;
	var bOnlyOnName = $('#filter-only-on-name-chk').is(':checked');

	if ($('#task-filter')[0].checkValidity()) {
		$('td.agile-tasks div.agile-sticker-container').each( function(nb,container) {
			sTaskName = $(container).find('div.agile-sticker-name').text();
			if (bOnlyOnName) {
				if (sTaskName.match(sFilter)) {
					$(container).removeClass('filtred');
				} else {
					$(container).addClass('filtred');
				}
			} else {
				$TaskDescription = $(container).find('div.agile-sticker-description').text();
				if (sTaskName.match(sFilter) || $TaskDescription.match(sFilter)) {
					$(container).removeClass('filtred');
				} else {
					$(container).addClass('filtred');
				}
			}
		});
	}
}

function removeTaskFilter () {
	$('td.agile-tasks div.agile-sticker-container').removeClass('filtred');
}

function checkTaskFilter () {
	var bReturn = true;
	try {
		new RegExp($('#task-filter')[0].value);
	} catch(e) {
		$('#task-filter')[0].setCustomValidity(gMessages.invalidRegEx);
		bReturn = false;
	}
	if (bReturn) {
		$('#task-filter')[0].setCustomValidity("");
	}
	return bReturn
}

function hideUnlinkedTasks() {
	$('tr.agile-user-story-0').hide();
}

function showUnlinkedTasks() {
	$('tr.agile-user-story-0').show();
}

function minimizeColumn(phase_id) {
	$('table#agile-board td.agile-phase-'+phase_id).addClass('minimized');
}

function maximizeColumn(phase_id) {
	$('table#agile-board td.agile-phase-'+phase_id).removeClass('minimized');
}

function taskInPhase( tsk, phase ) {
	if( tsk.phase_id ==  phase) {
		return true;
	}

	for( var i=0; i<aPhases.length; i++) {
		if( aPhases[i].id == phase && aPhases[i].resolutions ) {
			for( var j=0; j<aPhases[i].resolutions.length; j++) {
				if( tsk.resolution == aPhases[i].resolutions[j] ) {
					return true;
				}
			}
		}
	}

	return false;
}

function initEditable() {
	// only tracker manager can modify tasks
	if( !gIsManager ) {
		return;
	}

	// description
	jQuery("td.agile-tasks div.agile-sticker-name").dblclick( function () {
		if( jQuery(this).children('textarea').length == 0 ) {
			// close open textarea
			jQuery('#text_title').trigger('focusout');


			var l_oTitle = jQuery(this);
			var l_sTitle = l_oTitle.html();
			var l_nTaskId = jQuery(this).parent().parent().data('task_id');
			jQuery(this).html( '<textarea id="text_title" name="title" rows="11"></textarea>');


			jQuery('#text_title')
				.html( l_sTitle.replace(/<br>/g, "\n") )
				.focus()
				.focusout(function() {
					l_oTitle.html( l_sTitle );
				}) ;
			jQuery('#text_title').keydown(function(e) {
				if( e.keyCode == 27 ) {
					// ESC == cancel
					l_oTitle.html( l_sTitle );
					e.preventDefault();
				} else if ( e.keyCode == 13 && !e.shiftKey) {
					e.preventDefault();
					// ENTER - submit
					var textField = this;
					jQuery.ajax({
						type: 'POST',
						url: gAjaxUrl,
						dataType: 'json',
						data : {
							action   : 'update',
							group_id : gGroupId,
							taskboard_id : gTaskboardId,
							task_id : l_nTaskId,
							title : jQuery(this).val()
						},
						async: true
					}).done(function( answer ) {
						if(answer['message']) {
							showMessage(answer['message'], 'error');
						}

						if(answer['action'] == 'reload') {
							// reload whole board
							loadTaskboard( gGroupId );
						}

						l_oTitle.html(jQuery(textField).val().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>') );
					}).fail(function( jqxhr, textStatus, error ) {
						var err = textStatus + ', ' + error;
						alert(err);
					});
				}
			});
		}
	});
}

