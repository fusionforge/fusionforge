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

	$tablearr = array(_('Title'),_('Start date'),_('End date'), _('Goals'));
	
	echo $HTML->listTableTop($tablearr, false, 'sortable_table_tracker', 'sortable_table_tracker');
	
	
	foreach( $taskboardReleases as $release ) {
		echo '
		<tr valign="middle">
 			<td>'.util_make_link ('/plugins/taskboard/admin/?group_id='.$group_id.'&amp;action=edit_release&amp;release_id='.$release->getID(),
						htmlspecialchars( $release->getTitle() ) ).'</a></td>
			<td>'.date("Y-m-d", $release->getStartDate()).'</td>
			<td>'.date("Y-m-d", $release->getEndDate()).'</td>
			<td>'.htmlspecialchars( $release->getGoals() ).'</td>
		</tr>
		';
	}
	echo $HTML->listTableBottom();
	
?>
