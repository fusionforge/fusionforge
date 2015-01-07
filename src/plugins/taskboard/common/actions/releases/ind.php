<?php

/*
 * Copyright (C) 2015 Vitaliy Pylypiv <vitaliy.pylypiv@gmail.com>
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
			'title'=>_('Taskboard for ').$group->getPublicName().' : '._('Releases'),
			'pagename'=>"Releases",
			'sectionvals'=>array(group_getname($group_id)),
			'group' => $group_id
		)
	);

?>

<link rel="stylesheet" type="text/css" href="/plugins/taskboard/css/agile-board.css">

<div id="messages" class="warning" style="display: none;"></div>
<br/>
<?php 
	if( !$taskboard->getReleaseField() ) {
		exit_error(_("Release field is not configured"));
	}
	
	$taskboardReleases = $taskboard->getReleases();
	
	if( $taskboardReleases === false ) {
		exit_error($taskboard->getErrorMessage());
	}
	
	echo '<p>' . util_make_link ('/plugins/taskboard/releases/?group_id='.$group_id.'&amp;action=add_release',
			'<strong>'._('Add release').'</strong>') ;
	echo '</p>';

	$tablearr = array(_('Title'),_('Start date'),_('End date'), _('Goals'), _('Page'));
	
	echo $HTML->listTableTop($tablearr, false, 'sortable_table_tracker', 'sortable_table_tracker');
	
	$today = strtotime(date('Y-m-d'));
	foreach( $taskboardReleases as $release ) {
		$release_title = htmlspecialchars( $release->getTitle() );
		if (session_loggedin() && forge_check_perm('project_admin', $taskboard->Group->getID() ) ) {
			$release_title = util_make_link (
				'/plugins/taskboard/releases/?group_id='.$group_id.'&amp;action=edit_release&amp;release_id='.$release->getID(),
				 $release_title
			);
		}
		
		$current_release = '';
		
		if( $release->getStartDate() < $today && $today < $release->getEndDate() ) {
			$current_release = ' class= "agile-current-release "';
		}

		echo '
		<tr valign="middle"'.$current_release.'>
 			<td>'.$release_title.'</td>
			<td>'.date("Y-m-d", $release->getStartDate()).'</td>
			<td>'.date("Y-m-d", $release->getEndDate()).'</td>
			<td>'.htmlspecialchars( $release->getGoals() ).'</td>
			<td>'. ( $release->getPageUrl() ? '<a href="'.$release->getPageUrl().'" target="_blank">'.htmlspecialchars( $release->getPageUrl() ).'</a>' : '' ).'</td>
		</tr>
		';
	}
	echo $HTML->listTableBottom();
	
?>
