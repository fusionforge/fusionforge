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

global $gfwww;

require_once $gfwww."env.inc.php";
require_once $gfcommon.'include/pre.php';

if( file_exists( $gfconfig.'plugins/taskboard/config.php' ) ) {
	require_once $gfconfig.'plugins/taskboard/config.php' ;
} else {
	exit_error(_('Configuration file is missing for taskboard plugin'),'home');
}

global $gfplugins;
require_once $gfplugins.'taskboard/common/include/TaskBoardHtml.class.php';


$user = session_get_user(); // get the session user

if (!$user || !is_object($user) ) {
	exit_error(_('Invalid User'),'home');
} else if ( $user->isError() ) {
	exit_error($user->getErrorMessage(),'home');
} else if ( !$user->isActive()) {
	exit_error(_('Invalid User : Not active'),'home');
}

$pluginname = 'taskboard';
$group_id = getStringFromRequest('group_id');

if (!$group_id) {
	exit_error(_('Cannot Process your request : No ID specified'),'home');
} else {
	$group = group_get_object($group_id);
	if ( !$group) {
		exit_no_group();
	}
	if ( ! ($group->usesPlugin ( $pluginname )) ) {//check if the group has the plugin active
		exit_error(sprintf(_('First activate the %s plugin through the Project\'s Admin Interface'),$pluginname),'home');
	}


	$taskboard = new TaskBoardHtml( $group ) ;

	$taskboard->header(
		array(
			'title'=>'Taskboard for '.$group->getPublicName(),
			'pagename'=>"Taskboard",
			'sectionvals'=>array(group_getname($group_id)),
			'group' => $group_id
		)
	);

	if( $taskboard->isError() ) {
		exit_error($taskboard->getErrorMessage());
	} else {

		if( count( $taskboard->getUsedTrackersIds() ) == 0 ) {
			exit_error( _('Choose at least one tracker for using with taskboard.') );
		}

		$columns = $taskboard->getColumns();
		$user_stories_tracker = $taskboard->getUserStoriesTrackerID();
		$columns_number = count($columns) + ( $user_stories_tracker ? 1 : 0 );
		$column_width = intval( 100 / $columns_number );
?>

<div id="messages" class="warning" style="display: none;"></div>
<br/>

<link rel="stylesheet" type="text/css" href="/plugins/taskboard/css/agile-board.css">
<script type="text/javascript" src="/plugins/taskboard/js/agile-board.js?<?php echo time() ?>"></script>
<?php if ( function_exists( 'html_use_jqueryui' ) ) { html_use_jqueryui(); } else { ?>
<script type="text/javascript" src="/plugins/taskboard/js/jquery-ui.js"></script>
<?php } ?>

<?php
$techs = $group->getUsers();

$_assigned_to = getIntFromRequest('_assigned_to','0');
// stolen code from tracker
$tech_id_arr = array () ;
$tech_name_arr = array () ;

foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID() ;
	$tech_name_arr[] = $tech->getRealName() ;
}
$tech_id_arr[]='0';  //this will be the 'any' row
$tech_name_arr[]=_('Any');

if (is_array($_assigned_to)) {
	$_assigned_to='';
}
$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,_('Unassigned'));
// end of the stolen code

$release_box = '';
if( $taskboard->getReleaseField() ) {
	$release_field_alias = $taskboard->getReleaseField();
	$current_release = '' ; //TODO - initialize with real current release
	
	$releases = $taskboard->getReleaseValues();

	if( $releases ) {
		$release_id_arr = array();
		$release_name_arr = array();
		foreach( $releases as $release_name => $release_id ) {
			$release_id_arr[] = $release_name;
			$release_name_arr[] = $release_name;
		}
		
		$release_box=html_build_select_box_from_arrays ($release_id_arr,$release_name_arr,'_release',$current_release,false);
	}
}
?>


<div class="tabbertab'.($af->query_type == 'custom' ? ' tabbertabdefault' : '').'" title="'._('Simple Filtering and Sorting').'">
	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID().'" method="post">
		<table cellspacing="0">
			<tr>
		<?php if( $release_box ) { ?>

				<td>
					<?php echo _('Sprint/Release').':&nbsp;'. $release_box ; ?>
				</td>
		<?php } ?>
				<td>
					<?php echo _('Assignee').':&nbsp;'. $tech_box ; ?>
				</td>
			</tr>
		</table>
	</form>
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
			<td class="agile-phase-title" style="<?php echo $style ?>"><?php echo $column->getTitle() ?></td>
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

		if( count($used_trackers) == 1 ) {
			echo '<input type="hidden" name="tracker_id" id="tracker_id" value="' . $at_arr[0]->getID(). '">';
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
var gIsManager = <?php echo ( $taskboard->TrackersAdapter->isManager() ? 'true' : 'false' ) ?>;
var gIsTechnician = <?php echo ( $taskboard->TrackersAdapter->isTechnician() ? 'true' : 'false' ) ?>;
var gAjaxUrl = '<?php echo util_make_url ('/plugins/taskboard/ajax.php') ; ?>';
var gMessages = {
	'notasks' : "<?php echo _('There no tasks found.') ?>"
};

bShowUserStories = <?php echo $taskboard->getUserStoriesTrackerID() ? 'true' : 'false' ?>;
aUserStories = [];
aPhases = []

jQuery( document ).ready(function( $ ) {
	loadTaskboard( <?php echo $group_id ?> );

	jQuery('select[name="_assigned_to"], select[name="_release"]').change(function () {
		loadTaskboard( <?php echo $group_id ?> );
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
						url: '<?php echo util_make_url('/plugins/taskboard/ajax.php') ;?>',
						dataType: 'json',
						data : {
							action : 'add',
							group_id : gGroupId,
							tracker_id : jQuery('#tracker_id').val(),
							user_story_id : jQuery('#user_story_id').val(), 
							title : jQuery('#tracker-summary').val(),
							desc : jQuery('#tracker-description').val(),
							release : jQuery('select[name="_release"]').val()
						},
						async: true
					}).done(function( answer ) {
						jQuery('#new-task-dialog').dialog( "close" );

						if(answer['message']) {
							showMessage(answer['message'], 'error');
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
		<?php } ?>
	});

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

site_project_footer(array());

