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
			$feedback .= $Language->getText('project_admin_vhost','vhost_scheduled');
			$group->addHistory('Added vhost '.$vhost_name.' ','');
		}

	} else {

		$feedback .= $Language->getText('project_admin_vhost','not_valid_hostname',array($vhost_name));

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
		$feedback .= $Language->getText('project_admin_vhost','vhost_deleted');
		$group->addHistory('Virtual Host '.$row_vh['vhost_name'].' Removed','');

	}

}

project_admin_header(array('title'=>$Language->getText('project_admin_vhost','title'),'group'=>$group->getID(),'pagename'=>'project_admin_vhost','sectionvals'=>array(group_getname($group_id))));

?>

<p>&nbsp;</p>

<?php echo $Language->getText('project_admin_vhost','info', array($group->getUnixName(),$GLOBALS['sys_default_domain'],$GLOBALS['sys_name'],$group->getUnixName(),$GLOBALS['sys_default_domain']   )) ?>
<p>

<form name="new_vhost" action="<?php echo $PHP_SELF.'?group_id='.$group->getID().'&createvhost=1'; ?>" method="post"> 
<table border="0">
<tr>
	<td> <?php echo $Language->getText('project_admin_vhost','name') ?> </td>
	<td> <input type="text" size="15" maxlength="255" name="vhost_name" /> </td>
	<td> <input type="submit" value="<?php echo $Language->getText('project_admin_vhost','create') ?>" /> </td>
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
	$title[]=$Language->getText('project_admin_vhost','vhost');
	$title[]=$Language->getText('project_admin_vhost','operations');
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '	<tr>
			<td>'.$row_db['vhost_name'].'</td>
			<td>[ <strong><a href="'.$PHP_SELF.'?group_id='.$group->getID().'&amp;vhostid='.$row_db['vhostid'].'&amp;deletevhost=1">'.$Language->getText('project_admin_vhost','delete').'</a></strong>]
			</tr>	
		';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>'.$Language->getText('project_admin_vhost','no_vhosts').'No VHOSTs defined</p>';
}

project_admin_footer(array());

?>
