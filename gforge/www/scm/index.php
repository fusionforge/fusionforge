<?php
/**
  *
  * SourceForge CVS Frontend
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

//only projects can use the bug tracker, and only if they have it turned on
$project =& group_get_object($group_id);

if (!$project->isProject()) {
 	exit_error($Language->getText('scm_index','error_only_projects_can_use_cvs'));
}
if (!$project->usesCVS()) {
	exit_error($language->getText('scm_index','error_this_project_has_turned_off'));
}

site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$group_id,'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($project->getPublicName())));

$res_grp = db_query("SELECT * FROM groups WHERE group_id='$group_id'");

$row_grp = db_fetch_array($res_grp);

// ######################## table for summary info

print '<table width="100%"><tr valign="top"><td width="65%">'."\n";

// ######################## anonymous CVS instructions

if ($row_grp['is_public']) {
  if($GLOBALS['sys_cvs_single_host']) {
    print $Language->getText('scm_index', 'anoncvs').' 
    <p><tt>cvs -d:pserver:anonymous@' . $GLOBALS['sys_cvs_host'] . ':/cvsroot/'.$row_grp['unix_group_name'].' login <br><br>
cvs -z3 -d:pserver:anonymous@' . $GLOBALS['sys_cvs_host'] . ':/cvsroot/'.$row_grp['unix_group_name'].' co  <i>'.$Language->getText('scm_index','modulename').'</i>
</tt>


<p>'.$Language->getText('scm_index', 'anoncvsup');
  } else {
    print $Language->getText('scm_index', 'anoncvs').'

<p><tt>cvs -d:pserver:anonymous@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' login <br><br>
cvs -z3 -d:pserver:anonymous@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' co <i>'.$Language->getText('scm_index','modulename').'</i>
</tt>

 
<p>'.$Language->getText('scm_index', 'anoncvsup');
  }
}

// ############################ developer access

if($GLOBALS['sys_cvs_single_host']) {

print $Language->getText('scm_index', 'devcvs').'

<p><tt>export CVS_RSH=ssh
<br><br>cvs -z3 -d:ext:<i>'.$Language->getText('scm_index','developername').'</i>@'.$GLOBALS['sys_cvs_host'].':/cvsroot/'.$row_grp['unix_group_name'].' co <i>'.$Language->getText('scm_index','modulename').'</i>
</tt>';

} else {

print $Language->getText('scm_index', 'devcvs').' 

<p><tt>export CVS_RSH=ssh
<br><br>cvs -z3 -d:ext:<i>'.$Language->getText('scm_index','developername').'</i>@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' co <i>'.$Language->getText('scm_index','modulename').'</i>
</tt>';
}
// ################## summary info

print '</td><td width="35%">';
print $HTML->boxTop($Language->getText('scm_index', 'history'));

// ################ is there commit info?

$res_cvshist = db_query("SELECT * FROM group_cvs_history WHERE group_id='$group_id'");
if (db_numrows($res_cvshist) < 1) {
	//print '<p>This project has no CVS history.';
} else {

print '<p><b>'.$Language->getText('scm_index','developer_commits_adds').'</b><br>&nbsp;';

while ($row_cvshist = db_fetch_array($res_cvshist)) {
	print '<br>'.$row_cvshist['user_name'].' ('.$row_cvshist['cvs_commits_wk'].'/'
		.$row_cvshist['cvs_commits'].') ('.$row_cvshist['cvs_adds_wk'].'/'
		.$row_cvshist['cvs_adds'].')';
}

} // ### else no cvs history

// ############################## CVS Browsing

if ($row_grp['is_public']) {
	print $Language->getText('scm_index', 'browsetree').' 
<UL>
<li><a href="'.account_group_cvsweb_url($row_grp['unix_group_name']).'">
<b>'.$Language->getText('scm_index', 'browseit').'</b></a></li>';
}

print $HTML->boxBottom();

print '</td></tr></table>';

site_project_footer(array());

?>
