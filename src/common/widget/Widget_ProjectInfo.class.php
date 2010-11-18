<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');

/**
 * Widget_ProjectMembers
 */
class Widget_ProjectInfo extends Widget {
	public function __construct() {
		$this->Widget('projectinfo');
	}
	public function getTitle() {
		return _('Project Info');
	}
	public function getContent() {
		$request =& HTTPRequest::instance();
		$group_id = $request->get('group_id');
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		// Tag list
		if ($GLOBALS['sys_use_project_tags']) {
			$list_tag = list_project_tag($group_id);
			echo '<p>'.html_image('ic/tag.png'). ' ';
			if ($list_tag) {
				print _('Tags').':&nbsp;'. $list_tag;
			}
			else {
				$project = group_get_object($group_id);
				if (forge_check_perm ('project_admin', $project->getID())) {
					print '<a href="/project/admin/editgroupinfo.php?group_id=' . $group_id . '" >' . _('No tag defined for this project') . '</a>.';
				}
				else {
					print _('No tag defined for this project');
				}
			}
			echo '</p>';
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
			if (db_numrows($actv) > 0){
				$actv_res = db_result($actv,0,"ranking");
			} else {
				$actv_res = 0;
			}
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

	}
	public function canBeUsedByProject(&$project) {
		return true;
	}
	function getDescription() {
		return _('Some infos about the project.');
	}
}

?>
