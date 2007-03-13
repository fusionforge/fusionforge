<?php
/**
 * Project Admin page to manage group's VHOST entries
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

if (!$sys_use_project_vhost) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group = &group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_error('Error','Error creating group object');
} else if ($group->isError()) {
        exit_error('ERROR',$group->getErrorMessage());
}

if (getStringFromRequest('createvhost')) {
	$vhost_name = getStringFromRequest('vhost_name');

	$homedir = account_group_homedir($group->getUnixName());
	$docdir = $homedir.'/htdocs/';
	$cgidir = $homedir.'/cgi-bin/';

	if (valid_hostname($vhost_name)) {

		$res = db_query("
			INSERT INTO prweb_vhost(vhost_name, docdir, cgidir, group_id) 
			values ('$vhost_name','$docdir','$cgidir',".$group->getID().")
		"); 

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= "Cannot insert VHOST entry: ".db_error();
		} else {
			$feedback .= _('Virtual Host scheduled for creation.');
			$group->addHistory('Added vhost '.$vhost_name.' ','');
		}

	} else {

		$feedback .= sprintf(_('Not a valid hostname - %1$s'), $vhost_name);

	}
}


if (getStringFromRequest('deletevhost')) {
	$vhostid = getStringFromRequest('vhostid');

	//schedule for deletion

	$res =	db_query("
		SELECT * 
		FROM prweb_vhost 
		WHERE vhostid='$vhostid'
	");

	$row_vh = db_fetch_array($res);

	$res = db_query("
		DELETE FROM prweb_vhost 
		WHERE vhostid='$vhostid' 
		AND group_id='$group_id'
	");

	if (!$res || db_affected_rows($res) < 1) {
		$feedback .= "Could not delete VHOST entry:".db_error();
	} else {
		$feedback .= _('VHOST deleted');
		$group->addHistory('Virtual Host '.$row_vh['vhost_name'].' Removed','');

	}

}

project_admin_header(array('title'=>_('Virtual Host Management'),'group'=>$group->getID()));

?>

<p>&nbsp;</p>

<?php printf(_('<strong><span style="text-decoration:underline">Add New Virtual Host</span></strong><p>To add a new virtual host - simply point a <strong>CNAME</strong> for <em>yourhost.org</em> at <strong>%1$s.%2$s</strong>.  %3$s does not currently host mail (i.e. cannot be an MX) or DNS</strong>.  <p>Clicking on "create" will schedule the creation of the Virtual Host.  This will be synced to the project webservers - such that <em>yourhost.org</em> will display the material at <em>%4$s.%5$s</em>.'), $group->getUnixName(), $GLOBALS['sys_default_domain'], $GLOBALS['sys_name'], $group->getUnixName(), $GLOBALS['sys_default_domain']) ?>
<p>

<form name="new_vhost" action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group->getID().'&createvhost=1'; ?>" method="post"> 
<table border="0">
<tr>
	<td> <?php echo _('New Virtual Host <em>(e.g. vhost.org)</em>') ?> </td>
	<td> <input type="text" size="15" maxlength="255" name="vhost_name" /> </td>
	<td> <input type="submit" value="<?php echo _('Create') ?>" /> </td>
</tr>
</table>
</form>

<?php

$res_db = db_query("
	SELECT *
	FROM prweb_vhost 
	WHERE group_id='".$group->getID()."'
");
	
if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]=_('Virtual Host');
	$title[]=_('Operations');
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '	<tr>
			<td>'.$row_db['vhost_name'].'</td>
			<td>[ <strong><a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group->getID().'&amp;vhostid='.$row_db['vhostid'].'&amp;deletevhost=1">'._('Delete').'</a></strong>]
			</tr>	
		';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>'._('No VHOSTs defined').'No VHOSTs defined</p>';
}

project_admin_footer(array());

?>
