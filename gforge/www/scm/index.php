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

if (!$sys_use_cvs) {
	exit_disabled();
}

//only projects can use cvs, and only if they have it turned on
$project =& group_get_object($group_id);

if (!$project->isProject()) {
 	exit_error('Error',$Language->getText('scm_index','error_only_projects_can_use_cvs'));
}
if (!$project->usesCVS()) {
	exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
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
			<br />
			<br />
			cvs -z3 -d:pserver:anonymous@<?php echo $cvsrootend; ?> co  <i>modulename</i>
			</tt>
		</p>
		<?php echo $Language->getText('scm_index', 'anoncvsup'); ?>
<?php
}
?>

<?php
// ############################ developer access
?>
		<?php echo $Language->getText('scm_index', 'devcvs'); ?>
		<p>
			<tt>export CVS_RSH=ssh
			<br />
			<br />cvs -z3 -d:ext:<i>developername</i>@<?php echo $cvsrootend; ?> co <i>modulename</i>
			</tt>
		</p>
<?php
// ################## summary info
?>
		</td>
		<td width="35%">
		<?php echo $HTML->boxTop($Language->getText('scm_index', 'history')); ?>
<?php
// ######################### CVS

$result = db_query("
	SELECT u.realname, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined
	FROM stats_cvs_user s, users u
	WHERE group_id='$group_id' AND s.user_id=u.user_id AND (commits>0 OR adds >0)
	GROUP BY group_id, realname
	ORDER BY combined DESC, realname;
");

if (db_numrows($result) > 0) {
	print '<hr size="1" noshade="noshade" />';

	$headerMapping = array(
	'realname' => array("Name", 'width="60%"'),
	'adds' 	=> array("Adds", 'width="13%"'),
	'commits' => array("Commits", 'width="13%"')
	);
	ShowResultSet($result,'', false, true, $headerMapping, array('combined'));
}
else {
	echo $Language->getText('scm_index', 'nohistory');
}

// ############################## CVS Browsing

$anonymous = 1;
if (session_loggedin()) {
   $perm =& $project->getPermission(session_get_user());
   $anonymous = !$perm->isMember();
}
 
if ($project->enableAnonCVS() || !$anonymous) {
	echo $Language->getText('scm_index', 'browsetree');
?>
	<ul>
		<li>Christian Bayle did it<br /><a href="/scm/cvsweb.php/?cvsroot=<?php print $project->getUnixName(); ?>">
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (cvsweb php wrapper)</b></a>
		</li>
		<li><a href="<?php print account_group_cvsweb_url($project->getUnixName()); ?>">
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (cvsweb)</b></a>
		</li>
		<li>Ronald Petty contrib<br /><a href="/scm/controller.php?group_id=<?php echo $group_id; ?>">
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (php)</b></a>
		</li>
		<li>Dragos Moinescu contrib<br /><a href="/scm/controlleroo.php?group_id=<?php echo $group_id; ?>&amp;hide_attic=0">
			<b><?php echo $Language->getText('scm_index', 'browseit'); ?> (php OO)</b></a>
		</li>
	</ul>
<?php
}

echo $HTML->boxBottom();

?>
		</td>
	</tr>
</table>

<?php site_project_footer(array()); ?>