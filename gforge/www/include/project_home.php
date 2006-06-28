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

$title = $Language->getText('project_home','title');

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
	print "<p>".$Language->getText('project_home','holding_note',$GLOBALS['sys_name'])."</p>\n";
}

if ($project->getDescription()) {
	print "<p>" . $project->getDescription() . '</p>';
} else {
	print "<p>" . $Language->getText('group', 'nodescription') . '</p>';
}

// trove info
print "<br />\n";
print stripslashes(trove_getcatlisting($group_id,0,1));

// registration date
print($Language->getText('group', 'registered') . date($sys_datefmt, $project->getStartDate()));

// Get the activity percentile
// CB hide stats if desired
if ($project->usesStats()) {
	$actv = db_query("SELECT percentile FROM project_weekly_metric WHERE group_id='$group_id'");
	$actv_res = db_result($actv,0,"percentile");
	if (!$actv_res) {
		$actv_res=0;
	}
	print '<br />'.$Language->getText('group', 'activity'). substr($actv_res, 0, 5). '%';
	print '<br />'.$Language->getText('group', 'activitystat', $group_id);
	print '<br />'.$Language->getText('group', 'rssfeeds', $group_id). '&nbsp;' . html_image('ic/rss.png',16,16,array('border'=>'0'));
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
				print '<br /><br />'.$Language->getText('project_home','help_wanted').'  ';
					if ($num==1) {
						print '<a href="/people/?group_id='.$group_id.'">'. db_result($jobs_res,0,"name").'(s)</a>';
					} else {
						print $Language->getText('project_home','help_wanted_multiple', '<a href="/people/?group_id='.$group_id.'">').' </a>';
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

echo $HTML->boxTop($Language->getText('group','developer_info'));

if (db_numrows($res_admin) > 0) {

	?>
	<span class="develtitle"><?php echo $Language->getText('group','project_admins'); ?>:</span><br />
	<?php
		while ($row_admin = db_fetch_array($res_admin)) {
			if (trim($row_admin['admin_flags']) != 'A' && !$started_developers) {
				$started_developers=true;
				echo '<span class="develtitle">'. $Language->getText('group','project_developers').':</span><br />';
			}
			echo '<a href="/users/'.$row_admin['user_name'].'/">'.$row_admin['realname'].'</a><br />';
		}
	?>
	<hr width="100%" size="1" />
	<?php

}

?>

<p><a href="/project/memberlist.php?group_id=<?php print $group_id; ?>">[<?php echo $Language->getText('project_home','view_members') ?>]</a></p>
<p><a href="/project/request.php?group_id=<?php print $group_id; ?>">[<?php echo $Language->getText('project_joinrequest','request_link'); ?>]</a></p>
<?php

echo $HTML->boxBottom();

?>
		</td>
	</tr>
</table>
<p>&nbsp;</p>
<?php

// ############################# File Releases

// CB hide FRS if desired
if ($project->usesFRS()) {
	echo $HTML->boxTop($Language->getText('frs','latest_file_releases'));
	$unix_group_name = $project->getUnixName();

	echo '
	<table cellspacing="1" cellpadding="5" width="100%" border="0">
		<tr>
		<td align="left">
			'.$Language->getText('frs','file_package').'
		</td>
		<td align="center">
			'.$Language->getText('frs','file_version').'
		</td>
		<td align="center">
			'.$Language->getText('frs','file_rel_date').'
		</td>
		<td align="center">
			'.$Language->getText('frs','file_notes').' / '.$Language->getText('frs','file_monitor').'
		</td>
		<td align="center">
			'.$Language->getText('frs','file_download').'
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
			echo '<tr><td colspan="5"><strong>'.$Language->getText('group', 'norelease').'</strong></td></tr>';

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
					<td><a href="/frs/shownotes.php?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id') . '">';
					echo html_image('ic/manual16c.png','15','15',array('alt'=>$Language->getText('project_home','release_notes')));
					echo '</a> - <a href="/frs/monitor.php?filemodule_id=' .	db_result($res_files,$f,'package_id') . '&amp;group_id='.$group_id.'&amp;start=1">';
					echo html_image('ic/mail16d.png','15','15',array('alt'=>$Language->getText('project_home','monitor_package')));
					echo '</a>
					</td>
					<td><a href="/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id') . '">'.$Language->getText('frs','file_download').'</a></td></tr>';
				}
			}

		}
		?></table>
	<div align="center">
	<a href="/frs/?group_id=<?php print $group_id; ?>">[<?php echo $Language->getText('project_home','view_project_files')?>]</a>
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
echo $HTML->boxTop($Language->getText('group','public_area'));

// ################# Homepage Link

print "<a href=\"http://" . $project->getHomePage() . "\">";
print html_image('ic/home16b.png','20','20',array('alt'=>$Language->getText('group','short_homepage')));
print '&nbsp;'.$Language->getText('group','long_homepage').'</a>';

// ################## ArtifactTypes

