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
 	exit_error($Language->getText('scm_index','error_only_projects_can_use_cvs'));
}
if (!$project->usesCVS()) {
	exit_error($Language->getText('scm_index','error_this_project_has_turned_off'));
}

site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$group_id,'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($project->getPublicName())));

if($GLOBALS['sys_cvs_single_host']) {
	$cvsrootend=$GLOBALS['sys_cvs_host'].':/cvsroot/'.$project->getUnixName();
} else {
	$cvsrootend='cvs.'.$project->getUnixName().'.'.$GLOBALS['sys_cvs_host'].':/cvsroot/'.$project->getUnixName();
}
// ######################## table for summary info
?>

<table width="100%">
	<tr valign="top">
		<td width="65%">

<?php
// ######################## anonymous CVS instructions
if ($project->enableAnonCVS() && $project->enablePserver()) {
?>
		<?php echo $Language->getText('scm_index', 'anoncvs'); ?>
     			<p>
			<tt>cvs -d:pserver:anonymous@<?php echo $cvsrootend; ?> login 
			<br>
			<br>
			cvs -z3 -d:pserver:anonymous@<?php echo $cvsrootend; ?> co  <i>modulename</i>
			</tt>
			<p>
		<?php echo $Language->getText('scm_index', 'anoncvsup'); ?>
<?php
}
?>

<?php
// ############################ developer access
?>

		<? echo $Language->getText('scm_index', 'devcvs'); ?>

			<p>
			<tt>export CVS_RSH=ssh
			<br>
			<br>cvs -z3 -d:ext:<i>developername</i>@<?php echo $cvsrootend; ?> co <i>modulename</i>
			</tt>
			<p>

<?php
// ################## summary info
?>

		</td>
		<td width="35%">
		<?php echo $HTML->boxTop($Language->getText('scm_index', 'history')); ?>

<?php
// ################ is there commit info?
$res_cvshist = db_query("SELECT * FROM group_cvs_history WHERE group_id='$group_id'");
if (db_numrows($res_cvshist) < 1) {
?>
		<p>
		<?php echo $Language->getText('scm_index', 'nohistory'); ?>
<?php
} else {
?>
		<p>
		<b><?php echo $Language->getText('scm_index','developer_commits_adds'); ?></b>
		<br>&nbsp;

<?php
	while ($row_cvshist = db_fetch_array($res_cvshist)) {
?>
		<br><? print $row_cvshist['user_name'].' ('.$row_cvshist['cvs_commits_wk'].'/'
		.$row_cvshist['cvs_commits'].') ('.$row_cvshist['cvs_adds_wk'].'/'
		.$row_cvshist['cvs_adds'].')'; ?>
<?php
	}

} // ### else no cvs history

// ############################## CVS Browsing

$anonymous = 1;
if (session_loggedin()) {
   $perm =& $project->getPermission(session_get_user());
   $anonymous = !$perm->isMember();
}
 
if ($project->enableAnonCVS() || !$anonymous) {
	echo $Language->getText('scm_index', 'browsetree');
?>
		<UL>
		<LI><a href=<?php print account_group_cvsweb_url($project->getUnixName()); ?> >
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?>(cvsweb)</b></a>
		</UL>
		<UL>
		<LI>Ronald Petty contrib<BR><a href=/scm/controller.php?group_id=<?php echo $group_id; ?> >
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (php)</b></a>
		</UL>
		<UL>
		<LI>Dragos Moinescu contrib<BR><a href="/scm/controlleroo.php?group_id=<?php echo $group_id; ?>&hide_attic=0" >
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (php OO)</b></a>
		</UL>
<?php
}

echo $HTML->boxBottom();

?>
		</td>
	</tr>
</table>

<?php site_project_footer(array()); ?>
