<?php 

/** 
* project_home.php 
* 
* SourceForge: Breaking Down the Barriers to Open Source Development 
* Copyright 1999-2001 (c) VA Linux Systems 
* http://sourceforge.net 
* 
* @version   $Id$ 
*/

require_once('www/news/news_utils.php');
require_once('www/include/trove.php');
require_once('www/include/project_summary.php');

$title = _('Project Info');

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'home'));


// ########################################### end top area

// two column deal
?>

<table width="100%" border="0">
	<tr>
		<td width="80%" valign="top">
		<?php

// ########################################## top area, not in box
$res_admin = db_query("SELECT users.user_id,users.user_name,users.realname,user_group.admin_flags
	FROM users,user_group
	WHERE user_group.user_id=users.user_id
	AND user_group.group_id='$group_id'
	AND users.status='A'
	ORDER BY admin_flags DESC,realname");

if ($project->getStatus() == 'H') {
	print "<p>".sprintf(_('NOTE: This project entry is maintained by the %1$s staff. We are not the official site for this product. Additional copyright information may be found on this project\'s homepage.'), $GLOBALS['sys_name'])."</p>\n";
}

if ($project->getDescription()) {
	print "<p>" . nl2br($project->getDescription()) . '</p>';
} else {
	print "<p>" . _('This project has not yet submitted a description.') . '</p>';
}

// trove info
print "<br />\n";
print stripslashes(trove_getcatlisting($group_id,0,1));

// registration date
print(_('Registered:&nbsp;') . date($sys_datefmt, $project->getStartDate()));

// Get the activity percentile
// CB hide stats if desired
if ($project->usesStats()) {
	$actv = db_query("SELECT percentile FROM project_weekly_metric WHERE group_id='$group_id'");
	$actv_res = db_result($actv,0,"percentile");
	if (!$actv_res) {
		$actv_res=0;
	}
	print '<br />'._('Activity Percentile:&nbsp;'). substr($actv_res, 0, 5). '%';
	print '<br />'.sprintf(_('View project <a href="/project/stats/?group_id=%1$s">Statistics</a> or <a href="/project/report/?group_id=%1$s">Activity</a>.'), $group_id);
	print '<br />'.sprintf(_('View list of <a href="/export/rss_project.php?group_id=%1$s">RSS feeds</a> available for this project'), $group_id). '&nbsp;' . html_image('ic/rss.png',16,16,array('border'=>'0'));
}

if($GLOBALS['sys_use_people']) {
	$jobs_res = db_query("SELECT name 
					FROM people_job,people_job_category 
					WHERE people_job.category_id=people_job_category.category_id 
					AND people_job.status_id=1 
					AND group_id='$group_id' 
					GROUP BY name",2);
	if ($jobs_res) {
		$num=db_numrows($jobs_res);
			if ($num>0) {
				print '<br /><br />'._('HELP WANTED: This project is looking for').'  ';
					if ($num==1) {
						print '<a href="'.$GLOBALS['sys_urlprefix'].'/people/?group_id='.$group_id.'">'. db_result($jobs_res,0,"name").'(s)</a>';
					} else {
						print $Language->getText('project_home','help_wanted_multiple', '<a href="'.$GLOBALS['sys_urlprefix'].'/people/?group_id='.$group_id.'">').' </a>';
					}
			}
	}
}
plugin_hook ("project_after_description",false) ;

?>
		</td>
		<td nowrap="nowrap" valign="top" width="20%">

<?php

// ########################### Developers on this project

echo $HTML->boxTop(_('Developer Info'));

if (db_numrows($res_admin) > 0) {

	?>
	<span class="develtitle"><?php echo _('Project Admins'); ?>:</span><br />
	<?php
		while ($row_admin = db_fetch_array($res_admin)) {
			if (trim($row_admin['admin_flags']) != 'A' && !$started_developers) {
				$started_developers=true;
				echo '<span class="develtitle">'. _('Developers').':</span><br />';
			}
			echo '<a href="'.$GLOBALS['sys_urlprefix'].'/users/'.$row_admin['user_name'].'/">'.$row_admin['realname'].'</a><br />';
		}
	?>
	<hr width="100%" size="1" />
	<?php

}

?>

<p><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/project/memberlist.php?group_id=<?php print $group_id; ?>">[<?php echo _('View Members') ?>]</a></p>
<p><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/project/request.php?group_id=<?php print $group_id; ?>">[<?php echo _('Request to join'); ?>]</a></p>
<?php

echo $HTML->boxBottom();

?>
		</td>
	</tr>
</table>

<br />

<?php

// ############################# File Releases

// CB hide FRS if desired
if ($project->usesFRS()) {
	echo $HTML->boxTop(_('Latest File Releases'));
	$unix_group_name = $project->getUnixName();

	echo '
	<table cellspacing="1" cellpadding="5" width="100%" border="0">
		<tr>
		<td align="left">
			'._('Package').'
		</td>
		<td align="center">
			'._('Version').'
		</td>
		<td align="center">
			'._('Date').'
		</td>
		<td align="center">
			'._('Notes').' / '._('Notes').'
		</td>
		<td align="center">
			'._('Download').'
		</td>
		</tr>';

		//
		//  Members of projects can see all packages
		//  Non-members can only see public packages
		//
		if (session_loggedin()) {
			if (user_ismember($group_id) || user_ismember(1,'A')) {
				$pub_sql='';
			} else {
				$pub_sql=' AND frs_package.is_public=1 ';
			}
		} else {
			$pub_sql=' AND frs_package.is_public=1 ';
		}

		$sql="SELECT frs_package.package_id,frs_package.name AS package_name,frs_release.name AS release_name,frs_release.release_id AS release_id,frs_release.release_date AS release_date 
			FROM frs_package,frs_release 
			WHERE frs_package.package_id=frs_release.package_id 
			AND frs_package.group_id='$group_id' 
			AND frs_release.status_id=1 
			$pub_sql
			ORDER BY frs_package.package_id,frs_release.release_date DESC";

		$res_files = db_query($sql);
		$rows_files=db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No releases
			echo '<tr><td colspan="5"><strong>'._('This Project Has Not Released Any Files').'</strong></td></tr>';

		} else {
			/*
				This query actually contains ALL releases of all packages
				We will test each row and make sure the package has changed before printing the row
			*/
			for ($f=0; $f<$rows_files; $f++) {
				if (db_result($res_files,$f,'package_id')==db_result($res_files,($f-1),'package_id')) {
					//same package as last iteration - don't show this release
				} else {
					$rel_date = getdate(db_result($res_files,$f,'release_date'));
					echo '
					<tr align="center">
					<td align="left">
					<strong>' . db_result($res_files,$f,'package_name'). '</strong></td>';
					// Releases to display
					print '<td>'.db_result($res_files,$f,'release_name') .'
					</td>
					<td>' . $rel_date["month"] . ' ' . $rel_date["mday"] . ', ' . $rel_date["year"] . '</td>
					<td><a href="'.$GLOBALS['sys_urlprefix'].'/frs/shownotes.php?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id') . '">';
					echo html_image('ic/manual16c.png','15','15',array('alt'=>_('Release Notes')));
					echo '</a> - <a href="'.$GLOBALS['sys_urlprefix'].'/frs/monitor.php?filemodule_id=' .	db_result($res_files,$f,'package_id') . '&amp;group_id='.$group_id.'&amp;start=1">';
					echo html_image('ic/mail16d.png','15','15',array('alt'=>_('Monitor this package')));
					echo '</a>
					</td>
					<td><a href="'.$GLOBALS['sys_urlprefix'].'/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id') . '">'._('Download').'</a></td></tr>';
				}
			}

		}
		?></table>
	<div align="center">
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/frs/?group_id=<?php print $group_id; ?>">[<?php echo _('View All Project Files')?>]</a>
	</div>
<?php
	echo $HTML->boxBottom();
}

?>
<p />
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top" width="50%">

<?php

// ############################## PUBLIC AREAS
echo $HTML->boxTop(_('Public Areas'));

// ################# Homepage Link

print '<a href="http://' . $project->getHomePage() . '">';
print html_image('ic/home16b.png','20','20',array('alt'=>_('Home Page')));
print '&nbsp;'._('Project Home Page').'</a>';

// ################## ArtifactTypes

// CB hide tracker if desired
if ($project->usesTracker()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?group_id='.$group_id.'">';
	print html_image('ic/tracker20g.png','20','20',array('alt'=>_('Tracker')));
	print _('Tracker').'</a>';

	$result=db_query("SELECT agl.*,aca.count,aca.open_count
	FROM artifact_group_list agl
	LEFT JOIN artifact_counts_agg aca USING (group_artifact_id)
	WHERE agl.group_id='$group_id'
	AND agl.is_public=1
	ORDER BY group_artifact_id ASC");

	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<br /><em>'._('There are no public trackers available').'</em>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '<p />
		&nbsp;-&nbsp;<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?atid='. db_result($result, $j, 'group_artifact_id') .
		'&amp;group_id='.$group_id.'&amp;func=browse">'. db_result($result, $j, 'name') .'</a>
		( '.sprintf(_('<strong>%1$s</strong> open / <strong>%2$s</strong> total'), (int) db_result($result, $j, 'open_count'), (int) db_result($result, $j, 'count')) .' )<br />'.
		db_result($result, $j, 'description');
		}
	}
}

