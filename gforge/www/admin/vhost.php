<?php
/**
 * Site Admin page for maintaining groups' Virtual Hosts
 *
 * This page allows to:
 *   - add a VHOST entry for group
 *   - query properties of VHOST entry
 *   - edit some database (by going to group's DB Admin page)
 *   - register existing database in system
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');

if (!$sys_use_project_vhost) {
	exit_disabled();
}

session_require(array('group'=>'1','admin_flags'=>'A'));

if (getStringFromRequest('add')) {
	$groupname = getStringFromRequest('groupname');
	$vhost_name = getStringFromRequest('vhost_name');
	//$group_id = getIntFromRequest('group_id');

	if ($groupname) {

		$group = &group_get_object_by_name($groupname);
		if (!$group || !is_object($group)) {
			exit_error('Error','Could Not Get Group');
		} elseif ($group->isError()) {
			exit_error('Error',$group->getErrorMessage());
		}
		
		$group_id = $group->getID();

		if (valid_hostname($vhost_name)) {

			$homedir = account_group_homedir($group->getUnixName());
			$docdir = $homedir.'/htdocs/';
			$cgidir = $homedir.'/cgi-bin/';


			$res = db_query("
				INSERT INTO prweb_vhost(vhost_name, docdir, cgidir, group_id) 
				VALUES ('$vhost_name','$docdir','$cgidir',$group_id)
			");

			if (!$res || db_affected_rows($res) < 1) {
				$feedback .= _('Error adding VHOST:') .db_error();
			} else {
				$feedback .= _('Virtual Host:'). "<strong>".$vhost_name."</strong>" ._('Virtual Host:'). "<em>".$group->getUnixName()."</em>";
			}
		} else {

			$feedback .=	"<strong>" ._('The provided group name does not exist'). "</strong>";

		}
			
	}			
}

if (getStringFromRequest('tweakcommit')) {
	$vhostid = getIntFromRequest('vhostid');
	$docdir = getStringFromRequest('docdir');
	$cgidir = getStringFromRequest('cgidir');

	$res = db_query("
		UPDATE prweb_vhost 
		SET docdir='$docdir',
			cgidir='$cgidir' 
		WHERE vhostid=$vhostid
	");

	if (!$res || db_affected_rows($res) < 1) {
		$feedback .= _('Error updating VHOST entry:') .db_error();
	} else {
		$feedback .= _('Virtual Host entry updated.');
	}		

}


site_admin_header(array('title'=>_('Site Admin')));
?>

<h3><?php echo _('Virtual Host Administration'); ?></h3>

<form name="madd" method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">

<strong><?php echo _('Add Virtual Host'); ?></strong>

<table border="0">

<tr>
<td><?php echo _('Group Unix Name'); ?></td>
<td><input type="text" name="groupname" /></td>
</tr>

<tr>
<td><?php echo _('Virtual Host Name'); ?></td>
<td><input type="text" name="vhost_name" /></td>
</tr>
</table>

<input type="submit" name="add" value="<?php echo _('Add Virtual Host'); ?>" />
</form>

<p>&nbsp;</p>

<hr />
<strong><?php echo _('Tweak Directories'); ?></strong>
<br />

<form name="tweak" method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">
<table border="0">
<tr>
   <td><?php echo _('Virtual Host:'); ?></td><td><input type="text" name="vhost_name" /></td>
   <td><input type="submit" value="<?php echo _('Get Info'); ?>" /></td>
</tr>
</table>

<input type="hidden" name="tweak" value="1" />

</form>

<?php
if (getStringFromRequest('tweak')) {
	$vhost_name = getStringFromRequest('vhost_name');

	$res_vh = db_query("
		SELECT vhostid,vhost_name,docdir,cgidir,unix_group_name
		FROM prweb_vhost,groups
		WHERE vhost_name='$vhost_name'
		AND prweb_vhost.group_id=groups.group_id
	");

	if (db_numrows($res_vh) > 0) {

		$row_vh = db_fetch_array($res_vh);
	
		print '<p><strong>'._('Update Record:').'</strong></p><hr />';

		$title=array();
		$title[]=_('VHOST ID');
		$title[]=_('VHOST Name');
		$title[]=_('Group');
		$title[]=_('Htdocs Dir');
		$title[]=_('CGI Dir');
		$title[]=_('Operations');

		print '
			<form name="update" method="post" action="'.getStringFromServer('PHP_SELF').'">

			'.$GLOBALS['HTML']->listTableTop($title).'
			<tr><td>'.$row_vh['vhostid'].'</td>
			<td>'.$row_vh['vhost_name'].'</td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/projects/'.$row_vh['unix_group_name'].'">'.$row_vh['unix_group_name'].'</a></td>
			<td><input maxlength="255" type="text" name="docdir" value="'.$row_vh['docdir'].'" /></td>
			<td><input type="text" name="cgidir" value="'.$row_vh['cgidir'].'" /></td><td><input maxlength="255" type="submit" value="'._('Update').'" /></tr>
			'.$GLOBALS['HTML']->listTableBottom().'

			<input type="hidden" name="tweakcommit" value="1" />
			<input type="hidden" name="vhostid" value="'.$row_vh['vhostid'].'" />
			</form>
		';
	} else {
		echo _('No such VHOST:') . $vhost_name;
	}

}

site_admin_footer(array());

?>
