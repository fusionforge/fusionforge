<?php
/**
 * admin hierarchy view
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $group_id;

echo '<h3>'._('Modify the hierarchy').'</h3>';
echo '<form action="/plugins/projects_hierarchy/add_son.php?group_id='.$group_id.'" method="POST" name="formson">';
//select box of sons
echo '<table><tr>';
echo '<td>'._('Select a project:').'</td><td>'.son_box($group_id,'sub_project_id','0').'</td><td>&nbsp;</td>' ;
echo '</tr><tr>' ;
echo '<td>'._('Commentary:').'</td><td> <input type="text" size="25" value="" name="com"></td>' ;
//echo type_son_box();
echo '<td><input type="submit" name="son" value="'._('Add son project').'"></td></tr></table></form>';
echo '<br/>';
echo '<form action="/plugins/projects_hierarchy/add_link.php?group_id='.$group_id.'" method="POST" name="formlink">';
//select box of sons
echo '<table><tr>';
echo '<td>'._('Select a project:').'</td><td>'.link_box($group_id,'sub_project_id','0').'</td><td>&nbsp;</td>';
echo '</tr><tr>' ;
echo '<td>'._('Commentary:').'</td><td><input type="text" size="25" value="" name="com"></td>' ;
echo '<td><input type="submit" name="son" value="'._('Add a link').'"></td></tr></table></form>';
echo '<br/>';
//select all the sons of the current project
$res_son = db_query_params ('SELECT group_id,group_name,unix_group_name,sub_project_id, activated, link_type, com FROM groups,plugin_projects_hierarchy WHERE
		(groups.group_id = plugin_projects_hierarchy.sub_project_id
		AND plugin_projects_hierarchy.project_id = $1)',
		array ($group_id))
	or die (db_error ());
if (!$res_son || db_numrows($res_son) < 1) {
	$cpt_son = 0;
} else {
	//display of sons
	$cpt_son = 1;
	echo _('Link list');
	echo '<table>';
	$i = 0;
	while($row_son = db_fetch_array($res_son)){
		$i++;
		echo '<tr>';
		echo '<td>';
		//link to the project
		echo "<a href=\"/projects/".$row_son['unix_group_name']."/\">".$row_son['group_name']."</a>";
		echo '</td>';
		echo '<td>';
		if($row_son['link_type'] == 'navi'){
			echo _('Navigation link');
		} else {
			echo _('Share link');
		}
		echo '</td>';
		echo '<td>';
		if($row_son['activated'] == 'f'){
			echo _('Waiting');
		} else {
			print "<b>"._('Authorize')."</b>";
		}
		echo '</td>';
		echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_son.php?group_id=".$group_id."&sub_group_id=".$row_son['sub_project_id']."\"}'}>"._('Delete')."</a></td>";
		echo "<tr><td colspan='4'>"._('Commentary:')." <i>".$row_son['com']."</i>";
		echo '</td></tr>';
	}
}
//select  navigation link by father
$res_son = db_query_params('SELECT group_id,group_name,unix_group_name,project_id, activated,link_type,com FROM groups,plugin_projects_hierarchy WHERE
		(groups.group_id = plugin_projects_hierarchy.project_id
		AND plugin_projects_hierarchy.sub_project_id = $1 AND plugin_projects_hierarchy.link_type = $2) ',
		array($group_id,'navi'))
	or die (db_error ());
if (!$res_son || db_numrows($res_son) < 1) {
	if($cpt_son == 1 ){
		echo '</table>';
	}
} else {
	//display of sons
	if($cpt_son != 1 ){
		echo _('Link list');
		echo '<table>';
	}
	$i = 0;
	while($row_son = db_fetch_array($res_son)){
		$i++;
		echo '<tr>';
		echo '<td>';
		//link to the project
		echo "<a href=\"/projects/".$row_son['unix_group_name']."/\">".$row_son['group_name']."</a>";
		echo '</td>';
		echo '<td>';
		if($row_son['link_type'] == 'navi'){
			echo _('Navigation link');
		} else {
			echo _('Share link');
		}
		echo '</td>';
		echo '<td>';
		if($row_son['activated'] == 'f'){
			//echo _('Waiting');
			echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to authorize this project?')."\")){window.location.href=\"../../plugins/projects_hierarchy/wait_son.php?sub_group_id=".$group_id."&group_id=".$row_son['project_id']."\"}'}>"._('Authorize')."</a></td>";
		} else {
			print "<b>"._('Authorize')."</b>";
		}
		echo '</td>';
		echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?group_id=".$row_son['project_id']."&sub_group_id=".$group_id."\"}'}>"._('Delete')."</a></td>";
		echo "<tr><td colspan='4'>"._('Commentary of father:')." <i>".$row_son['com']."</i></td>";
		echo '</tr>';
	}
	echo '</table>';
}
//research allowing father
$res_father = db_query_params('SELECT group_id,group_name,unix_group_name,project_id,com FROM groups,plugin_projects_hierarchy WHERE
			groups.group_id = plugin_projects_hierarchy.project_id
			AND plugin_projects_hierarchy.sub_project_id = $1
			AND plugin_projects_hierarchy.activated = true AND plugin_projects_hierarchy.link_type = $2',
			array($group_id, 'shar'))
	or die (db_error ()) ;
if (!$res_father || db_numrows($res_father) < 1) {
} else {
	//display of the father of the current project
	echo '<table><tr><td colspan=\"2\">';
	echo _('Project\'s parent');
	echo '</td></tr>';
	while ($row_father = db_fetch_array($res_father)) {
		echo '<tr>';
		echo '<td>';
		echo "<a href=\"/projects/".$row_father['unix_group_name']."/\">".$row_father['group_name']."</a>";
		echo '</td><td>';
		echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?sub_group_id=".$group_id."&group_id=".$row_father['group_id']."\"}'}>"._('Delete')."</a></td>";
		echo '</td></tr>';
	}
	echo '</table>';
}
//research waiting fathers
$res_wait = db_query_params('SELECT group_id,group_name,unix_group_name,project_id,link_type,com FROM groups,plugin_projects_hierarchy WHERE
			groups.group_id = plugin_projects_hierarchy.project_id
			AND plugin_projects_hierarchy.sub_project_id = $1
			AND plugin_projects_hierarchy.activated = false AND plugin_projects_hierarchy.link_type = $2',
			array ($group_id, 'shar'))
	or die (db_error ()) ;
if (!$res_wait || db_numrows($res_wait) < 1) {
} else {
	//display of waiting fathers
	echo '<table><tr><td colspan=\"2\">';
	echo _('Father waiting for validation');
	echo '</td></tr>';
	while ($row_wait = db_fetch_array($res_wait)) {
		echo '<tr>';
		echo '<td>';
		echo "<a href=\"../../projects/".$row_wait['unix_group_name']."/\">".$row_wait['group_name']."</a>";
		echo '</td>';
		echo'<td>';
		echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to authorize this project?')."\")){window.location.href=\"../../plugins/projects_hierarchy/wait_son.php?sub_group_id=".$group_id."&group_id=".$row_wait['group_id']."\"}'}>"._('Do you really want to authorize this project?')."</a></td>";
		echo '</td><td>';
		echo "<td><a href='#' onclick='if(confirm(\""._('Do you really want to delete this link?')."\")){window.location.href=\"../../plugins/projects_hierarchy/del_father.php?sub_group_id=".$group_id."&group_id=".$row_wait['group_id']."\"}'}>"._('Delete')."</a></td>";
		echo "</td><tr><td colspan='3'>"._('Commentary of father:')." <i>".$row_wait['com']."</i>";
		echo '</td></tr>';
	}
	echo '</table>';
}
?>
