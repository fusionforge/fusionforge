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

require_once('www/include/vote_function.php');
require_once('common/include/vars.php');
require_once('www/news/news_utils.php');
require_once('www/include/trove.php');
require_once('www/include/project_summary.php');

//make sure this project is NOT a foundry
if (!$project->isProject()) {
	header ("Location: /foundry/". $project->getUnixName() ."/");
	exit;
}	   

// Icons theming
$imgproj=$HTML->imgproj;

$title = 'Project Info - '. $project->getPublicName();

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'home','pagename'=>'projects','sectionvals'=>array(group_getname($group_id))));


// ########################################### end top area

// two column deal
?>

<TABLE WIDTH="100%" BORDER="0">
	<TR>
		<TD WIDTH="99%" VALIGN="top">
		<?php 

// ########################################## top area, not in box 
$res_admin = db_query("
	SELECT users.user_id AS user_id,users.user_name AS user_name 
	FROM users,user_group 
	WHERE user_group.user_id=users.user_id 
	AND user_group.group_id=$group_id 
	AND user_group.admin_flags = 'A'
	AND users.status='A'
");

if ($project->getStatus() == 'H') {
	print "<P>NOTE: This project entry is maintained by the ".$GLOBALS['sys_name']." staff. We are not "
		. "the official site "
		. "for this product. Additional copyright information may be found on this project's homepage.\n";
}

if ($project->getDescription()) {
	print "<P>" . $project->getDescription();
} else {
	print "<P>" . $Language->getText('group', 'nodescription');
}

// trove info
print '<BR>&nbsp;<BR>';
print trove_getcatlisting($group_id,0,1);

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
	print '<br>'.$Language->getText('group', 'activity'). $actv_res . '%';
	print '<br>'.$Language->getText('group', 'activitystat', $group_id);
}

$jobs_res = db_query("SELECT name ".
				"FROM people_job,people_job_category ".
				"WHERE people_job.category_id=people_job_category.category_id ".
				"AND people_job.status_id=1 ".
				"AND group_id='$group_id' ".
				"GROUP BY name",2);
if ($jobs_res) {
	$num=db_numrows($jobs_res);
		if ($num>0) {
			print '<br><br>HELP WANTED: This project is looking for ';
				if ($num==1) {
					print '<a href="/people/?group_id='.$group_id.'">'.
							  db_result($jobs_res,0,"name").'(s)</a>';
				} else {
					print 'People to fill '.
						'<a href="/people/?group_id='.$group_id.'">several '.
						'different positions</a>';
				}
		}
}


?>
		</TD>
		<TD NoWrap VALIGN="top">

<?php

// ########################### Developers on this project

echo $HTML->boxTop($Language->getText('group','developer_info'));

if (db_numrows($res_admin) > 0) {

	?>
	<SPAN CLASS="develtitle"><?php echo $Language->getText('group','project_admins'); ?>:</SPAN><BR>
	<?php
		while ($row_admin = db_fetch_array($res_admin)) {
			print "<A href=\"/users/$row_admin[user_name]/\">$row_admin[user_name]</A><BR>";
		}
	?>
	<HR WIDTH="100%" SIZE="1" NoShade>
	<?php

}

?>
<SPAN CLASS="develtitle"><?php echo $Language->getText('group','developers'); ?>:</SPAN><BR>
<?php
//count of developers on this project
$res_count = db_query("SELECT user_id FROM user_group WHERE group_id=$group_id");
print db_numrows($res_count);

?>

<A HREF="/project/memberlist.php?group_id=<?php print $group_id; ?>">[View Members]</A>
<?php 

echo $HTML->boxBottom();

?>
		</TD>
	</TR>
</TABLE>
<P>
<?php

// ############################# File Releases