// ################## forums

if ($project->usesForum()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/forum/?group_id='.$group_id.'">';
	print html_image('ic/forum20g.png','20','20',array('alt'=>_('Forums')));
	print '&nbsp;'._('Forums').'</a>';
	$forums_count = project_get_public_forum_count($group_id);
	$messages_count = project_get_public_forum_message_count($group_id);
	print ' (<strong> ' . $forums_count .'</strong> ';
	printf(ngettext("public forum","public forums",$forums_count),$forums_count);
	print ' / <strong> ' . $messages_count .'</strong> ';
	printf(ngettext("message","messages",$forums_count),$forums_count);
	print ' )' ;
}

// ##################### Doc Manager

if ($project->usesDocman()) {
	print '
	<hr size="1" />
	<a href="'.$GLOBALS['sys_urlprefix'].'/docman/?group_id='.$group_id.'">';
	print html_image('ic/docman16b.png','20','20',array('alt'=>_('Docs')));
	print '&nbsp;'._('DocManager: Project Documentation').'</a>';
}

// ##################### Mailing lists

if ($project->usesMail()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/mail/?group_id='.$group_id.'">';
	print html_image('ic/mail16b.png','20','20',array('alt'=>_('Lists')));
	print '&nbsp;'._('Mailing Lists').'</a>';
	print " ( <strong>". project_get_mail_list_count($group_id) ."</strong> "._('public mailing lists')." )";
}

