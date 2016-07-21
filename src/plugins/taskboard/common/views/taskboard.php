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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfplugins.'taskboard/common/include/TaskBoardHtml.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$taskboard_id = getIntFromRequest('taskboard_id');
$pluginTaskboard = plugin_get_object('taskboard');

if (!$group_id) {
	exit_error(_('Cannot Process your request')._(': ')._('No ID specified'), 'home');
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		exit_no_group();
	}
	if ( ! ($group->usesPlugin($pluginTaskboard->name))) {//check if the group has the plugin active
		exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginTaskboard->name),'home');
	}

	$taskboard = new TaskBoardHtml($group,$taskboard_id);
	$taskboard->header(
		array(
			'title' => $taskboard->getName(),
			'pagename' => 'Taskboard',
			'sectionvals' => array($group->getPublicName()),
			'group' => $group_id
		)
	);

	if($taskboard->isError()) {
		echo $HTML->error_msg($taskboard->getErrorMessage());
	} else {

		if(count($taskboard->getUsedTrackersIds()) == 0) {
			echo $HTML->warning_msg(_('Choose at least one tracker for using with taskboard.'));
		} else {

			$columns = $taskboard->getColumns();

			if(count($columns) == 0) {
				echo $HTML->warning_msg(_('Configure columns for the board first.'));
			} else {

				$messages = '';
				foreach($columns as $column) {
					if(count($column->getResolutions()) == 0) {
						$messages .= sprintf( _('Resolutions list is empty for "%s", column is not dropable'), $column->getTitle() ).'<br>';
					}
				}

				$user_stories_tracker = $taskboard->getUserStoriesTrackerID();
				$columns_number = count($columns) + ($user_stories_tracker ? 1 : 0);
				$column_width = intval(100 / $columns_number);
?>

<div id="messages" class="warning" <?php if (!$messages) { ?> style="display: none;" <?php } ?>><?php echo $messages ?></div>
<br/>

<?php
$techs = $group->getUsers();

$_assigned_to = getIntFromRequest('_assigned_to', '0');
// stolen code from tracker
$tech_id_arr = array();
$tech_name_arr = array();

foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID() ;
	$tech_name_arr[] = $tech->getRealName() ;
}
$tech_id_arr[] = '0';  //this will be the 'any' row
$tech_name_arr[] = _('Any');

if (is_array($_assigned_to)) {
	$_assigned_to='';
}
$tech_box = html_build_select_box_from_arrays($tech_id_arr, $tech_name_arr, '_assigned_to', $_assigned_to, true, _('Unassigned'));
// end of the stolen code

$release_box = '';
$release_id = getIntFromRequest('_release', '0');
if ($taskboard->getReleaseField()) {
	$release_field_alias = $taskboard->getReleaseField();

	if( $release_id ) {
		// use release, specified with URL
		$current_release = new TaskBoardRelease( $taskboard, $release_id );
	} else {
		// use current release, according to the dates
		$current_release = $taskboard->getCurrentRelease();
	}

	$current_release_title = '';
	if ($current_release ) {
		$current_release_title = $current_release->getTitle();
	}

	$releases = $taskboard->getReleaseValues();

	if ($releases) {
		$release_id_arr = array();
		$release_name_arr = array();
		foreach( $releases as $release_name => $release_id ) {
			$release_id_arr[] = $release_name;
			$release_name_arr[] = $release_name;
		}

		$release_box=html_build_select_box_from_arrays ($release_id_arr,$release_name_arr,'_release',$current_release_title, false, 'none', true);
	}
}

$colspan=0;
if ($release_box) {
	$colspan = 2;
	if ( forge_check_perm('tracker_admin', $group_id ) ) {
		$colspan = 3;
	}
}
?>


<div>
	<form>
		<table cellspacing="0" width="100%">
			<tr valign="middle">
				<td width="10%">
					<?php echo _('Assignee')._(': '); ?>
				</td>
				<td width="10%">
					<?php echo $tech_box ; ?>
				</td>
				<td colspan="<?php echo $colspan ?>">
				</td>
			</tr>
			<tr>
		<?php if ($release_box) { ?>
				<td>
					<?php echo _('Release'); ?>
				</td>
				<td>
					<?php echo $release_box; ?>
				</td>
				<?php if ( forge_check_perm('tracker_admin', $group_id ) ) { ?>
				<td style="vertical-align: middle;">
					<div id="taskboard-release-description"></div>
					<div id="taskboard-release-snapshot">
						<input type="hidden" name="taskboard_release_id" id="taskboard-release-id" value="" />
						<input type="text" name="snapshot_date" value="<?php echo date('Y-m-d') ?>" />
						<button id="taskboard-save-snapshot-btn"><?php echo _('Save release snapshot'); ?></button>
					</div>
				</td>
				<?php } ?>
				<td>
					<div id="taskboard-burndown-div">
						<button id="taskboard-burndown-btn"><?php echo _('Burndown chart'); ?></button>
					</div>
				</td>
		<?php } ?>

			</tr>
		</table>
	</form>
</div>

<div id="agile-board-progress">
</div>

<table id="agile-board">
	<thead>
		<tr valign="top">

		<?php if( $user_stories_tracker ) { ?>
			<td class="agile-phase-title" style="width: <?php echo $column_width ?>%;"><?php echo  _('User stories')?></td>
		<?php } ?>

		<?php foreach( $columns as $column ) { ?>
		<?php
			$style='width: ' . $column_width . '%;';
			$title_bg_color =  $column->getTitleBackgroundColor();
			if( $title_bg_color ) {
				$style .= 'background-color: ' . $title_bg_color . ';';
			}
		?>
			<td class="agile-phase-title" style="<?php echo $style ?>">
				<?php echo $column->getTitle() ?>&nbsp;&nbsp;
				<input type="checkbox" class="agile-minimize-column" id="phase-title-<?php echo $column->getID() ?>" phase_id="<?php echo $column->getID() ?>">
			</td>
		<?php } ?>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>


<div id="new-task-dialog" style="display: none;">
	<input type="hidden" name="user_story_id" id="user_story_id" value="">
	<?php
		$used_trackers = $taskboard->getUsedTrackersIds();
		if(count($used_trackers) == 1) {
			echo html_e('input', array('type' => 'hidden', 'name' => 'tracker_id', 'id' => 'tracker_id', 'value' => $used_trackers[0]));
		} else {
			// select target tracker if more then single trackers are configured
			echo "<div>\n";
			echo '<select name="tracker_id" id="tracker_id">';
			foreach( $used_trackers as $tracker_id ) {
				$tracker = $taskboard->TrackersAdapter->getTasksTracker($tracker_id);
				echo '<option value="'.$tracker->getID().'">' . $tracker->getName() . '</option>';
			}
			echo '</select>';
			echo "</div>\n";
		}
	?>



	<div>
		 <strong><?php echo _('Summary')?><?php echo utils_requiredField(); ?>:</strong><br />
		<input id="tracker-summary" title="<?php echo util_html_secure(_('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.')) ?>" type="text" name="summary" size="70" value="" maxlength="255" />
	</div>

	<div>
		<strong><?php echo _('Detailed description') ?><?php echo utils_requiredField(); ?>: </strong>
		<br />
		<textarea id="tracker-description" name="description" rows="10" cols="79" title="<?php echo util_html_secure(html_get_tooltip_description('description')) ?>"></textarea>
	</div>
</div>

<script>
var gGroupId = <?php echo $group_id ?>;
var gTaskboardId = <?php echo $taskboard_id ?>;
var gIsManager = <?php echo ( $taskboard->TrackersAdapter->isManager() ? 'true' : 'false' ) ?>;
var gIsTechnician = <?php echo ( $taskboard->TrackersAdapter->isTechnician() ? 'true' : 'false' ) ?>;
var gAjaxUrl = '<?php echo util_make_url ('/plugins/'.$pluginTaskboard->name.'/ajax.php') ; ?>';
var gMessages = {
	'notasks' : "<?php echo _('There are no tasks found.') ?>",
	'progressByTasks' : "<?php echo _('Progress by tasks') ?>",
	'progressByCost' : "<?php echo _('Progress by cost') ?>",
	'remainingCost' : "<?php echo _('Remaining m/d') ?>",
	'completedCost' : "<?php echo _('Completed m/d') ?>"
};

<?php
	$releases = array();
	foreach( $taskboard->getReleases() as $release ) {
		$releases[ $release->getTitle() ] = array(
			'id' => $release->getID(),
			'startDate' => date( 'Y-m-d', $release->getStartDate()),
			'endDate' => date( 'Y-m-d', $release->getEndDate()),
			'goal' => htmlspecialchars( $release->getTitle() )
		);
	}
?>
var gReleases = <?php echo json_encode($releases) ;?>

bShowUserStories = <?php echo $taskboard->getUserStoriesTrackerID() ? 'true' : 'false' ?>;
aUserStories = [];
aPhases = []

jQuery( document ).ready(function( $ ) {
	loadTaskboard( <?php echo $group_id ?> );

	jQuery('select[name="_assigned_to"], select[name="_release"]').change(function () {
		loadTaskboard( <?php echo $group_id ?> );
	});

	jQuery('#taskboard-burndown-btn').click( function ( e ) {
		window.location = '<?php echo util_make_url ('/plugins/'.$pluginTaskboard->name.'/releases/?view=burndown&group_id='.$group_id.'&taskboard_id='.$taskboard_id.'&release_id=' ); ?>' + jQuery('#taskboard-release-id').val();
		e.preventDefault();
	});

	<?php if( user_getid()) { ?>
	jQuery('#new-task-dialog').dialog(
	{
		autoOpen: false,
		width: 350,
		modal: true,
		buttons: [
			{
				text : "<?php echo _("Create task") ?>",
				id: "new-task-dialog-submit-button",
				click : function () {
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo util_make_url('/plugins/'.$pluginTaskboard->name.'/ajax.php') ;?>',
						dataType: 'json',
						data : {
							action : 'add',
							group_id : gGroupId,
							taskboard_id : gTaskboardId,
							tracker_id : jQuery('#tracker_id').val(),
							user_story_id : jQuery('#user_story_id').val(),
							title : jQuery('#tracker-summary').val(),
							desc : jQuery('#tracker-description').val(),
							release : jQuery('select[name="_release"]').val(),
							assigned_to :  jQuery('select[name="_assigned_to"]').val()
						},
						async: true
					}).done(function( answer ) {
						jQuery('#new-task-dialog').dialog( "close" );

						if(answer['alert']) {
							showMessage(answer['alert'], 'error');
						}

						if(answer['action'] == 'reload') {
							// reload whole board
							loadTaskboard( gGroupId );
						}
					}).fail(function( jqxhr, textStatus, error ) {
						var err = textStatus + ', ' + error;
						alert(err);
					});
				}
			},
			{
				text : "<?php echo _("Cancel") ?>",
				click: function() {
					jQuery('#new-task-dialog').dialog( "close" );
				}
			}
		],
		close: function() {
		},
		open: function () {
			jQuery('#new-task-dialog-submit-button').prop( "disabled", true );
			jQuery('#tracker-summary').val('');
			jQuery('#tracker-description').val('');
		}
	});

	<?php if ( forge_check_perm('tracker_admin', $group_id ) ) { ?>
	jQuery('#taskboard-save-snapshot-btn').click( function ( e ) {
		jQuery.ajax({
			type: 'POST',
			url: '<?php echo util_make_url('/plugins/taskboard/ajax.php') ;?>',
			dataType: 'json',
			data : {
				action : 'save_release_snapshot',
				group_id : gGroupId,
				taskboard_id : gTaskboardId,
				tracker_id : jQuery('#tracker_id').val(),
				release_id : jQuery('#taskboard-release-id').val(),
				snapshot_date :  jQuery('input[name="snapshot_date"]').val()
			},
			async: true
		}).done(function( answer ) {
			if(answer['alert']) {
				alert(answer['alert']);
			}
		}).fail(function( jqxhr, textStatus, error ) {
			var err = textStatus + ', ' + error;
			alert(err);
		});

		e.preventDefault();
	});
	<?php }?>


	<?php } ?>

	jQuery('#tracker-summary, #tracker-description').keyup( function () {
		// submit button is enabled only if both, title and descritpion, are filled
		if( jQuery('#tracker-summary').val() && jQuery('#tracker-description').val()) {
			jQuery('#new-task-dialog-submit-button').prop( "disabled", false );
		} else {
			jQuery('#new-task-dialog-submit-button').prop( "disabled", true );
		}
	});
});
</script>
<?php
			}
		}
	}
}

site_project_footer(array());
