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
 	exit_error('Error','Only Projects Can Use CVS');
}
if (!$project->usesCVS()) {
	exit_error('Error','This Project Has Turned Off CVS');
}

site_project_header(array('title'=>'CVS Repository','group'=>$group_id,'toptab'=>'cvs','pagename'=>'cvs','sectionvals'=>array($project->getPublicName())));

$res_grp = db_query("SELECT * FROM groups WHERE group_id='$group_id'");

$row_grp = db_fetch_array($res_grp);

// ######################## table for summary info

print '<TABLE width="100%"><TR valign="top"><TD width="65%">'."\n";

// ######################## anonymous CVS instructions

if ($row_grp['is_public']) {
	print $Language->getText('cvs', 'anoncvs').' 

<P><tt>cvs -d:pserver:anonymous@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' login
<BR>&nbsp;<BR>
cvs -z3 -d:pserver:anonymous@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' co <I>modulename</I>
</tt>

 
<P>'.$Language->getText('cvs', 'anoncvsup');
}

// ############################ developer access

print $Language->getText('cvs', 'devcvs').' 

<P><tt>export CVS_RSH=ssh
<BR>&nbsp;<BR>cvs -z3 -d:ext:<I>developername</I>@cvs.'.$row_grp['unix_group_name'].'.'.$GLOBALS['sys_default_domain'].':/cvsroot/'.$row_grp['unix_group_name'].' co <I>modulename</I>
</tt>';

// ################## summary info

print '</TD><TD width="35%">';
print $HTML->boxTop($Language->getText('cvs', 'history'));

// ################ is there commit info?

$res_cvshist = db_query("SELECT * FROM group_cvs_history WHERE group_id='$group_id'");
if (db_numrows($res_cvshist) < 1) {
	//print '<P>This project has no CVS history.';
} else {

print '<P><B>Developer (30 day/Commits) (30 day/Adds)</B><BR>&nbsp;';

while ($row_cvshist = db_fetch_array($res_cvshist)) {
	print '<BR>'.$row_cvshist['user_name'].' ('.$row_cvshist['cvs_commits_wk'].'/'
		.$row_cvshist['cvs_commits'].') ('.$row_cvshist['cvs_adds_wk'].'/'
		.$row_cvshist['cvs_adds'].')';
}

} // ### else no cvs history

// ############################## CVS Browsing

if ($row_grp['is_public']) {
	print $Language->getText('cvs', 'browsetree').' 
<UL>
<LI><A href="'.account_group_cvsweb_url($row_grp['unix_group_name']).'">
<B>'.$Language->getText('cvs', 'browseit').'</B></A>';
}

print $HTML->boxBottom();

print '</TD></TR></TABLE>';

site_project_footer(array());

?>
