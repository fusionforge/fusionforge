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
				$feedback .= 'Error adding VHOST: '.db_error();
			} else {
				$feedback .= "Virtual Host <strong>".$vhost_name."</strong> scheduled for creation on group <em>".$group->getUnixName()."</em>";
			}
		} else {

			$feedback .=	"<strong>The provided group name does not exist.</strong>";

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
		$feedback .= 'Error updating VHOST entry: '.db_error();
	} else {
		$feedback .= "Virtual Host entry updated.";
	}		

}


site_admin_header(array('title'=>"Site Admin"));
?>

<h3>Virtual Host Administration</h3>

<form name="madd" method="post" action="<?php echo $PHP_SELF; ?>">

<strong>Add Virtual Host</strong>

<table border="0">

<tr>
<td>Group Unix Name</td>
<td><input type="text" name="groupname" /></td>
</tr>

<tr>
<td>Virtual Host Name</td>
<td><input type="text" name="vhost_name" /></td>
</tr>
</table>

<input type="submit" name="add" value="Add Virtual Host" />
</form>

<p>&nbsp;</p>

<hr />
<strong>Tweak Directories</strong>
<br />

<form name="tweak" method="post" action="<?php echo $PHP_SELF; ?>">
<table border="0">
<tr>
   <td>Virtual Host: </td><td><input type="text" name="vhost_name" /></td>
   <td><input type="submit" value="Get Info" /></td>
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
	
		print '<p><strong>Update Record:</strong></p><hr />';

		$title=array();
		$title[]='VHOST ID';
		$title[]='VHOST Name';
		$title[]='Group';
		$title[]='Htdocs Dir';
		$title[]='CGI Dir';
		$title[]='Operations';

		print '
			<form name="update" method="post" action="'.$PHP_SELF.'">

			'.$GLOBALS['HTML']->listTableTop($title).'
			<tr><td>'.$row_vh['vhostid'].'</td>
			<td>'.$row_vh['vhost_name'].'</td>
			<td><a href="/projects/'.$row_vh['unix_group_name'].'">'.$row_vh['unix_group_name'].'</a></td>
			<td><input maxlength="255" type="text" name="docdir" value="'.$row_vh['docdir'].'" /></td>
			<td><input type="text" name="cgidir" value="'.$row_vh['cgidir'].'" /></td><td><input maxlength="255" type="submit" value="Update" /></tr>
			'.$GLOBALS['HTML']->listTableBottom().'

			<input type="hidden" name="tweakcommit" value="1" />
			<input type="hidden" name="vhostid" value="'.$row_vh['vhostid'].'" />
			</form>
		';
	} else {
		echo "No such VHOST: '$vhost_name'";
	}

}

site_admin_footer(array());

?>
