<?php
/**
 * Project Summary
 *
 * Copyright 1999-2001 (c) VA Linux Systems 
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$project_agg_arr=array();

/**
 * project_setup_agg() - Set up a project aggregate array.
 *
 * @param		int		Project ID
 * @access		private
 */
function project_setup_agg($group_id) {
	global $project_agg_arr,$project_agg_arr_is_set;
	$res=db_query_params ('SELECT type, count FROM project_sums_agg WHERE group_id=$1',
			array($group_id));
	$rows=db_numrows($res);
	if ($res && $rows > 0) {
		for ($i=0; $i<$rows; $i++) {
			$project_agg_arr[db_result($res,$i,'type')]=db_result($res,$i,'count');
		}	
	}
	$project_agg_arr_is_set=true;
}

/**
 * project_getaggvalue() - Get a projects aggregate value for a specific type
 *
 * @param		int		The group ID
 * @param		string	The type
 * @access		private
 */
function project_getaggvalue($group_id,$type) {
	global $project_agg_arr,$project_agg_arr_is_set;
	if (!$project_agg_arr_is_set) {
		project_setup_agg($group_id);
	}
	// Remove warning
	if (isset($project_agg_arr[$type])) {
		return "$project_agg_arr[$type]";
	} else {
		return '0';
	}
}

/**
 * project_get_mail_list_count() - Get the number of mailing lists for a project.
 *
 * @param		int		The group ID
 */
function project_get_mail_list_count($group_id) {
	return project_getaggvalue($group_id,'mail'); 
}

/**
 * project_get_survey_count() - Get the number of surveys for a project.
 *
 * @param		int		The group ID
 */
function project_get_survey_count($group_id) {
	return project_getaggvalue($group_id,'surv'); 
}	   

/**
 * project_get_public_forum_count() - Get the number of public forums for a project.
 *
 * @param		int		The group ID
 */
function project_get_public_forum_count($group_id) {
	return project_getaggvalue($group_id, 'fora');
}

/**
 * project_get_public_forum_message_count() - Get the number of messages within public forums for a project.
 *
 * @param		int		The group ID
 */
function project_get_public_forum_message_count($group_id) {
	return project_getaggvalue($group_id, 'fmsg');
}

/**
 * project_summary() - Build a project summary box that projects can insert into their project pages
 *
 * @param		int		The group ID
 * @param		string	How to return the results.
 * @param		bool	Whether to return the results within an HTML table or not
 */
