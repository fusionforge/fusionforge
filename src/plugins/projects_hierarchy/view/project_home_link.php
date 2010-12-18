<?php
/**
 * projects_hierarchyPlugin Class
 *
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2010 (c) Franck Villaume - Capgemini
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
 
echo $HTML->boxTop(_('Linked projects'));
$cpt_project = 0 ;
// father request
$res = db_query_params('SELECT DISTINCT group_id,unix_group_name,group_name FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type=$1 AND plugin_projects_hierarchy.activated=$2 AND groups.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.sub_project_id=$3',
			array ('shar',
				't',
				$group_id));
echo db_error();
while ($row = db_fetch_array($res)) {
	echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Parent project').': <a href="'.forge_get_config('url_prefix').'/projects/'.$row['unix_group_name'].'/">' . $row['group_name'] . '</a><br/>';
	$cpt_project ++;
}

if($cpt_project != 0) {
	print '<hr size="1" />';
}
$cpt_temp = $cpt_project;
// sons request
$res = db_query_params('SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type=$1 AND plugin_projects_hierarchy.activated=$2 AND groups.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.project_id=$3',
			array ('shar',
				't',
				$group_id));
echo db_error();
while ($row = db_fetch_array($res)) {
	echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Child project').' : <a href="'.forge_get_config('url_prefix').'/projects/'.$row['unix_group_name'].'/">' . $row['group_name'] . '</a> : '.$row['com'].'<br/>';
	$cpt_project ++;
}

if($cpt_project != $cpt_temp) {
	print '<hr size="1" />';
}
$cpt_temp = $cpt_project ;

// links if project is father
$res = db_query_params('SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type=$1 AND plugin_projects_hierarchy.activated=$2 AND groups.group_id=plugin_projects_hierarchy.sub_project_id AND plugin_projects_hierarchy.project_id=$3',
			array ('navi',
				't',
				$group_id));
echo db_error();
while ($row = db_fetch_array($res)) {
	echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Links')." : <a href=\"".forge_get_config('url_prefix')."/projects/".$row['unix_group_name']."/\">" . $row['group_name'] . "</a> :  ".$row['com']."<br/>";
	$cpt_project ++;
}

// links if project is son
$res = db_query_params('SELECT DISTINCT group_id,unix_group_name,group_name,com FROM groups,plugin_projects_hierarchy WHERE plugin_projects_hierarchy.link_type=$1 AND plugin_projects_hierarchy.activated=$2 AND groups.group_id=plugin_projects_hierarchy.project_id AND plugin_projects_hierarchy.sub_project_id=$3',
			array('navi',
				't',
				$group_id));
echo db_error();
while ($row = db_fetch_array($res)) {
	echo html_image('ic/forum20g.png','20','20',array('alt'=>_('Link'))).'&nbsp;'._('Links')." : <a href=\"".forge_get_config('url_prefix')."/projects/".$row['unix_group_name']."/\">" . $row['group_name'] . "</a><br/>";
	$cpt_project ++;
}

if($cpt_project != $cpt_temp){ 
	print '<hr size="1" />';
}

if($cpt_project == 0){ 
	echo _('No linked project avalaible');
	print '<hr size="1" />';
}

echo $HTML->boxBottom();
?>