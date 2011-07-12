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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';
require_once $gfwww.'admin/admin_utils.php';

if (!forge_get_config('use_project_vhost')) {
	exit_disabled('home');
}

session_require_global_perm ('forge_admin');

if (getStringFromRequest('add')) {
	$groupname = getStringFromRequest('groupname');
	$vhost_name = getStringFromRequest('vhost_name');
	//$group_id = getIntFromRequest('group_id');

	if ($groupname) {

		$group = group_get_object_by_name($groupname);
		if (!$group || !is_object($group)) {
			exit_no_group();
		} elseif ($group->isError()) {
			exit_error($group->getErrorMessage(),'home');
		}

		$group_id = $group->getID();

		if (valid_hostname($vhost_name)) {

			$homedir = account_group_homedir($group->getUnixName());
			$docdir = $homedir.'/htdocs/';
			$cgidir = $homedir.'/cgi-bin/';


			$res = db_query_params ('
				INSERT INTO prweb_vhost(vhost_name, docdir, cgidir, group_id)
				VALUES ($1,$2,$3,$4)
			',
			array($vhost_name,
			$docdir,
			$cgidir,
			$group_id)) ;


			if (!$res || db_affected_rows($res) < 1) {
				$error_msg .= _('Error adding VHOST: ') .db_error();
			} else {
				$feedback .= _('Virtual Host: ').$vhost_name._(' scheduled for creation on group ').$group->getUnixName();
			}
		} else {
			$error_msg .= _('Vhost not valid');
		}
    } else {
		$warning_msg .=	_('Missing group name');
    }
}

if (getStringFromRequest('tweakcommit')) {
	$vhostid = getIntFromRequest('vhostid');
	$docdir = getStringFromRequest('docdir');
	$cgidir = getStringFromRequest('cgidir');

	$res = db_query_params ('
		UPDATE prweb_vhost
		SET docdir=$1,
			cgidir=$2
		WHERE vhostid=$3
	',
			array($docdir,
			$cgidir,
			$vhostid)) ;


	if (!$res || db_affected_rows($res) < 1) {
		$error_msg .= _('Error updating VHOST entry: ') .db_error();
	} else {
		$feedback .= _('Virtual Host entry updated.');
	}

}


site_admin_header(array('title'=>_('Site admin')));
?>

<h3><?php echo _('Virtual Host Administration'); ?></h3>

<form name="madd" method="post" action="<?php echo getStringFromServer('PHP_SELF'); ?>">

<strong><?php echo _('Add Virtual Host'); ?></strong>

<table border="0">

<tr>
<td><?php echo _('Project Unix Name'); ?></td>
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

	$res_vh = db_query_params ('
		SELECT vhostid,vhost_name,docdir,cgidir,unix_group_name,group_id
		FROM prweb_vhost,groups
		WHERE vhost_name=$1
		AND prweb_vhost.group_id=groups.group_id
	',
			array($vhost_name)) ;


	if (db_numrows($res_vh) > 0) {

		$row_vh = db_fetch_array($res_vh);

		print '<div class="feedback">'._('Update Record:').'</div>';

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
			<td>'.util_make_link_g ($row_vh['unix_group_name'],$row_vh['group_id'],$row_vh['unix_group_name']).'</td>
			<td><input maxlength="255" type="text" name="docdir" value="'.$row_vh['docdir'].'" /></td>
			<td><input type="text" name="cgidir" value="'.$row_vh['cgidir'].'" /></td><td><input maxlength="255" type="submit" value="'._('Update').'" /></tr>
			'.$GLOBALS['HTML']->listTableBottom().'

			<input type="hidden" name="tweakcommit" value="1" />
			<input type="hidden" name="vhostid" value="'.$row_vh['vhostid'].'" />
			</form>
		';
	} else {
		echo '<div class="warning">'._('No such VHOST: ') . $vhost_name.'</div>';
	}

}

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
