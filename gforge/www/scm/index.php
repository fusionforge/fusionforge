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

//only projects can use cvs, and only if they have it turned on
$project =& group_get_object($group_id);

if (!$project->isProject()) {
 	exit_error('Error','Only Projects Can Use CVS');
}
if (!$project->usesCVS()) {
	exit_error('Error','This Project Has Turned Off CVS');
}

site_project_header(array('title'=>'CVS Repository','group'=>$group_id,'toptab'=>'cvs','pagename'=>'cvs','sectionvals'=>array($project->getPublicName())));


// ######################## table for summary info

print '<table width="100%"><tr valign="top"><td width="65%">'."\n";

// ######################## anonymous CVS instructions

if ($project->enableAnonCVS()) {
	print $Language->getText('cvs', 'anoncvs').
	'<p><tt>cvs -d:pserver:anonymous@'.$sys_cvs_host.':/cvsroot/'.
	$project->getUnixName().
	' login<br />&nbsp;<br />cvs -z3 -d:pserver:anonymous@'.
	$sys_cvs_host.':/cvsroot/'.
	$project->getUnixName().' co <em>modulename</em></tt></p><p>'.
	$Language->getText('cvs', 'anoncvsup').'</p>';
}
else {
        print $Language->getText('cvs','noanoncvs');
}
// ############################ developer access

print $Language->getText('cvs', 'devcvs').' 

<p><tt>export CVS_RSH=ssh
<br />&nbsp;<br />cvs -z3 -d:ext:<em>developername</em>@'.$sys_cvs_host.':/cvsroot/'.$project->getUnixName().' co <em>modulename</em></tt></p>';

// ################## summary info

print '</td><td width="35%">';
print $HTML->boxTop($Language->getText('cvs', 'history'));

// ################ is there commit info?

$res_cvshist = db_query("SELECT * FROM group_cvs_history WHERE group_id='$group_id'");
if (db_numrows($res_cvshist) < 1) {
	//print '<p>This project has no CVS history.</p>';
} else {

print '<p><strong>Developer (30 day/Commits) (30 day/Adds)</strong><br />&nbsp;';

while ($row_cvshist = db_fetch_array($res_cvshist)) {
	print '<br />'.$row_cvshist['user_name'].' ('.$row_cvshist['cvs_commits_wk'].'/'
		.$row_cvshist['cvs_commits'].') ('.$row_cvshist['cvs_adds_wk'].'/'
		.$row_cvshist['cvs_adds'].')';
}

} // ### else no cvs history

// ############################## CVS Browsing

$anonymous = 1;
if (session_loggedin()) {
        $perm =& $project->getPermission(session_get_user());
	$anonymous = !$perm->isMember();
}

if ($project->enableAnonCVS() || !$anonymous) {
	print $Language->getText('cvs', 'browsetree').' 
<ul>
<li><a href="'.account_group_cvsweb_url($project->getUnixName()).'">
<strong>'.$Language->getText('cvs', 'browseit').'</strong></a>';
}

print $HTML->boxBottom();

print '</td></tr></table>';

site_project_footer(array());

?>
