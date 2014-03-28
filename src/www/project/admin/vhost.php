<?php
/**
 * Project Admin page to manage group's VHOST entries
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

global $HTML;

if (!forge_get_config('use_project_vhost')) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
session_require_perm ('project_admin', $group_id) ;

$group = group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_error(_('Error:'),'Error creating group object');
} elseif ($group->isError()) {
        exit_error(_('Error:'),$group->getErrorMessage());
}

if (getStringFromRequest('createvhost')) {
	$vhost_name = getStringFromRequest('vhost_name');

	$homedir = account_group_homedir($group->getUnixName());
	$docdir = $homedir.'/htdocs/';
	$cgidir = $homedir.'/cgi-bin/';

	if (valid_hostname($vhost_name)) {

		$res = db_query_params('INSERT INTO prweb_vhost(vhost_name, docdir, cgidir, group_id)
			values ($1, $2, $3, $4)', array($vhost_name, $docdir, $cgidir, $group->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$error_msg = _('Cannot insert VHOST entry:').db_error();
		} else {
			$feedback = _('Virtual Host scheduled for creation.');
			$group->addHistory('Added vhost '.$vhost_name.' ','');
		}

	} else {

		$feedback .= sprintf(_('Not a valid hostname - %s'), $vhost_name);

	}
}


if (getStringFromRequest('deletevhost')) {
	$vhostid = getStringFromRequest('vhostid');

	//schedule for deletion

	$res =	db_query_params ('
		SELECT *
		FROM prweb_vhost
		WHERE vhostid=$1
	',
			array($vhostid));

	$row_vh = db_fetch_array($res);

	$res = db_query_params ('
		DELETE FROM prweb_vhost
		WHERE vhostid=$1
		AND group_id=$2
	',
			array($vhostid,
				$group_id));

	if (!$res || db_affected_rows($res) < 1) {
		$error_msg .= _('Could not delete VHOST entry:').db_error();
	} else {
		$feedback .= _('VHOST deleted');
		$group->addHistory('Virtual Host '.$row_vh['vhost_name'].' Removed','');

	}

}

project_admin_header(array('title'=>_('Virtual Host Management'),'group'=>$group->getID()));

print '<h2>' . _('Add New Virtual Host') . '</h2>';

print '<p>';
printf(_('To add a new virtual host - simply point a <strong>CNAME</strong> for <em>yourhost.org</em> at <strong>%1$s.%2$s</strong>.  %3$s does not currently host mail (i.e. cannot be an MX) or DNS</strong>.'), $group->getUnixName(), forge_get_config ('web_host'), forge_get_config ('forge_name'));
print '</p>';

print '<p>';
printf(_('Clicking on “Create” will schedule the creation of the Virtual Host.  This will be synced to the project webservers - such that <em>yourhost.org</em> will display the material at <em>%1$s.%2$s</em>.'), $group->getUnixName(), forge_get_config ('web_host'));
print '</p>';

?>

<form name="new_vhost" action="<?php echo util_make_uri('/project/admin/?group_id='.$group->getID().'&createvhost=1'); ?>" method="post">
<table>
<tr>
	<td> <?php echo _('New Virtual Host <em>(e.g. vhost.org)</em>') ?> </td>
	<td> <input type="text" size="15" maxlength="255" name="vhost_name" required="required" /> </td>
	<td> <input type="submit" value="<?php echo _('Create') ?>" /> </td>
</tr>
</table>
</form>

<?php

$res_db = db_query_params('
	SELECT *
	FROM prweb_vhost
	WHERE group_id=$1', array($group->getID()));

if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]=_('Virtual Host');
	$title[]=_('Operations');
	echo $HTML->listTableTop($title);
	while ($row_db = db_fetch_array($res_db)) {
		$cells = array();
		$cells[][] = $row_db['vhost_name'];
		$cells[][] = '[ <strong>'.util_make_link('/project/admin/?group_id='.$group->getID().'&vhostid='.$row_db['vhostid'].'&deletevhost=1', _('Delete')).'</strong>]';
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
} else {
	echo '<p>'._('No VHOSTs defined').'</p>';
}

project_admin_footer();