// CB hide tracker if desired
if ($project->usesTracker()) {
	print '<hr size="1" /><a href="/tracker/?group_id='.$group_id.'">';
	print html_image('ic/tracker20g.png','20','20',array('alt'=>$Language->getText('group','short_tracker')));
	print $Language->getText('group', 'long_tracker').'</a>';

	$result=db_query("SELECT agl.*,aca.count,aca.open_count
	FROM artifact_group_list agl
	LEFT JOIN artifact_counts_agg aca USING (group_artifact_id)
	WHERE agl.group_id='$group_id'
	AND agl.is_public=1
	ORDER BY group_artifact_id ASC");

	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<br /><em>'.$Language->getText('project_home','no_trackers').'</em>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '<p />
		&nbsp;-&nbsp;<a href="/tracker/?atid='. db_result($result, $j, 'group_artifact_id') .
		'&amp;group_id='.$group_id.'&amp;func=browse">'. db_result($result, $j, 'name') .'</a>
		( '.$Language->getText('project_home','tracker_open',array((int) db_result($result, $j, 'open_count'), (int) db_result($result, $j, 'count'))) .' )<br />'.
		db_result($result, $j, 'description');
		}
	}
}

// ################## forums

if ($project->usesForum()) {
	print '<hr size="1" /><a href="/forum/?group_id='.$group_id.'">';
	print html_image('ic/forum20g.png','20','20',array('alt'=>$Language->getText('group','short_forum')));
	print '&nbsp;'.$Language->getText('group','long_forum').'</a>';
	$forums_count = project_get_public_forum_count($group_id);
	if ($forums_count > 1) {
		$label = 'forum_messages';
	} else {
		$label = 'forum_messages_only_one_forum';
	}
	print " ( ".$Language->getText('project_home',$label,array(project_get_public_forum_message_count($group_id), $forums_count))." ";
	print ")\n";
}

// ##################### Doc Manager

if ($project->usesDocman()) {
	print '
	<hr size="1" />
	<a href="/docman/?group_id='.$group_id.'">';
	print html_image('ic/docman16b.png','20','20',array('alt'=>$Language->getText('group','short_docman')));
	print '&nbsp;'.$Language->getText('group','long_docman').'</a>';
}

// ##################### Mailing lists

if ($project->usesMail()) {
	print '<hr size="1" /><a href="/mail/?group_id='.$group_id.'">';
	print html_image('ic/mail16b.png','20','20',array('alt'=>$Language->getText('group','short_mail')));
	print '&nbsp;'.$Language->getText('group','long_mail').'</a>';
	print " ( <strong>". project_get_mail_list_count($group_id) ."</strong> ".$Language->getText('project_home','public_mailing_lists').")";
}

// ##################### Task Manager

if ($project->usesPm()) {
	print '<hr size="1" /><a href="/pm/?group_id='.$group_id.'">';
	print html_image('ic/taskman20g.png','20','20',array('alt'=>$Language->getText('group','short_pm')));
	print '&nbsp;'.$Language->getText('group','long_pm').'</a>';
	$sql="SELECT * FROM project_group_list WHERE group_id='$group_id' AND is_public=1";
	$result = db_query ($sql);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<br /><em>'.$Language->getText('project_home','no_subprojects').'</em>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '
			<br /> &nbsp; - <a href="/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
			'&amp;group_id='.$group_id.'&amp;func=browse">'.db_result($result, $j, 'project_name').'</a>';
		}

	}
}

// ######################### Surveys

if ($project->usesSurvey()) {
	print '<hr size="1" /><a href="/survey/?group_id='.$group_id.'">';
	print html_image('ic/survey16b.png','20','20',array('alt'=>$Language->getText('group','short_survey')));
	print " ".$Language->getText('group','long_survey')."</a>";
	echo ' ( <strong>'. project_get_survey_count($group_id) .'</strong> '.$Language->getText('project_home','surveys').'  )';
}

// ######################### SCM

if ($project->usesSCM()) {
	print '<hr size="1" /><a href="/scm/?group_id='.$group_id.'">';
	print html_image('ic/cvs16b.png','20','20',array('alt'=>$Language->getText('group','short_scm')));
	print " ".$Language->getText('group','long_scm')."</a>";

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
		print "<a href=\"ftp://" . $project->getUnixName() . "." . $GLOBALS['sys_default_domain'] . "/pub/". $project->getUnixName() ."/\">";
		print html_image('ic/ftp16b.png','20','20',array('alt'=>$Language->getText('group','long_ftp')));
		print $Language->getText('group','long_ftp')."</a>";
	}
}

echo $HTML->boxBottom();

if ($project->usesNews()) {
	// COLUMN BREAK
	?>

		</td>
		<td width="15">&nbsp;</td>
		<td valign="top" width="50%">

	<?php
	// ############################# Latest News

	echo $HTML->boxTop($Language->getText('group','long_news'));

	echo news_show_latest($group_id,10,false);

	echo $HTML->boxBottom();
}

?>
		</td>
	</tr>
</table>

<?php

site_project_footer(array());

?>
