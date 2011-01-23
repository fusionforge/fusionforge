<?php
/*
 * Copyright 2004 (c) GForge LLC
 * Copyright 2006 (c) Fabien Regnier - Sogeti
 * Copyright 2011, Franck Villaume - Capgemini
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
 
function son_box($group_id, $name, $selected = 'xzxzxz') {
	global $son;
	if (!$son) {
		$family = get_family($group_id);
		$cond = '';
		$skipped = array();
		if($family != NULL) {
			
			reset($family);
			while (list($key, $val) = each($family)) {
				$skipped[] = $val;
			}
		}
		$son = db_query_params('SELECT group_id,group_name,register_time FROM groups 
					WHERE status = $1
					AND type_id = 1
					AND group_id != $2
					AND group_id <> ALL ($3)
					AND group_id NOT IN (SELECT sub_project_id FROM plugin_projects_hierarchy WHERE link_type = $4)
					AND group_id IN (select group_id from group_plugin,plugins where group_plugin.plugin_id = plugins.plugin_id and plugins.plugin_name = $5);',
					array('A',
						$group_id,
						db_int_array_to_any_clause($skipped),
						'shar',
						'projects_hierarchy'));
	}
	return html_build_select_box($son, $name, $selected, false);
}

function link_box($group_id, $name, $selected = 'xzxzxz') {
	global $link;
	if (!$link) {
		$link = db_query_params('SELECT group_id,group_name,register_time FROM groups 
					WHERE status=$1
					AND type_id=1
					AND group_id != $2
					AND group_id NOT IN (SELECT sub_project_id FROM plugin_projects_hierarchy WHERE project_id = $2 )
					AND group_id NOT IN (SELECT project_id FROM plugin_projects_hierarchy WHERE sub_project_id = $2 )
					AND group_id IN (select group_id from group_plugin,plugins where group_plugin.plugin_id = plugins.plugin_id and plugins.plugin_name = $3);',
					array('A', $group_id, 'projects_hierarchy'));
	}
	return html_build_select_box($link, $name, $selected, false);
}

function type_son_box() {
	return "<select name='link_type' onchange=\"javascript:
if(this.value!= 0){
document.formson.son.disabled=false
}
else {
document.formson.son.disabled=true
}\">
\n<option value='0' selected=\"selected\" >"._('Link Type')."</option>\n
<option value='shar'>"._('Share')."</option>\n
<option value='navi' >"._('Navigation')."</option>\n
</select>";
}

//search all the family,all ancestor 
function get_family($group_id, $family = '', $cpt = 0){
	$res = db_query_params('SELECT project_id FROM plugin_projects_hierarchy WHERE sub_project_id = $1',
				array($group_id))
		or die(db_error());
	if (!$res || db_numrows($res) < 1) {
		//return $family;
	} else {
		$row = db_fetch_array($res);
		$family[$cpt] = $row['project_id'];
		$cpt++;
		return get_family($row['project_id'], $family, $cpt);
	}
	return $family;
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