// ##################### Task Manager

if ($project->usesPm()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/pm/?group_id='.$group_id.'">';
	print html_image('ic/taskman20g.png','20','20',array('alt'=>_('Tasks')));
	print '&nbsp;'._('Task Manager').'</a>';
	$sql="SELECT * FROM project_group_list WHERE group_id='$group_id' AND is_public=1";
	$result = db_query ($sql);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<br /><em>'._('There are no public subprojects available').'</em>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '
			<br /> &nbsp; - <a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
			'&amp;group_id='.$group_id.'&amp;func=browse">'.db_result($result, $j, 'project_name').'</a>';
		}

	}
}

// ######################### Surveys

if ($project->usesSurvey()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/survey/?group_id='.$group_id.'">';
	print html_image('ic/survey16b.png','20','20',array('alt'=>_('Surveys')));
	print " "._('Surveys')."</a>";
	echo ' ( <strong>'. project_get_survey_count($group_id) .'</strong> '._('surveys').'  )';
}

// ######################### SCM

if ($project->usesSCM()) {
	print '<hr size="1" /><a href="'.$GLOBALS['sys_urlprefix'].'/scm/?group_id='.$group_id.'">';
	print html_image('ic/cvs16b.png','20','20',array('alt'=>_('SCM')));
	print " "._('SCM Repository')."</a>";

	/*
	$result = db_query("
		SELECT sum(commits) AS commits,sum(adds) AS adds
		FROM stats_cvs_group
		WHERE group_id='$group_id'
	", -1, 0, SYS_DB_STATS);
	$cvs_commit_num = db_result($result,0,0);
	$cvs_add_num	= db_result($result,0,1);
	if (!$cvs_commit_num) {
		$cvs_commit_num=0;
	}
	if (!$cvs_add_num) {
		$cvs_add_num=0;
	}
	*/
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("scm_stats", $hook_params) ;
	
}

// ######################## AnonFTP

// CB hide FTP if desired
if ($project->usesFTP()) {
	if ($project->isActive()) {
		print '<hr size="1" />';
		print '<a href="ftp://' . $project->getUnixName() . '.' . $GLOBALS['sys_default_domain'] . '/pub/'. $project->getUnixName() .'/">';
		print html_image('ic/ftp16b.png','20','20',array('alt'=>_('Anonymous FTP Space')));
		print _('Anonymous FTP Space')."</a>";
	}
}

//webcalendar
plugin_hook("cal_link_group",$group_id);
echo $HTML->boxBottom();

if ($project->usesNews()) {
	// COLUMN BREAK
	?>

		</td>
		<td width="15">&nbsp;</td>
		<td valign="top" width="50%">

	<?php
	// ############################# Latest News

	echo $HTML->boxTop(_('Latest News'));

	echo news_show_latest($group_id,10,false);

	echo $HTML->boxBottom();
}

//
//	Linked projects (hierarchy)
//

plugin_hook('project_home_link',$group_id);

?>
		</td>
	</tr>
</table>

<?php

site_project_footer(array());

?>