// CB hide FRS if desired
if ($project->usesFRS()) {
	echo $HTML->boxTop($Language->getText('frs','latest_file_releases')); 
	$unix_group_name = $project->getUnixName();

	echo '
	<TABLE cellspacing="1" cellpadding="5" width="100%" border="0">
		<TR bgcolor="'.$HTML->COLOR_LTBACK1.'">
		<TD align="left"">
			'.$Language->getText('frs','file_package').'
		</td>
		<TD align="center">
			'.$Language->getText('frs','file_version').'
		</td>
		<td align="center">
			'.$Language->getText('frs','file_rel_date').'
		</td>
		<TD align="center">
			'.$Language->getText('frs','file_notes').' / '.$Language->getText('frs','file_monitor').'
		</td>
		<TD align="center">
			'.$Language->getText('frs','file_download').'
		</td>
		</TR>';

		$sql="SELECT frs_package.package_id,frs_package.name AS package_name,frs_release.name AS release_name,frs_release.release_id AS release_id,frs_release.release_date AS release_date ".
			"FROM frs_package,frs_release ".
			"WHERE frs_package.package_id=frs_release.package_id ".
			"AND frs_package.group_id='$group_id' ".
			"AND frs_release.status_id=1 ".
			"ORDER BY frs_package.package_id,frs_release.release_date DESC";

		$res_files = db_query($sql);
		$rows_files=db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No releases
			echo '<TR BGCOLOR="'.$HTML->COLOR_LTBACK1.'"><TD COLSPAN="4"><B>'.$Language->getText('group', 'norelease').'</B></TD></TR>';

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
					<TR BGCOLOR="'.$HTML->COLOR_LTBACK1.'" ALIGN="center">
					<TD ALIGN="left">
					<B>' . db_result($res_files,$f,'package_name'). '</B></TD>';
					// Releases to display
					print '<TD>'.db_result($res_files,$f,'release_name') .'
					</TD>
					<td>' . $rel_date["month"] . ' ' . $rel_date["mday"] . ', ' . $rel_date["year"] . '</td>
					<TD><A href="/project/shownotes.php?group_id=' . $group_id . '&release_id=' . db_result($res_files,$f,'release_id') . '">';
					echo html_image('ic/manual16c.png','15','15',array('alt'=>'Release Notes'));
					echo '</A> - <A HREF="/project/filemodule_monitor.php?filemodule_id=' .	db_result($res_files,$f,'package_id') . '&group_id='.$group_id.'&start=1">';
					echo html_image('ic/mail16d.png','15','15',array('alt'=>'Monitor This Package'));
					echo '</A>
					</TD>
					<TD><A HREF="/project/showfiles.php?group_id=' . $group_id . '&release_id=' . db_result($res_files,$f,'release_id') . '">'.$Language->getText('frs','file_download').'</A></TD></TR>';
				}
			}

		}
		?></TABLE>
	<div align="center">
	<a href="/project/showfiles.php?group_id=<?php print $group_id; ?>">[View ALL Project Files]</A>
	</div>
<?php
	echo $HTML->boxBottom();
}

?>
<P>
<TABLE WIDTH="100%" BORDER="0" CELLPADDING="0" CELLSPACING="0">
	<TR>
		<TD VALIGN="top">

<?php

// ############################## PUBLIC AREAS
echo $HTML->boxTop($Language->getText('group','public_area')); 

// ################# Homepage Link

print "<A href=\"http://" . $project->getHomePage() . "\">";
print html_image('ic/home16b.png','20','20',array('alt'=>$Language->getText('group','short_homepage')));
print '&nbsp;'.$Language->getText('group','long_homepage').'</A>';

// ################## ArtifactTypes

// CB hide tracker if desired
if ($project->usesTracker()) {
	print '<HR SIZE="1" NoShade><A href="/tracker/?group_id='.$group_id.'">';
	print html_image('ic/taskman16b.png','20','20',array('alt'=>$Language->getText('group','short_tracker')));
	print $Language->getText('group', 'short_tracker').'</A>';

	$result=db_query("SELECT agl.*,aca.count,aca.open_count
	FROM artifact_group_list agl
	LEFT JOIN artifact_counts_agg aca USING (group_artifact_id) 
	WHERE agl.group_id='$group_id'
	AND agl.is_public=1 
	ORDER BY group_artifact_id ASC");

	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<BR><I>There are no public trackers available</I>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '<P>
		&nbsp;-&nbsp;<A HREF="/tracker/?atid='. db_result($result, $j, 'group_artifact_id') .
		'&group_id='.$group_id.'&func=browse">'. db_result($result, $j, 'name') .'</A> 
		( <B>'. db_result($result, $j, 'open_count') .' open / '. db_result($result, $j, 'count') .' total</B> )<BR>'.
		db_result($result, $j, 'description');
		}
	}
}

// ################## forums

if ($project->usesForum()) {
	print '<HR SIZE="1" NoShade><A href="/forum/?group_id='.$group_id.'">';
	print html_image('ic/notes16.png','20','20',array('alt'=>$Language->getText('group','short_forum'))); 
	print '&nbsp;'.$Language->getText('group','long_forum').'</A>';
	print " ( <B>". project_get_public_forum_message_count($group_id) ."</B> messages in ";

	print "<B>". project_get_public_forum_count($group_id) ."</B> forums )\n";
}

