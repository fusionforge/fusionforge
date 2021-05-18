<?php
/**
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
 * Copyright 2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredtthauer - TrivialDev
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $group_id, $group, $HTML, $pluginTaskboard, $taskboard;

session_require_perm('tracker_admin', $group_id);

$taskboard->header(
	array(
		'title' => $taskboard->getName()._(': ')._('Administration - Trackers configuration'),
		'pagename' => _('Trackers configuration'),
		'sectionvals' => array($group->getPublicName()),
		'group' => $group_id
	)
);

$atf = $taskboard->TrackersAdapter->getArtifactTypeFactory();
if (!$atf || !is_object($atf) || $atf->isError()) {
	echo $HTML->error_msg(_('Could Not Get ArtifactTypeFactory'));
} else {
	$at_arr = $atf->getArtifactTypes();
	if ($at_arr === false || !count($at_arr) ) {
		echo $HTML->error_msg(_('There Are No Trackers Defined For This Project'));
	} else {

		$trackers = array();
		//select trackers, having resolution field
		for ($j = 0; $j < count($at_arr); $j++) {
			if (is_object($at_arr[$j]) && $at_arr[$j]->getID()) {
				$fields = $at_arr[$j]->getExtraFields();
				foreach( $fields as $field) {
					if( $field['alias'] == 'resolution' ) {
						$trackers[] = $at_arr[$j];
					}
				}
			}
		}

		$taskboard_id = $taskboard->getID();
		$taskboard_name = $taskboard->getName();
		$taskboard_description = $taskboard->getDescription();
		$release_field = $taskboard->getReleaseField();
		$release_field_tracker = $taskboard->getReleaseFieldTracker();
		$estimated_cost_field = $taskboard->getEstimatedCostField();
		$remaining_cost_field = $taskboard->getRemainingCostField();
		$user_stories_tracker = $taskboard->getUserStoriesTrackerID();
		$user_stories_reference_field = $taskboard->getUserStoriesReferenceField();
		$user_stories_sort_field = $taskboard->getUserStoriesSortField();
		$first_column_by_default = $taskboard->getFirstColumnByDefault();

		$trackers_selected = array();
		$trackers_bgcolor  = array();
		foreach( $taskboard->getUsedTrackersData() as $used_tracker_data ) {
			$trackers_selected[] = $used_tracker_data['group_artifact_id'];
			$trackers_bgcolor[ $used_tracker_data['group_artifact_id'] ] = $used_tracker_data['card_background_color'];
		}

		if (!empty($trackers)) {
			if (empty($trackers_selected)) {
				echo $HTML->warning_msg(_('Choose at least one tracker for using with Task Board.'));
			} else {
				echo html_e('div', array('id' => 'messages', 'class' => 'warning', 'style' => 'display: none;'), '', false);
			}
		} else {
			echo $HTML->error_msg(_('There are no any tracker having “resolution” field.'));
		}
		echo $HTML->openForm(array('action' => '/plugins/'.$pluginTaskboard->name.'/admin/?group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&action=trackers', 'method' => 'post'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'post_changes', 'value' => 'y'));
		echo html_e('input', array('type' => 'hidden', 'name' => 'taskboard_name', 'value'=>$taskboard_name));
		echo html_e('input', array('type' => 'hidden', 'name' => 'taskboard_description', 'value'=>$taskboard_description));
		echo $HTML->listTableTop();
		$cells = array();
		$tablearr = array(_('Tracker'), _('Description'), _('Use'), _('Card background color'));
		$content = $HTML->boxTop(_('Tasks trackers'));
		$content .= $HTML->listTableTop($tablearr, array(), 'sortable_table_tracker', 'sortable_table_tracker');
		foreach ($trackers as $tracker) {
			$tracker_id = $tracker->getID();
			$innercells = array();
			$innercells[][] = util_make_link('/tracker/?atid='.$tracker_id.'&group_id='.$group_id.'&func=browse', $HTML->getFollowPic().' &nbsp;'.$tracker->getName());
			$innercells[][] = $tracker->getDescription();
			$innercells[][] = '<input type="checkbox" name="use[]" value="'.$tracker_id.'" class="use_tracker" '.(in_array($tracker_id, $trackers_selected) ? 'checked="checked"' : '' ).'>';
			$innercells[][] = $taskboard->colorBgChooser('bg['.$tracker_id.']', (array_key_exists($tracker_id, $trackers_bgcolor) ? $trackers_bgcolor[$tracker_id] : NULL ));
			$content .= $HTML->multiTableRow(array('class' => 'middle'), $innercells);
		}
		$content .= $HTML->listTableBottom();
		$content .= $HTML->listTableTop();
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('Estimated effort field'));
		$innercells[][] = html_e('select', array('name' => 'estimated_cost_field'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('Remaining effort field'));
		$innercells[][] = html_e('select', array('name' => 'remaining_cost_field'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$content .= $HTML->listTableBottom();
		$content .= $HTML->boxBottom();
		$cells[] = array($content, 'class' => 'halfwidth');
		$content = $HTML->boxTop(_('User stories tracker'));
		$content .= $HTML->listTableTop();
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('User stories tracker'));
		$innercells[][] = html_e('select', array('name' => 'user_stories_tracker'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('User stories reference field')).'&nbsp;'.html_e('span', array('id' => 'usrefreq', 'style' => (!intval($user_stories_tracker)) ? 'display: none;' : '' ), utils_requiredField(), false);
		$innercells[][] = html_e('select', array('name' => 'user_stories_reference_field'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('User stories sorting field'));
		$innercells[][] = html_e('select', array('name' => 'user_stories_sort_field'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$content .= $HTML->listTableBottom();
		$content .= $HTML->boxBottom();
		$content .= $HTML->boxTop(_('General parameters'));
		$content .= $HTML->listTableTop();
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('Use first column by default'));
		$firstColumnByDefaultAttr = array('name' => 'first_column_by_default', 'type' => 'checkbox', 'value' => 1);
		($first_column_by_default ? $firstColumnByDefaultAttr['checked'] = 'checked' : '');
		$innercells[][] = html_e('input', $firstColumnByDefaultAttr);
		$content .= $HTML->multiTableRow(array(), $innercells);
		$content .= $HTML->listTableBottom();
		$content .= $HTML->boxMiddle(_('Releases management'));
		$content .= $HTML->listTableTop();
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('Manage releases'));
		$releaseTrackerInputAttr1 = array('type' => 'radio', 'name' => 'release_field_tracker', 'value' => 1, 'id' => 'release_tracker1');
		$releaseTrackerInputAttr2 = array('type' => 'radio', 'name' => 'release_field_tracker', 'value' => 2, 'id' => 'release_tracker2');
		($release_field_tracker == 2 ? $releaseTrackerInputAttr2['checked'] = 'checked' : $releaseTrackerInputAttr1['checked'] = 'checked');

		$innercells[][] = html_e('label', array('for' => 'release_tracker1'), html_e('input', $releaseTrackerInputAttr1).'&nbsp;'._('by tasks')).
				html_e('br').
				html_e('label', array('for' => 'release_tracker2'), html_e('input', $releaseTrackerInputAttr2).'&nbsp;'._('by user stories'));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$innercells = array();
		$innercells[][] = html_e('strong', array(), _('Release field'));
		$innercells[][] = html_e('select', array('name' => 'release_field'), html_e('option', array('value' => ''), _('Not defined')));
		$content .= $HTML->multiTableRow(array(), $innercells);
		$content .= $HTML->listTableBottom();
		$content .= $HTML->boxBottom();
		$cells[] = array($content, 'class' => 'halfwidth');
		echo $HTML->multiTableRow(array('class' => 'top'), $cells);
		echo $HTML->listTableBottom();
		echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'post_changes', 'value' => _('Submit'))));
		echo $HTML->addRequiredFieldsInfoBox();
		echo $HTML->closeForm();
		echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var all_trackers = new Array();
<?php
		foreach( $at_arr as $tracker ) {
			echo  'all_trackers.push({ id: "'.$tracker->getID().'", name: "'.$tracker->getName().'", desc: "'.$tracker->getDescription().'" });'."\n";
		}
?>

jQuery(function($){
	var user_story_ref_field ='';

	function loadUserStoriesTrackers() {
		var selected = $('select[name=user_stories_tracker] option:selected').val();
		if( !selected ) {
			selected = '<?php echo $user_stories_tracker ?>';
		}

		var str = '<option value=""><?php echo _('Not defined'); ?></option>';
		$.each(all_trackers, function(key, value) {
			if( !$('input.use_tracker[value=' + value.id + ']').is(':checked') ) {
				str +='<option value="'+ value.id +'"'+ ( value.id == selected ? 'selected' : '' ) +'>'+ value.name +'</option>';
			}
		});
		$('select[name=user_stories_tracker]').empty().html(str);

		loadUserStorySortFields();
	}

	function loadUserStorySortFields() {
		$.ajax({
			type: 'POST',
			url: '<?php echo util_make_url('/plugins/'.$pluginTaskboard->name.'/admin/ajax.php'); ?>',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id     : <?php echo $group_id ?>,
				taskboard_id : <?php echo $taskboard_id ?>,
				'trackers[]' : [ $('select[name=user_stories_tracker]').val() ]
			},
			async: false
		}).done(function( answer ) {
			if(answer['message']) {
				showMessage(answer['message'], 'error');
			}

			if( answer['common_selects'] || answer['common_texts'] ) {
				var selected = $('select[name=user_stories_sort_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $user_stories_sort_field ?>';
				}

				var str = '<option value=""><?php echo _('Not defined'); ?></option>';
				if( answer['common_selects'] ) {
					$.each(answer['common_selects'], function(key, value) {
						str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
					});
				}

				if( answer['common_texts'] ) {
					$.each(answer['common_texts'], function(key, value) {
						str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
					});
				}

				$('select[name=user_stories_sort_field]').empty().html(str);
			}
		});
	}

	function loadReleaseField() {
		var release_trackers = new Array();

		if( $('input[type=radio][name=release_field_tracker]:checked').val() == 1 ) {
			// load release from task trackers
			$('input.use_tracker').each( function () {
				if ( $(this).is(':checked') ) {
					release_trackers.push( $(this).attr('value') );
				}
			});
		} else {
			// load field user story tracker
			release_trackers = [ $('select[name=user_stories_tracker]').val() ];
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo util_make_url('/plugins/'.$pluginTaskboard->name.'/admin/ajax.php'); ?>',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id     : <?php echo $group_id ?>,
				taskboard_id : <?php echo $taskboard_id ?>,
				'trackers[]' : release_trackers
			},
			async: false
		}).done(function( answer ) {
			if(answer['message']) {
				showMessage(answer['message'], 'error');
			}

			if( answer['common_selects'] ) {
				var selected = $('select[name=release_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $release_field ?>';
				}

				var str = '<option value=""><?php echo _('Not defined'); ?></option>';
				if( answer['common_selects'] ) {
					$.each(answer['common_selects'], function(key, value) {
						str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
					});
				}

				$('select[name=release_field]').empty().html(str);
			}
		});
	}

	function loadTrackersFields() {
		var trackers = new Array();
		$('input.use_tracker').each( function () {
			if ( $(this).is(':checked') ) {
				trackers.push( $(this).attr('value') );
			}
		});

		if( trackers.length == 0 ) {
			showMessage("<?php echo _('Choose at least one tracker for using with Task Board.'); ?>", "warning");
		}

		$.ajax({
			type: 'POST',
			url: '<?php echo util_make_url('/plugins/'.$pluginTaskboard->name.'/admin/ajax.php'); ?>',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id : <?php echo $group_id ?>,
				taskboard_id : <?php echo $taskboard_id ?>,
				'trackers[]' : trackers
			},
			async: false
		}).done(function( answer ) {
			if(answer['message']) {
				showMessage(answer['message'], 'error');
			}

			if( answer['common_selects'] ) {
				var selected = $('select[name=release_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $release_field ?>';
				}

				var str = '<option value=""><?php echo _('Not defined') ?></option>';
				$.each(answer['common_selects'], function(key, value)
				{
					str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
				});
				$('select[name=release_field]').empty().html(str);
			}

			if( answer['common_texts'] ) {
				var selected = $('select[name=estimated_cost_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $estimated_cost_field ?>';
				}

				var str = '<option value=""><?php echo _('Not defined') ?></option>';
				$.each(answer['common_texts'], function(key, value) {
					str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
				});
				$('select[name=estimated_cost_field]').empty().html(str);
			}

			if( answer['common_texts'] ) {
				var selected = $('select[name=remaining_cost_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $remaining_cost_field ?>';
				}

				var str = '<option value=""><?php echo _('Not defined') ?></option>';
				$.each(answer['common_texts'], function(key, value) {
					str +='<option value="'+ key +'"'+ ( key == selected ? 'selected' : '' ) +'>'+ value +'</option>';
				});
				$('select[name=remaining_cost_field]').empty().html(str);
			}

			if( answer['common_refs'] ) {
				var selected = $('select[name=user_stories_reference_field] option:selected').val();
				if( !selected ) {
					selected = '<?php echo $user_stories_reference_field ?>';
				}

				user_story_ref_field = '<option value=""><?php echo _('Not defined') ?></option>';
				$.each(answer['common_refs'], function(key, value) {
					user_story_ref_field +='<option value="'+ key +'"'+ ( ( (key == selected) || (!selected && key=='user_story') ) ? 'selected' : '' ) +'>'+ value +'</option>';
				});

				if( $('select[name=user_stories_tracker]').val() ) {
					$('select[name=user_stories_reference_field]').empty().html(user_story_ref_field);
				} else {
					$('select[name=user_stories_reference_field]').empty().html('<option value=""><?php echo _('Not defined') ?></option>');
				}
			}

			loadUserStoriesTrackers();
		});
	}

	$('input.use_tracker').click( function () {
		cleanMessages();
		loadTrackersFields();
	});

	$('select[name=user_stories_tracker]').change( function () {
		cleanMessages();
		if( $(this).val() ) {
			$('#usrefreq').show();
			$('select[name=user_stories_reference_field]').empty().html(user_story_ref_field);
			$('#release_tracker2').prop("disabled", false);
			loadUserStorySortFields();
		} else {
			$('#usrefreq').hide();
			$('select[name=user_stories_reference_field]').empty().html('<option value=""><?php echo _('Not defined') ?></option>');
			$('#release_tracker1').prop("checked", true);
			$('#release_tracker2').prop("disabled", true);
		}
	});

	$('input[type=radio][name=release_field_tracker]').change( function () {
		loadReleaseField();
	});

	loadUserStoriesTrackers();
	loadTrackersFields();
	loadReleaseField();
});
//]]>
<?php
		echo html_ac(html_ap() - 1);
	}
}
