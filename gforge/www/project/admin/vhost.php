<?php
/**
  *
  * Project Admin page to manage group's VHOST entries
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/vars.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group = &group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_error('Error','Error creating group object');
} else if ($group->isError()) {
        exit_error('ERROR',$group->getErrorMessage());
}

if ($createvhost) {

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
			$feedback .= "Virtual Host scheduled for creation.";
			$group->addHistory('Added vhost '.$vhost_name.' ','');
		}

	} else {

		$feedback .= "Not a valid hostname - $vhost_name"; 

	}
}


if ($deletevhost) {

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
		$feedback .= "VHOST deleted";	
		$group->addHistory('Virtual Host '.$row_vh['vhost_name'].' Removed','');

	}

}

project_admin_header(array('title'=>'Editing Virtual Host Info','group'=>$group->getID(),'pagename'=>'project_admin_vhost','sectionvals'=>array(group_getname($group_id))));

?>

<p>

<b><u>Add New Virtual Host</u></b>
<p>
To add a new virtual host - simply point a <b>CNAME</b> for <i>yourhost.org</i> at
<b><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></b>.  <?php echo $GLOBALS['sys_name']; ?> does not currently host mail (i.e. cannot be an MX)
or DNS</b>.  
<p>
Clicking on "create" will schedule the creation of the Virtual Host.  This will be
synced to the project webservers - such that <i>yourhost.org</i> will display the 
material at <i><?php echo $group->getUnixName().'.'.$GLOBALS['sys_default_domain']; ?></i>.

<p>

<form name="new_vhost" action="<?php echo $PHP_SELF.'?group_id='.$group->getID().'&createvhost=1'; ?>" method="post"> 
<table border = 0>
<tr>
	<td> New Virtual Host <i>(e.g. vhost.org)</i> </td>
	<td> <input type="text" size="15" maxlength="255" name="vhost_name"> </td>
	<td> <input type="submit" value="Create"> </td>
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
	$title[]='Virtual Host';
	$title[]='Operations';
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '	<tr>
			<td>'.$row_db['vhost_name'].'</td>
			<td>[ <b><a href="'.$PHP_SELF.'?group_id='.$group->getID().'&vhostid='.$row_db['vhostid'].'&deletevhost=1">Delete</a> </b>]
			</tr>	
		';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>No VHOSTs defined</p>';
}

project_admin_footer(array());

?>