// ##################### Doc Manager

if ($project->usesDocman()) {
	print '
	<HR SIZE="1" NoShade>
	<A href="/docman/?group_id='.$group_id.'">';
	print html_image('ic/docman16b.png','20','20',array('alt'=>$Language->getText('group','short_docman')));
	print '&nbsp;'.$Language->getText('group','long_docman').'</A>';
}

// ##################### Mailing lists

if ($project->usesMail()) {
	print '<HR SIZE="1" NoShade><A href="/mail/?group_id='.$group_id.'">';
	print html_image('ic/mail16b.png','20','20',array('alt'=>$Language->getText('group','short_mail'))); 
	print '&nbsp;'.$Language->getText('group','long_mail').'</A>';
	print " ( <B>". project_get_mail_list_count($group_id) ."</B> public mailing lists )";
}

// ##################### Task Manager 

if ($project->usesPm()) {
	print '<HR SIZE="1" NoShade><A href="/pm/?group_id='.$group_id.'">';
	print html_image('ic/taskman16b.png','20','20',array('alt'=>$Language->getText('group','short_pm')));
	print '&nbsp;'.$Language->getText('group','long_pm').'</A>';
	$sql="SELECT * FROM project_group_list WHERE group_id='$group_id' AND is_public=1";
	$result = db_query ($sql);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<BR><I>There are no public subprojects available</I>';
	} else {
		for ($j = 0; $j < $rows; $j++) {
			echo '
			<BR> &nbsp; - <A HREF="/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
			'&group_id='.$group_id.'&func=browse">'.db_result($result, $j, 'project_name').'</A>';
		}

	}
}

// ######################### Surveys 

if ($project->usesSurvey()) {
	print '<HR SIZE="1" NoShade><A href="/survey/?group_id='.$group_id.'">';
	print html_image('ic/survey16b.png','20','20',array('alt'=>$Language->getText('group','short_survey')));
	print " ".$Language->getText('group','long_survey')."</A>";
	echo ' ( <B>'. project_get_survey_count($group_id) .'</B> surveys )';
}

// ######################### CVS 

if ($project->usesCVS()) {
	print '<HR SIZE="1" NoShade><A href="/scm/?group_id='.$group_id.'">';
	print html_image('ic/cvs16b.png','20','20',array('alt'=>$Language->getText('group','short_cvs')));
	print " ".$Language->getText('group','long_cvs')."</A>";

	$result = db_query("
		SELECT cvs_commits AS commits,cvs_adds AS adds
		FROM stats_project_all
		WHERE group_id='$group_id'
	", -1, 0, SYS_DB_STATS);
	$cvs_commit_num = db_result($result,0,0);
	$cvs_add_num    = db_result($result,0,1);
	if (!$cvs_commit_num) {
		$cvs_commit_num=0;
	}
	if (!$cvs_add_num) {
		$cvs_add_num=0;
	}
	echo ' ( <B>' . number_format($cvs_commit_num, 0) . '</B> commits, <B>' . number_format($cvs_add_num, 0) . '</B> adds )';
	if ($cvs_commit_num || $cvs_add_num) {
		echo '<br> &nbsp; - 
			<a href="'.account_group_cvsweb_url($project->getUnixName()).'">
			Browse CVS</a>';
	}

}

// ######################## AnonFTP 

// CB hide FTP if desired
if ($project->usesFTP()) {
	if ($project->isActive()) {
		print '<HR SIZE="1" NoShade>';
		print "<A href=\"ftp://" . $project->getUnixName() . "." . $GLOBALS['sys_default_domain'] . "/pub/". $project->getUnixName() ."/\">";
		print html_image('ic/ftp16b.png','20','20',array('alt'=>$Language->getText('group','long_ftp')));
		print $Language->getText('group','long_ftp')."</A>";
	}
}

echo $HTML->boxBottom();

if ($project->usesNews()) {
	// COLUMN BREAK
	?>

		</TD>
		<TD WIDTH="15">&nbsp;</TD>
		<TD VALIGN="top">

	<?php
	// ############################# Latest News

	echo $HTML->boxTop($Language->getText('group','long_news'));

	echo news_show_latest($group_id,10,false);

	echo $HTML->boxBottom();
}

?>
		</TD>
	</TR>
</TABLE>

<?php

site_project_footer(array());

?>
