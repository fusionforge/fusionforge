<?php
/**
  *
  * Site Admin page for maintaining groups' Virtual Hosts
  *
  * This page allows to:
  *   - add a VHOST entry for group
  *   - query properties of VHOST entry
  *   - edit some database (by going to group's DB Admin page)
  *   - register existing database in system
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');

if (!$sys_use_project_vhost) {
	exit_disabled();
}

session_require(array('group'=>'1','admin_flags'=>'A'));

if ($add) {

	if ($group_id) {

		$group = &group_get_object_by_name($groupname);
		exit_assert_object($group, 'Group');

		if (valid_hostname($vhost_name)) {

			$homedir = account_group_homedir($group->getUnixName());
			$docdir = $homedir.'/htdocs/';
			$cgidir = $homedir.'/cgi-bin/';


			$res = db_query("
				INSERT INTO prweb_vhost(vhost_name, docdir, cgidir, group_id) 
				VALUES ('$vhost_name','$docdir','$cgidir',$group_id)
			");

			if (!$res || db_affected_rows($res) < 1) {
				$feedback .= $Language->getText('admin_vhost','error_adding_vhost') .db_error();
			} else {
				$feedback .= $Language->getText('admin_vhost','virtual_host'). "<strong>".$vhost_name."</strong>" .$Language->getText('admin_vhost','scheduled_for_creation'). "<em>".$group->getUnixName()."</em>";
			}
		} else {

			$feedback .=	"<strong>" .$Language->getText('admin_vhost','the_provided_group'). "</strong>";

		}
			
	}			
}

if ($tweakcommit) {

	$res = db_query("
		UPDATE prweb_vhost 
		SET docdir='$docdir',
			cgidir='$cgidir' 
		WHERE vhostid=$vhostid
	");

	if (!$res || db_affected_rows($res) < 1) {
		$feedback .= $Language->getText('admin_vhost','error_updating_vhost') .db_error();
	} else {
		$feedback .= $Language->getText('admin_vhost','virtual_host_entry_updated');
	}		

}


site_admin_header(array('title'=>$Language->getText('admin_vhost','title')));
?>

<h3><?php echo $Language->getText('admin_vhost','virtual_host_administration'); ?></h3>

<form name="madd" method="post" action="<?php echo $PHP_SELF; ?>">

<strong><?php echo $Language->getText('admin_vhost','add_virtual_host'); ?></strong>

<table border="0">

<tr>
<td><?php echo $Language->getText('admin_vhost','group_unix_name'); ?></td>
<td><input type="text" name="groupname" /></td>
</tr>

<tr>
<td><?php echo $Language->getText('admin_vhost','virtual_host_name'); ?></td>
<td><input type="text" name="vhost_name" /></td>
</tr>
</table>

<input type="submit" name="add" value="<?php echo $Language->getText('admin_vhost','add_virtual_host'); ?>" />
</form>

<p>&nbsp;</p>

<hr />
<strong><?php echo $Language->getText('admin_vhost','tweak_directories'); ?></strong>
<br />

<form name="tweak" method="post" action="<?php echo $PHP_SELF; ?>">
<table border="0">
<tr>
   <td><?php echo $Language->getText('admin_vhost','virtual_host'); ?></td><td><input type="text" name="vhost_name" /></td>
   <td><input type="submit" value="<?php echo $Language->getText('admin_vhost','get_info'); ?>" /></td>
</tr>
</table>

<input type="hidden" name="tweak" value="1" />

</form>

<?php
if ($tweak) {


	$res_vh = db_query("
		SELECT vhostid,vhost_name,docdir,cgidir,unix_group_name
		FROM prweb_vhost,groups
		WHERE vhost_name='$vhost_name'
		AND prweb_vhost.group_id=groups.group_id
	");

	if (db_numrows($res_vh) > 0) {

		$row_vh = db_fetch_array($res_vh);
	
		print '<p><strong>'.$Language->getText('admin_vhost','update_record').'</strong></p><hr />';

		$title=array();
		$title[]=$Language->getText('admin_vhost','vhost_id');
		$title[]=$Language->getText('admin_vhost','vhost_name');
		$title[]=$Language->getText('admin_vhost','group');
		$title[]=$Language->getText('admin_vhost','htdocs_dir');
		$title[]=$Language->getText('admin_vhost','cgi_dir');
		$title[]=$Language->getText('admin_vhost','operations');

		print '
			<form name="update" method="post" action="'.$PHP_SELF.'">

			'.$GLOBALS['HTML']->listTableTop($title).'
			<tr><td>'.$row_vh['vhostid'].'</td>
			<td>'.$row_vh['vhost_name'].'</td>
			<td><a href="/projects/'.$row_vh['unix_group_name'].'">'.$row_vh['unix_group_name'].'</a></td>
			<td><input maxlength="255" type="text" name="docdir" value="'.$row_vh['docdir'].'" /></td>
			<td><input type="text" name="cgidir" value="'.$row_vh['cgidir'].'" /></td><td><input maxlength="255" type="submit" value="'.$Language->getText('admin_vhost','update').'" /></tr>
			'.$GLOBALS['HTML']->listTableBottom().'

			<input type="hidden" name="tweakcommit" value="1" />
			<input type="hidden" name="vhostid" value="'.$row_vh['vhostid'].'" />
			</form>
		';
	} else {
		echo $Language->getText('admin_vhost','no_such_host') . $vhost_name;
	}

}

site_admin_footer(array());

?>