function project_summary($group_id,$mode,$no_table) {
	if (!$group_id) {
		return 'Error - No Project ID';
	}
	if (!$mode) {
		$mode='full';
	}

	$project = group_get_object($group_id);

	if (!$project || !is_object($project)) {
		return 'Could Not Create Project Object';
	} elseif ($project->isError()) {
		return $project->getErrorMessage();
	}

	if (!$no_table) {
		$return = '

		<table border=0 width="100%"><tr><td class="tablecontent">';
	}

	// ################## ArtifactTypes

	$return .= '<a href="'.util_make_url ('/tracker/?group_id='.$group_id).'">';
	$return .= html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
	$return .= ' Tracker</a>';

	if ($mode != 'compact') {
		$result=db_query_params ('SELECT agl.*,aca.count,aca.open_count
		FROM artifact_group_list agl
		LEFT JOIN artifact_counts_agg aca USING (group_artifact_id) 
		WHERE agl.group_id=$1
		AND agl.is_public=1
		ORDER BY group_artifact_id ASC',
			array($group_id));

		$rows = db_numrows($result);
	
		if (!$result || $rows < 1) {
			$return .= '<br /><em>'._('There are no public trackers available').'</em>';
		} else {
			for ($j = 0; $j < $rows; $j++) {
				$return .= '<p>
				&nbsp;-&nbsp;'.util_make_link ('/tracker/?atid='. db_result($result, $j, 'group_artifact_id') . '&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'name'));
				$return .= sprintf(ngettext('(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', '(<strong>%1$s</strong> open / <strong>%2$s</strong> total)', (int) db_result($result, $j, 'open_count')), (int) db_result($result, $j, 'open_count'), (int) db_result($result, $j, 'count')) ;
				$return .= '</p>';
			}   
		}
	}

	// ##################### Forums

	if ($project->usesForum()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/forum/?group_id='.$group_id).'">';
		$return .= html_image("ic/forum20g.png","20","20",array("alt"=>"Forums"));
		$return .= '&nbsp;Forums</a>';

		if ($mode != 'compact') {
			$return .= " ( <strong>". project_get_public_forum_message_count($group_id) ."</strong> messages in ";
			$return .= "<strong>". project_get_public_forum_count($group_id) ."</strong> forums )\n";
		}
	}

	// ##################### Doc Manager

	if ($project->usesDocman()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/docman/?group_id='.$group_id).'">';
		$return .= html_image("ic/docman16b.png","20","20",array("alt"=>"Docs"));
		$return .= '&nbsp;Doc&nbsp;Manager</a>';
	}

	// ##################### Mailing lists

	if ($project->usesMail()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/mail/?group_id='.$group_id).'">';
		$return .= html_image("ic/mail16b.png","20","20",array("alt"=>"Mail Lists"));
		$return .= '&nbsp;Mailing&nbsp;Lists</a>';

		if ($mode != 'compact') {
			$return .= " ( <strong>". project_get_mail_list_count($group_id) ."</strong> public lists )";
		}
	}

	// ##################### Tasks

	if ($project->usesPm()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/pm/?group_id='.$group_id).'">';
		$return .= html_image("ic/taskman20g.png","20","20",array("alt"=>"Tasks"));
		$return .= '&nbsp;Task&nbsp;Manager</a>';

		if ($mode != 'compact') {
			//get a list of publicly available projects
			$result = db_query_params ('SELECT * FROM project_group_list WHERE group_id=$1 AND is_public=1',
						   array ($group_id));
			$rows = db_numrows($result);
			if (!$result || $rows < 1) {
				$return .= '<br /><em>There are no public subprojects available</em>';
			} else {
				for ($j = 0; $j < $rows; $j++) {
					$return .= '
					<br /> &nbsp; - '.util_make_link ('/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').'&amp;group_id='.$group_id.'&amp;func=browse',db_result($result, $j, 'project_name'));
				}
				db_free_result($result);
			}
		}
	}

	// ######################### Surveys 

	if ($project->usesSurvey()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/survey/?group_id='.$group_id).'">';
		$return .= html_image("ic/survey16b.png","20","20",array("alt"=>"Surveys"));
		$return .= "&nbsp;Surveys</a>";
		if ($mode != 'compact') {
			$return .= ' ( <strong>'. project_get_survey_count($group_id) .'</strong> surveys )';
		}
	}

	// ######################### SCM 

	if ($project->usesSCM()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/scm/?group_id='.$group_id).'">';
		$return .= html_image("ic/cvs16b.png","20","20",array("alt"=>"SCM"));
		$return .= "&nbsp;SCM&nbsp;Tree</a>";

		if ($mode != 'compact') {
			$result = db_query_params ('SELECT SUM(commits) AS commits,SUM(adds) AS adds from stats_cvs_group where group_id=$1',
						   array ($group_id));
			$return .= ' ( <strong>'.db_result($result,0,0).'</strong> commits, <strong>'.db_result($result,0,1).'</strong> adds )';
		}
	}

	// ######################## Released Files
	
	if ($project->isActive()) {
		$return .= '

			<hr size="1" />';
		$return .= '<a href="'.util_make_url ('/project/showfiles.php?group_id='.$group_id).'">';
		$return .= html_image("ic/ftp16b.png","20","20",array("alt"=>"FTP"));
		$return .= "&nbsp;Released&nbsp;Files</a>";
	}

	if (!$no_table) {
		$return .= '

		</td></tr></table>';
	}

	return $return;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
