<?php 

/** 
* project_home.php 
* 
* SourceForge: Breaking Down the Barriers to Open Source Development 
* Copyright 1999-2001 (c) VA Linux Systems 
* http://sourceforge.net 
* 
*/

require_once $gfwww.'news/news_utils.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'include/project_summary.php';
require_once $gfcommon.'include/tag_cloud.php';
//UNCOMMENT TO TEST WIDGETS
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

session_require_perm ('project_read', $group_id) ;

$title = _('Project Info');

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'home'));


// ########################################### end top area
$request =& HTTPRequest::instance();
$request->set('group_id',$group_id);
//COMMENT TO DESACTIVATE WIDGETS
$lm = new WidgetLayoutManager();
$lm->displayLayout($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP);
/*
// two column deal
// Embedd some RDFa to describe the project using DOAP and SIOC
echo '
<div id="forge-project-home" class="underline-link">

<div about="" typeof="sioc:Space doap:Project" xmlns:sioc="http://rdfs.org/sioc/ns#" xmlns:doap="http://usefulinc.com/ns/doap#">
    <span rel="planetforge:hosted_by" resource="'. util_make_url ('/') .'"></span>
</div>

<table id="project-summary-and-devs" class="my-layout-table" summary="">
	<tr>
		<td>
			<h2><span property="doap:shortdesc">'.$project->getPublicName().'</span></h2>
			<h3>'._('Project summary').'</h3>';


// ########################################## top area, not in box

$res_admin = db_query_params ('SELECT users.user_id,users.user_name,users.realname,user_group.admin_flags
	FROM users,user_group
	WHERE user_group.user_id=users.user_id
	AND user_group.group_id=$1
	AND users.status=$2
	ORDER BY admin_flags DESC,realname',
			array($group_id,
				'A'));

if ($project->getStatus() == 'H') {
	print "<p>".sprintf(_('NOTE: This project entry is maintained by the %1$s staff. We are not the official site for this product. Additional copyright information may be found on this project\'s homepage.'), forge_get_config ('forge_name'))."</p>\n";
}

$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("project_before_description",$hook_params) ;

// insert an empty <span /> which seems to be better if not compacted
print '<span property="doap:name" content="'. $project->getUnixName() .'"></span>'."\n";
// print '<span property="coclico:hosted_by">' ...
//print '<div property="sioc:has_container" xmlns:sioc="http://rdfs.org/sioc/ns#" content="fusionforge:ForgeProjects" xmlns:fusionforge="http://fusionforge.org/fusionforge#">';

// Try to display the blocks description first if active.
$pluginManager = plugin_manager_get_object();
if (! $pluginManager->PluginIsInstalled('blocks') || !plugin_hook ("blocks", "summary_description")) {
	$project_description = $project->getDescription();
	if ($project_description) {
		// need to use a litteral version for content attribute since nl2br is for HTML
		print "<p>"
			.'<span property="doap:description" content="'. preg_quote($project_description,'"') .'">'
			. nl2br($project_description)
			.'</span></p>';
	} else {
		print "<p>" . _('This project has not yet submitted a description.') . '</p>';
	}
}

print "<br />\n";

// Tag list
if ($GLOBALS['sys_use_project_tags']) {
	$list_tag = list_project_tag($group_id);
	if ($list_tag) {
		print '<p>' . _('Tags').':&nbsp;'. $list_tag . '</p>';
	}
	else {
		if (forge_check_perm ('project_admin', $group_id)) {
			print '<p><a href="/project/admin/editgroupinfo.php?group_id=' . $group_id . '" >' . _('No tag defined for this project') . '</a>.</p>';
		}
		else {
			print '<p>' . _('No tag defined for this project') . '</p>';
		}
	}
}

if(forge_get_config('use_trove')) {
	print "<br />\n";
	print stripslashes(trove_getcatlisting($group_id,0,1,1));
}

// registration date
$project_start_date = $project->getStartDate();
print(_('Registered:&nbsp;') . 
	'<span property="doap:created" content="'.date('Y-m-d', $project_start_date).'">'.
	date(_('Y-m-d H:i'), $project_start_date).
	'</span>');

// Get the activity percentile
// CB hide stats if desired
if ($project->usesStats()) {
	$actv = db_query_params ('SELECT ranking FROM project_weekly_metric WHERE group_id=$1',
			array($group_id));
	$actv_res = db_result($actv,0,"ranking");
	if (!$actv_res) {
		$actv_res=0;
	}
	print '<br />'.sprintf (_('Activity Ranking: %d'), $actv_res) ;
	print '<br />'.sprintf(_('View project <a href="%1$s" >Statistics</a>'),util_make_url ('/project/stats/?group_id='.$group_id));
	if ( ($project->usesTracker() && forge_get_config('use_tracker')) || ($project->usesPm() && forge_get_config('use_pm')) ) {
		print sprintf(_(' or <a href="%1$s">Activity</a>'),util_make_url ('/project/report/?group_id='.$group_id));
	}
	print '<br />'.sprintf(_('View list of <a href="%1$s">RSS feeds</a> available for this project.'), util_make_url ('/export/rss_project.php?group_id='.$group_id)). '&nbsp;' . html_image('ic/rss.png',16,16,array());
}

if(forge_get_config('use_people')) {
	$jobs_res = db_query_params ('SELECT name 
					FROM people_job,people_job_category 
					WHERE people_job.category_id=people_job_category.category_id 
					AND people_job.status_id=1 
					AND group_id=$1 
					GROUP BY name',
				     array ($group_id),
				     2);
	if ($jobs_res) {
		$num=db_numrows($jobs_res);
			if ($num>0) {
				print '<br /><br />';
				printf(
					ngettext('HELP WANTED: This project is looking for a <a href="%1$s">"%2$s"</a>.',
						 'HELP WANTED: This project is looking for people to fill <a href="%1$s">several different positions</a>.',
						 $num),
					util_make_url ('/people/?group_id='.$group_id),
					db_result($jobs_res,0,"name"));
//print '<div rel="fusionforge:has_job" typeof="fusionforge:Job" xmlns:fusionforge="http://fusionforge.org/fusionforge#">';
//print '<span rel="dc:title" content="'. db_result($jobs_res,0,"name").'" xmlns:dc="http://purl.org/dc/elements/1.1/">'; 
//print '</span>';
//echo '</div>';
//end of job description part
			}
	}
}


$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("project_after_description",$hook_params) ;

echo '</td>' ;

// ########################### Developers on this project

echo '<td>' ;

plugin_hook ("blocks", "summary_right") ;

echo $HTML->boxTop(_('Project Members'), 'Project_Members');

$iam_member = false ;

if (db_numrows($res_admin) > 0) {
	echo "<p>\n";
	echo '<span class="develtitle">'._('Project Admins').'</span><br />';
	$started_developers = false;
	while ($row_admin = db_fetch_array($res_admin)) {
		if (trim($row_admin['admin_flags']) != 'A' && !$started_developers) {
			$started_developers=true;
			echo '<span class="develtitle">'. _('Developers').':</span><br />';
		}
		if (!$started_developers) {
			echo '<div rel="doap:maintainer">'."\n";
		} else {
			echo '<div rel="doap:developer">'."\n";
		}
		# a foaf:Person that holds an account on the forge
		$developer_url = util_make_url_u ($row_admin['user_name'],$row_admin['user_id']);
		echo '<div typeof="foaf:Person" xmlns:foaf="http://xmlns.com/foaf/0.1/" about="'.
			$developer_url.'#me' .'" >'."\n";
		echo '<div rel="foaf:account">'."\n";
		echo '<div typeof="sioc:UserAccount" about="'.
        	$developer_url.
        	'" xmlns:sioc="http://rdfs.org/sioc/ns#">'."\n";
		echo util_make_link_u ($row_admin['user_name'],$row_admin['user_id'],$row_admin['realname']) ."<br />\n";
		echo "</div>\n"; // /sioc:UserAccount
		echo "</div>\n"; // /foaf:holdsAccount
		echo "</div>\n"; // /foaf:Person
		echo "</div>\n"; // /doap:maintainer|developer
		if ($row_admin['user_id'] == user_getid())
			$iam_member = true ;
	}
	echo "</p>\n";
}

$members = $project->getUsers();
echo '<p><span rel="sioc:has_usergroup" xmlns:sioc="http://rdfs.org/sioc/ns#">';
echo '<div about="members/" typeof="sioc:UserGroup">';
echo '<span rel="http://www.w3.org/2002/07/owl#sameAs">';
echo util_make_link ('/project/memberlist.php?group_id='.$group_id,sprintf(_('View the %1$d Member(s)'),count($members)));
echo '</span>';
echo '</div>';
echo '</span></p>';
// end of project usergroup description

if (!$iam_member) {
	echo '<p>'.util_make_link ('/project/request.php?group_id='.$group_id,_('Request to join')).'</p>';
}
echo $HTML->boxBottom();

echo '</td></tr></table>';
$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("project_before_frs",$hook_params) ;
echo '<div id="file-releases">';

// ############################# File Releases

if ($project->usesFRS()) {
	echo $HTML->boxTop(_('Latest File Releases'), 'Latest_File_Releases');
	$unix_group_name = $project->getUnixName();

	echo '
	<table summary="Latest file releases" class="width-100p100">
		<tr class="table-header">
			<th class="align-left" scope="col">
				'._('Package').'
			</th>
			<th scope="col">
				'._('Version').'
			</th>
			<th scope="col">
				'._('Date').'
			</th>
			<th scope="col">
				'._('Notes').'
			</th>
			<th scope="col">
				'._('Monitor').'
			</th>
			<th scope="col">
				'._('Download').'
			</th>
		</tr>';

	if (! forge_check_perm ('frs', $group_id, 'read_public')) {
		echo '<tr><td colspan="6"><strong>'._('This Project Has Not Released Any Files (or you are not allowed to see them).').'</strong></td></tr>';
	} else {
		$qpa = db_construct_qpa () ;
		$qpa = db_construct_qpa ($qpa, 'SELECT frs_package.package_id,frs_package.name AS package_name,frs_release.name AS release_name,frs_release.release_id AS release_id,frs_release.release_date AS release_date 
			FROM frs_package,frs_release 
			WHERE frs_package.package_id=frs_release.package_id 
			AND frs_package.group_id=$1 
			AND frs_release.status_id=1 ',
					 array ($group_id)) ;
		
		if (! forge_check_perm ('frs', $group_id, 'read_private')) {
			$qpa = db_construct_qpa ($qpa, 'AND (frs_package.is_public=1 ', array ()) ;
		}
		
		$qpa = db_construct_qpa ($qpa, 'ORDER BY frs_package.package_id,frs_release.release_date DESC',
					 array ());
		
		$res_files = db_query_qpa ($qpa);
		$rows_files=db_numrows($res_files);
		if (!$res_files || $rows_files < 1) {
			echo db_error();
			// No releases
			echo '<tr><td colspan="6"><strong>'._('This Project Has Not Released Any Files (or you are not allowed to see them).').'</strong></td></tr>';
			
		} else {
			
			//	This query actually contains ALL releases of all packages
			//	We will test each row and make sure the package has changed before printing the row
			
			for ($f=0; $f<$rows_files; $f++) {
				if (db_result($res_files,$f,'package_id')==db_result($res_files,($f-1),'package_id')) {
					//same package as last iteration - don't show this release
				} else {
					$rel_date = getdate (db_result ($res_files, $f, 'release_date'));
					$package_name = db_result($res_files, $f, 'package_name');
					$package_release = db_result($res_files,$f,'release_name');
					echo '
                        <tr class="align-center">
						<td class="align-left">
							<strong>' . $package_name . '</strong>
						</td>';
					// Releases to display
//print '<div about="" xmlns:sioc="http://rdfs.org/sioc/ns#" rel="container_of" resource="'.util_make_link ('/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files,$f,'release_id').'">';

					echo '
                        <td>'
						.$package_release.'
						</td>
						<td>'
						. $rel_date["month"] . ' ' . $rel_date["mday"] . ', ' . $rel_date["year"] .
						'</td>
						<td class="align-center">';
//echo '</div>';
					
					// -> notes
					// accessibility: image is a link, so alt must be unique in page => construct a unique alt
					$tmp_alt = $package_name . " - " . _('Release Notes');
					$link = '/frs/shownotes.php?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files, $f, 'release_id');
					$link_content = $HTML->getReleaseNotesPic($tmp_alt, $tmp_alt);
					echo util_make_link ($link, $link_content);
					echo '</td>
						<td class="align-center">';
					
					// -> monitor
					$tmp_alt = $package_name . " - " . _('Monitor this package');
					$link = '/frs/monitor.php?filemodule_id=' .  db_result($res_files,$f,'package_id') . '&amp;group_id='.$group_id.'&amp;start=1';
					$link_content = $HTML->getMonitorPic($tmp_alt, $tmp_alt);
					echo util_make_link ($link, $link_content);
					echo '</td>
						<td class="align-center">';
					
					// -> download
					$tmp_alt = $package_name." ".$package_release." - ". _('Download');
					$link_content = $HTML->getDownloadPic($tmp_alt, $tmp_alt);
					$t_link_anchor = $HTML->toSlug($package_name)."-".$HTML->toSlug($package_release)."-title-content";
					$link = '/frs/?group_id=' . $group_id . '&amp;release_id=' . db_result($res_files, $f, 'release_id')."#".$t_link_anchor;
					echo util_make_link ($link, $link_content);
					echo '</td>
					</tr>';
					
				}
			}
		}
	}
	echo '</table>';
	echo '<div class="underline-link">' . util_make_link ('/frs/?group_id='.$group_id, _('View All Project Files')) . '</div>';
		
	echo $HTML->boxBottom();
}

echo '</div><!-- id="file-releases" -->' . "\n";

$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("project_after_frs",$hook_params) ;

// ############################## PUBLIC AREAS

echo '<table id="project-public-areas-and-news" class="my-layout-table" summary="">
    <tr><td>
    ';
echo $HTML->boxTop(_('Public Areas'), 'Public_Areas');

// ################# Homepage Link

echo '<div class="public-area-box" rel="doap:homepage">';
echo util_make_link ('http://' . $project->getHomePage(), $HTML->getHomePic(_('Home Page')) . '&nbsp;' . _('Project Home Page'), false, true);
echo '</div>
    ';

// ################## ArtifactTypes

if ($project->usesTracker()) {
	echo '<div class="public-area-box">';
	$link_content = $HTML->getFollowPic(_('Tracker')) . '&nbsp;' . _('Tracker');
	echo util_make_link ( '/tracker/?group_id=' . $group_id, $link_content);

	$result=db_query_params ('SELECT agl.*,aca.count,aca.open_count
	FROM artifact_group_list agl
	LEFT JOIN artifact_counts_agg aca USING (group_artifact_id)
	WHERE agl.group_id=$1
	AND agl.is_public=1
	ORDER BY group_artifact_id ASC',
			array($group_id));

	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '<br /><em>'._('There are no public trackers available').'</em>';
	} else {
		echo '<ul class="tracker" rel="doap:bug-database">';
		for ($j = 0; $j < $rows; $j++) {
			// tracker REST paths are something like : /tracker/cm/project/A_PROJECT/atid/NUMBER to plan compatibility
			// with OSLC-CM server API
			$group_artifact_id = db_result($result, $j, 'group_artifact_id');
			$tracker_stdzd_uri = util_make_url('/tracker/cm/project/'. $project->getUnixName() .'/atid/'. $group_artifact_id);
			echo '<li about="'. $tracker_stdzd_uri . '" typeof="sioc:Container" xmlns:sioc="http://rdfs.org/sioc/ns#">'."\n";
            print '<span rel="http://www.w3.org/2002/07/owl#sameAs">'."\n";
			echo util_make_link ('/tracker/?atid='. $group_artifact_id . '&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'name')) . ' ' ;
			echo "</span>\n"; // /owl:sameAs
 			printf(ngettext('(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', '(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', (int) db_result($result, $j, 'open_count')), (int) db_result($result, $j, 'open_count'), (int) db_result($result, $j, 'count'));
			echo '<br />'.db_result($result, $j, 'description');
			print '<span rel="sioc:has_space" resource="" />'."\n";
			echo "</li>\n";
		}
		echo '</ul>';
	}
	echo '</div>';
}

// ################## forums

if ($project->usesForum()) {
	echo '<div class="public-area-box">';
//	print '<hr size="1" /><a rel="sioc:container_of" xmlns:sioc="http://rdfs.org/sioc/ns#" href="'.util_make_url ('/forum/?group_id='.$group_id).'">';
    $link_content = $HTML->getForumPic('') . '&nbsp;' . _('Public Forums');
    echo util_make_link ( '/forum/?group_id=' . $group_id, $link_content);
	print ' (';
	$messages_count = project_get_public_forum_message_count($group_id);
	$forums_count = project_get_public_forum_count($group_id);
	printf(ngettext("<strong>%d</strong> message","<strong>%d</strong> messages",$messages_count),$messages_count);
	print ' in ';
	printf(ngettext("<strong>%d</strong> forum","<strong>%d</strong> forums",$forums_count),$forums_count);
	print ')' ;
	print "\n</div>";
}

// ##################### Doc Manager

if ($project->usesDocman()) {
	echo '<div class="public-area-box">';
	$link_content = $HTML->getDocmanPic('') . '&nbsp;' . _('DocManager: Project Documentation');
//	<a rel="sioc:container_of" xmlns:sioc="http://rdfs.org/sioc/ns#" href="'.util_make_url ('/docman/?group_id='.$group_id).'">';
	print util_make_link( '/docman/?group_id='.$group_id, $link_content);
	echo '</div>';
}

// ##################### Mailing lists

if ($project->usesMail()) {
	echo '<div class="public-area-box">';
	$link_content = $HTML->getMailPic('') . '&nbsp;' . _('Mailing Lists');
	print util_make_link( '/mail/?group_id='.$group_id, $link_content);
	$n = project_get_mail_list_count($group_id);
	echo ' ';
	printf(ngettext('(<strong>%1$s</strong> public mailing list)', '(<strong>%1$s</strong> public mailing lists)', $n), $n);
	echo '</div>';
}

// ##################### Task Manager

if ($project->usesPm()) {
	echo '<div class="public-area-box">';
	$link_content = $HTML->getPmPic('') . '&nbsp;' . _('Task Manager');
	print util_make_link( '/pm/?group_id='.$group_id, $link_content);
	
	$result = db_query_params ('SELECT * FROM project_group_list WHERE group_id=$1 AND is_public=1',
				   array ($group_id));
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<br /><em>'._('There are no public subprojects available').'</em>';
	} else {
		echo '<ul class="task-manager">';
		for ($j = 0; $j < $rows; $j++) {
			echo '<li>' ;
			print util_make_link ('/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').'&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'project_name'));
			echo '</li>' ;
		}
		echo '</ul>';
	}
	echo '</div>';
}

// ######################### Surveys

if ($project->usesSurvey()) {
	echo '<div class="public-area-box">';
	$link_content = $HTML->getSurveyPic('') . '&nbsp;' . _('Surveys');
	echo util_make_link( '/survey/?group_id='.$group_id, $link_content);
	echo ' (<strong>'. project_get_survey_count($group_id) .'</strong> ' . _('surveys').')';
	echo '</div>';
}

// ######################### SCM

if ($project->usesSCM()) {
	echo '<div class="public-area-box">';

	$link_content = $HTML->getScmPic('') . '&nbsp;' . _('SCM Repository');
//	print '<hr size="1" /><a rel="doap:repository" href="'.util_make_url ('/scm/?group_id='.$group_id).'">';
	print util_make_link( '/scm/?group_id='.$group_id, $link_content);

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("scm_stats", $hook_params) ;
	echo '</div>';
}

// ######################### Plugins

$hook_params = array ();
$hook_params['group_id'] = $group_id;
plugin_hook ("project_public_area", $hook_params);

// ######################## AnonFTP

// CB hide FTP if desired
if ($project->usesFTP()) {
	if ($project->isActive()) {
		echo '<div class="public-area-box">';
		
		$link_content = $HTML->getFtpPic('') . '&nbsp;' . _('Anonymous FTP Space');
//		print '<a rel="doap:anonymous root" href="ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName() .'/">';
        print util_make_link('ftp://' . $project->getUnixName() . '.' . forge_get_config('web_host') . '/pub/'. $project->getUnixName(), $link_content, false, true);
		echo '</div>';
	}
}

//webcalendar
plugin_hook("cal_link_group",$group_id);
echo $HTML->boxBottom();

echo '
    </td>
    <td>
    ';

if ($project->usesNews()) {
	// ############################# Latest News
	echo $HTML->boxTop(_('Latest News'), 'Latest_News');
	echo news_show_latest($group_id,10,false);
	echo $HTML->boxBottom();
}

//echo $HTML->boxBottom();

echo '</td>
    </tr>
    </table>
    </div><!-- about="" -->
    </div><!-- id="forge-project-home" -->
    ';

//
//	Linked projects (hierarchy)
//

plugin_hook('project_home_link',$group_id);
*/
site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
