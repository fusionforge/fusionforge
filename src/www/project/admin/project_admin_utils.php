<?php
/**
 * Project Admin: Module of common functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
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

/*
	Standard header to be used on all /project/admin/* pages
*/

function project_admin_header($params) {
	global $group_id,$feedback,$HTML;

	$params['toptab']='admin';
	$params['group']=$group_id;

	session_require_perm ('project_admin', $group_id) ;

	$project = group_get_object($group_id);
	if (!$project || !is_object($project)) {
		return;
	}

	$labels = array();
	$links = array();
	$attr_r = array();

	$labels[] = _('Project Information');
	$attr_r[] = array('class' => 'tabtitle', 'title' => _('General information about project. Tag, trove list, description.'));
	$labels[] = _('Users and permissions');
	$attr_r[] = array('class' => 'tabtitle', 'title' => _('Permissions management. Edit / Create roles. Assign new permissions to user. Add / Remove member.'));
	$labels[] = _('Tools');
	$attr_r[] = array('class' => 'tabtitle', 'title' => _('Activate / Desactivate extensions like docman, forums, plugins.'));
	$labels[] = _('Project History');
	$attr_r[] = array('class' => 'tabtitle', 'title' => _('Show the significant change of your project.'));
	if(forge_get_config('use_people')) {
		$labels[] = _('Post Jobs');
		$attr_r[] = array('class' => 'tabtitle', 'title' => _('Hiring new people. Describe the job'));
		$labels[] = _('Edit Jobs');
		$attr_r[] = array('class' => 'tabtitle', 'title' => _('Edit already created available position in your project.'));
	}
	if(forge_get_config('use_project_multimedia')) {
		$labels[] = _('Edit Multimedia Data');
		//TODO: set the title.
		$attr_r[] = array('class' => 'tabtitle', 'title' => _(''));
	}
	if(forge_get_config('use_project_vhost')) {
		$labels[] = _('VHOSTs');
		//TODO: set the title.
		$attr_r[] = array('class' => 'tabtitle', 'title' => _(''));
	}
	if(forge_get_config('use_project_database')) {
		$labels[] = _('Database Admin');
		//TODO: set the title.
		$attr_r[] = array('class' => 'tabtitle', 'title' => _(''));
	}
	if ($project->usesStats()) {
		$labels[] = _('Stats');
		//TODO: set the title.
		$attr_r[] = array('class' => 'tabtitle', 'title' => _(''));
	}
	plugin_hook("quota_label_project_admin");

	$links[] = '/project/admin/?group_id='.$group_id;
	$links[] = '/project/admin/users.php?group_id='.$group_id;
	$links[] = '/project/admin/tools.php?group_id='.$group_id;
	$links[] = '/project/admin/history.php?group_id='.$group_id;
	if(forge_get_config('use_people')) {
		$links[] = '/people/createjob.php?group_id='.$group_id;
		$links[] = '/people/?group_id='.$group_id;
	}
	if(forge_get_config('use_project_multimedia')) {
		$links[] = '/project/admin/editimages.php?group_id='.$group_id;
	}
	if(forge_get_config('use_project_vhost')) {
		$links[] = '/project/admin/vhost.php?group_id='.$group_id;
	}
	if(forge_get_config('use_project_database')) {
		$links[] = '/project/admin/database.php?group_id='.$group_id;
	}
	$links[] = '/project/stats/?group_id='.$group_id;
	plugin_hook("quota_link_project_admin");

	$params['submenu'] = $HTML->subMenu($labels, $links, $attr_r);

	plugin_hook("groupadminmenu", $params);

	site_project_header($params);
}

/*

	Standard footer to be used on all /project/admin/* pages

*/

function project_admin_footer($params=array()) {
	site_project_footer($params);
}

/*

	The following three functions are for group
	audit trail

	When changes like adduser/rmuser/change status
	are made to a group, a row is added to audit trail
	using group_add_history()

*/

function group_get_history ($group_id=false) {
return db_query_params("SELECT group_history.field_name,group_history.old_value,group_history.adddate,users.user_name
FROM group_history,users
WHERE group_history.mod_by=users.user_id
AND group_id=$1 ORDER BY group_history.adddate DESC", array($group_id));
}

function group_add_history ($field_name,$old_value,$group_id) {
	$group=group_get_object($group_id);
	$group->addHistory($field_name,$old_value);
}

/*

	Nicely html-formatted output of this group's audit trail

*/

function show_grouphistory ($group_id) {
	/*
		show the group_history rows that are relevant to
		this group_id
	*/

	$result=group_get_history($group_id);
	$rows=db_numrows($result);

	if ($rows > 0) {

		echo '<p>'._('This log will show who made significant changes to your project and when').'</p>';

		$title_arr=array();
		$title_arr[]=_('Field');
		$title_arr[]=_('Old Value');
		$title_arr[]=_('Date');
		$title_arr[]=_('By');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		for ($i=0; $i < $rows; $i++) {
			$field=db_result($result, $i, 'field_name');
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';

			if (is_numeric(db_result($result, $i, 'old_value'))) {
				if (preg_match("/[Uu]ser/i", $field)) {
					echo user_getname(db_result($result, $i, 'old_value'));
				} else {
					echo db_result($result, $i, 'old_value');
				}
			} else {
				echo db_result($result, $i, 'old_value');
			}
			echo '</td>'.
				'<td>'.date(_('Y-m-d H:i'),db_result($result, $i, 'adddate')).'</td>'.
				'<td>'.db_result($result, $i, 'user_name').'</td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo '<p>'._('No changes').'</p>';
	}
}

/*
	prdb_namespace_seek - check that a projects' potential db name hasn't
	already been used.  If it has - add a 1..20 to the end of it.  If it
	iterates through twenty times and still fails - namespace depletion -
	throw an error.

 */
function prdb_namespace_seek($namecheck) {

	$query = 'SELECT * FROM prdb_dbs WHERE dbname=$1';

	$res_dbl = db_query_params($query, array($namecheck));

	if (db_numrows($res_dbl) > 0) {
		//crap, we're going to have issues
		$curr_num = 1;

		while ((db_numrows($res_dbl) > 0) && ($curr_num < 20)) {

			$curr_num++;
			$namecheck .= $namecheck.$curr_num;

			$res_dbl = db_query_params($query, array($namecheck));
		}

		// if we reached 20, then the namespace is depleted - eject eject
		if ($curr_num == 20) {
			exit_error(_('Failed to find namespace for database'),'home');
		}

	}
	return $namecheck;

} //end prdb_namespace_seek()

function random_pwgen() {
	return (substr(strtr(base64_encode(util_randbytes(9)), '+', '.'),
		       0, 10));
}

function permissions_blurb() {
	return _('<strong>NOTE:</strong><dl><dt><strong>Project Admins (bold)</strong></dt><dd>can access this page and other project administration pages</dd><dt><strong>Release Technicians</strong></dt><dd>can make the file releases (any project admin also a release technician)</dd><dt><strong>Tool Technicians (T)</strong></dt><dd>can be assigned Bugs/Tasks/Patches</dd><dt><strong>Tool Admins (A)</strong></dt><dd>can make changes to Bugs/Tasks/Patches as well as use the /toolname/admin/ pages</dd><dt><strong>Tool No Permission (N/A)</strong></dt><dd>Developer doesn\'t have specific permission (currently equivalent to \'-\')</dd><dt><strong>Moderators</strong> (forums)</dt><dd>can delete messages from the project forums</dd><dt><strong>Editors</strong> (doc. manager)</dt><dd>can update/edit/remove documentation from the project.</dd></dl>');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
