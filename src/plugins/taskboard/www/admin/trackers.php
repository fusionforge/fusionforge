<?php

/*
 * Copyright (C) 2013 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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

$taskboard->header(
	array(
		'title'=>'Taskboard for '.$group->getPublicName().' : Administration : Trackers configuration' ,
		'pagename'=>_('Trackers confoguration'),
		'sectionvals'=>array(group_getname($group_id)),
		'group'=>$group_id
	)
);

global $group_id, $HTML;

$atf = $taskboard->TrackersAdapter->getArtifactTypeFactory();
if (!$atf || !is_object($atf) || $atf->isError()) {
	exit_error(_('Could Not Get ArtifactTypeFactory'));
}

$at_arr = $atf->getArtifactTypes();
if ($at_arr === false || !count($at_arr) ) {
	exit_error(_('There Are No Trackers Defined For This Project'));
}

$trackers_selected = array();
$trackers_bgcolor  = array();
$release_field  = '';
$release_field_tracker  = 1;
$estimated_cost_field = $plugins_taskboard_estimated_cost_field_init;
$remaining_cost_field = $plugins_taskboard_remaining_cost_field_init;
$user_stories_tracker = '';
$user_stories_reference_field = '';
$user_stories_sort_field = '';
$first_column_by_default = 1;

$tracker = array();
//select trackers, having resolution field
for ($j = 0; $j < count($at_arr); $j++) {
	if (is_object($at_arr[$j])) {
		if( $at_arr[$j]->getID() )
	
		$fields = $at_arr[$j]->getExtraFields();
		foreach( $fields as $field) {
			if( $field['alias'] == 'resolution' ) {
				$trackers[] = $at_arr[$j];
			}
		}
	}
}


if( $taskboard->getID() ) {
	foreach( $taskboard->getUsedTrackersData() as $used_tracker_data ) {
		$trackers_selected[] = $used_tracker_data['group_artifact_id'];
		$trackers_bgcolor[ $used_tracker_data['group_artifact_id'] ] = $used_tracker_data['card_background_color'];
		$release_field = $taskboard->getReleaseField();
		$release_field_tracker = $taskboard->getReleaseFieldTracker();
		$estimated_cost_field = $taskboard->getEstimatedCostField();
		$remaining_cost_field = $taskboard->getRemainingCostField();
		$user_stories_tracker = $taskboard->getUserStoriesTrackerID();
		$user_stories_reference_field = $taskboard->getUserStoriesReferenceField();
		$user_stories_sort_field = $taskboard->getUserStoriesSortField();
		$first_column_by_default = $taskboard->getFirstColumnByDefault();
	}
	
}

if (getStringFromRequest('post_changes')) {
	$trackers_selected = getArrayFromRequest('use', array());
	$trackers_bgcolor  = getArrayFromRequest('bg', array());
	$release_field = getStringFromRequest('release_field','');
	$release_field_tracker = getIntFromRequest('release_field_tracker',1);
	$estimated_cost_field = getStringFromRequest('estimated_cost_field','');
	$remaining_cost_field = getStringFromRequest('remaining_cost_field','');
	$user_stories_tracker = getStringFromRequest('user_stories_tracker','');
	$user_stories_reference_field = getStringFromRequest('user_stories_reference_field','');
	$user_stories_sort_field = getStringFromRequest('user_stories_sort_field','');
	$first_column_by_default = getIntFromRequest('first_column_by_default','0');

	// try to save data
	if( $taskboard->getID() ) {
		$ret = $taskboard->update( $trackers_selected, $trackers_bgcolor, $release_field, $release_field_tracker, $estimated_cost_field, $remaining_cost_field, $user_stories_tracker, $user_stories_reference_field, $user_stories_sort_field, $first_column_by_default);
	} else {
		$ret = $taskboard->create( $trackers_selected, $trackers_bgcolor, $release_field, $release_field_tracker, $estimated_cost_field, $remaining_cost_field, $user_stories_tracker, $user_stories_reference_field, $user_stories_sort_field, $first_column_by_default);
	}

	if( !$ret ) {
		exit_error( $taskboard->getErrorMessage() );
	}
}

if( count($at_arr) > 0 ) {
	if( count($trackers_selected) == 0 ) {
		echo '<div id="messages" class="warning">'._('Choose at least one tracker for using with taskboard.').'</div>';
	} else {
		echo '<div id="messages" class="warning" style="display: none;"></div>';
	}
} else {
	echo '<div id="messages" class="error">'._('There are no any tracker having "resolution" field.').'</div>';
}

?>

<script type="text/javascript" src="/plugins/taskboard/js/agile-board.js"></script>
<form action="/plugins/taskboard/admin/?group_id=<?php echo $group_id ?>&amp;action=trackers" method="post">
<input type="hidden" name="post_changes" value="y">

<table cellspacing="2" cellpadding="2" width="100%">
<tr valign="top">
	<td with="50%">
<?php

echo $HTML->boxTop(_("Tasks trackers"));

if(  count($at_arr) > 0 ) {
	$tablearr = array(_('Tracker'),_('Description'),_('Use'),_('Card background color'));

	
	echo $HTML->listTableTop($tablearr, false, 'sortable_table_tracker', 'sortable_table_tracker');
	foreach( $trackers as $tracker ) {
		$tracker_id = $tracker->getID();
		echo '
			<tr valign="middle">
				<td><a href="'.util_make_url ('/tracker/?atid='.$tracker_id.'&amp;group_id='.$group_id.'&amp;func=browse').'">'.
					html_image("ic/tracker20w.png","20","20").' &nbsp;'.
					$tracker->getName() .'</a>
				</td>
				<td>' .  $tracker->getDescription() .'
				</td>
				<td><input type="checkbox" name="use[]" value="'.$tracker_id.'" class="use_tracker" '.
					(in_array($tracker_id, $trackers_selected) ? 'checked' : '' ).'></td>
				<td>'. $taskboard->colorBgChooser( 
					'bg['.$tracker_id.']',
					( array_key_exists($tracker_id, $trackers_bgcolor) ? $trackers_bgcolor[$tracker_id]  : NULL  ) 
				) .'</td>
			</tr>';
	}
	echo $HTML->listTableBottom();

?>
<table>
	<tr><td><strong><?php echo _('Estimated cost field') ?></strong></td><td><select name="estimated_cost_field"><option option value=""><?php echo _('Not defined') ?></option></select></td></tr>
	<tr><td><strong><?php echo _('Remaining cost field') ?></strong></td><td><select name="remaining_cost_field"><option option value=""><?php echo _('Not defined') ?></option></select></td></tr>
</table>



	</td>
	<td width="50%">

<?php echo $HTML->boxTop(_("User story tracker")); ?>
	
<table>
	<tr><td><strong><?php echo _('User stories tracker') ?></strong></td><td><select name="user_stories_tracker"><option value=""><?php echo _('Not defined') ?></option></select></td></tr>
	<tr><td><strong><?php echo _('User stories reference field') ?></strong>&nbsp;<span id='usrefreq' <?php if( !$user_stories_tracker) { ?> style='display: none;' <?php } ?> ><?php echo utils_requiredField(); ?></span></td><td><select name="user_stories_reference_field"><option value=""><?php echo _('Not defined') ?></option></select></td></tr>
	<tr><td><strong><?php echo _('User stories sorting field') ?></strong></td><td><select name="user_stories_sort_field"><option value=""><?php echo _('Not defined') ?></option></select></td></tr>
</table>

<?php echo $HTML->boxBottom(); ?>

<?php echo $HTML->boxTop(_("General parameters")); ?>
<table>
	<tr><td><strong><?php echo _('Use first column by default') ?></strong></td><td><input name="first_column_by_default" type="checkbox" <?php echo ($first_column_by_default? 'checked' : '')  ?> value="1"></td></tr>
</table>

<?php echo $HTML->boxMiddle(_("Release/sprint management"));?>

<table>
	<tr><td><strong><?php echo _('Manage releases/sprints') ?></strong></td><td>
		<input type="radio" name="release_field_tracker" value="1" id="release_tracker1" <?php echo ( $release_field_tracker!=2 ? 'checked' : '') ?>>&nbsp;<?php echo _('by tasks') ?><br>
		<input type="radio" name="release_field_tracker" value="2" id="release_tracker2" <?php echo ( $release_field_tracker==2 ? 'checked' : '') ?>>&nbsp;<?php echo _('by user stories') ?></td></tr>
	<tr><td><strong><?php echo _('Release/sprint field') ?></strong></td><td><select name="release_field"><option value=""><?php echo _('Not defined') ?></option></select></td></tr>
</table>

<?php echo $HTML->boxBottom(); ?>

	</td>
</tr></table>

<p>
	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
</p>

<?php
	echo utils_requiredField().' '._('Indicates required fields.');
}
?>

</form>

<script>
var all_trackers = new Array();
<?php 
foreach( $at_arr as $tracker ) { 
	echo  'all_trackers.push( { id: "'.$tracker->getID().'", name: "'.$tracker->getName().'", desc: "'.$tracker->getDescription().'" } );'."\n";
} ?>

jQuery(function($){
	var user_story_ref_field ='';

	function loadUserStoriesTrackers() {
		var selected = $('select[name=user_stories_tracker] option:selected').val();
		if( !selected ) {
			selected = '<?php echo $user_stories_tracker ?>';
		}

		var str = '<option value=""><?php echo _('Not defined') ?></option>';
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
			url: '/plugins/taskboard/admin/ajax.php',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id     : <?php echo $group_id ?>,
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

				var str = '<option value=""><?php echo _('Not defined') ?></option>';
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
			url: '/plugins/taskboard/admin/ajax.php',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id     : <?php echo $group_id ?>,
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

				var str = '<option value=""><?php echo _('Not defined') ?></option>';
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
			showMessage("<?php echo _('Choose at least one tracker for using with taskboard.') ?>", "warning");
		} 

		$.ajax({
			type: 'POST',
			url: '/plugins/taskboard/admin/ajax.php',
			dataType: 'json',
			data : {
				action : 'get_trackers_fields',
				group_id : <?php echo $group_id ?>,
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
</script>
