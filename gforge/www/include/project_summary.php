<?php
/**
 * project_summary.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

$project_agg_arr=array();

/**
 * project_setup_agg() - Set up a project aggregate array.
 *
 * @param		int		Group ID
 * @access		private
 */
function project_setup_agg($group_id) {
	global $project_agg_arr,$project_agg_arr_is_set;
	$res=db_query("SELECT * FROM project_sums_agg WHERE group_id=$group_id");
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
	if ($project_agg_arr[$type]) {
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
		return 'Error - No Group ID';
	}
	if (!$mode) {
		$mode='full';
	}

	$project =& group_get_object($group_id);
	// ################## forums

	if (!$project || !is_object($project)) {
		return 'Could Not Create Project Object';
	} elseif ($project->isError()) {
		return $project->getErrorMessage();
	}

	if (!$no_table) {
		$return .= '

		<TABLE BORDER=0 WIDTH="100%"><TR><TD BGCOLOR="#EAECEF">';
	}

	// ################## ArtifactTypes

	$return .= '<A href="/tracker/?group_id='.$group_id.'">';
	$return .= html_image("ic/tracker20g.png",'20','20',array('alt'=>'Tracker'));
	$return .= ' Tracker</A>';

	if ($mode != 'compact') {
		$result=db_query("SELECT agl.*,aca.count,aca.open_count
		FROM artifact_group_list agl
		LEFT JOIN artifact_counts_agg aca USING (group_artifact_id) 
		WHERE agl.group_id='$group_id'
		AND agl.is_public=1
		ORDER BY group_artifact_id ASC");

		$rows = db_numrows($result);

		if (!$result || $rows < 1) {
			$return .= '<BR><I>There are no public trackers available</I>';
		} else {
			for ($j = 0; $j < $rows; $j++) {
				$return .= '<P>
				&nbsp;-&nbsp;<A HREF="/tracker/?atid='. db_result($result, $j, 'group_artifact_id') .
				'&group_id='.$group_id.'&func=browse">'. db_result($result, $j, 'name') .'</A> 
				( <B>'. db_result($result, $j, 'open_count') .' open / '. db_result($result, $j, 'count') .' total</B> )<BR>'.
				db_result($result, $j, 'description');
			}   
		}
	}

	if ($project->usesForum()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/forum/?group_id='.$group_id.'">';
		$return .= html_image("ic/forum20g.png","20","20",array("BORDER"=>"0","ALT"=>"Forums"));
		$return .= '&nbsp;Forums</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_public_forum_message_count($group_id) ."</B> messages in ";
			$return .= "<B>". project_get_public_forum_count($group_id) ."</B> forums )\n";
		}
	}

	// ##################### Doc Manager

	if ($project->usesDocman()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/docman/?group_id='.$group_id.'">';
		$return .= html_image("ic/docman16b.png","20","20",array("BORDER"=>"0","ALT"=>"Docs"));
		$return .= '&nbsp;Doc&nbsp;Manager</A>';
	}

	// ##################### Mailing lists

	if ($project->usesMail()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/mail/?group_id='.$group_id.'">';
		$return .= html_image("ic/mail16b.png","20","20",array("BORDER"=>"0","ALT"=>"Mail Lists"));
		$return .= '&nbsp;Mailing&nbsp;Lists</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_mail_list_count($group_id) ."</B> public lists )";
		}
	}

	// ##################### Task Manager 

	if ($project->usesPm()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/pm/?group_id='.$group_id.'">';
		$return .= html_image("ic/taskman20g.png","20","20",array("BORDER"=>"0","ALT"=>"Tasks"));
		$return .= '&nbsp;Task&nbsp;Manager</A>';

		if ($mode != 'compact') {
			//get a list of publicly available projects
			$sql="SELECT * FROM project_group_list WHERE group_id='$group_id' AND is_public=1";
			$result = db_query ($sql);
			$rows = db_numrows($result);
			if (!$result || $rows < 1) {
				$return .= '<BR><I>There are no public subprojects available</I>';
			} else {
				for ($j = 0; $j < $rows; $j++) {
					$return .= '
					<BR> &nbsp; - <A HREF="/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
					'&group_id='.$group_id.'&func=browse">'.db_result($result, $j, 'project_name').'</A>';
				}
				db_free_result($result);
			}
		}
	}

	// ######################### Surveys 

	if ($project->usesSurvey()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/survey/?group_id='.$group_id.'">';
		$return .= html_image("ic/survey16b.png","20","20",array("BORDER"=>"0","ALT"=>"Surveys"));
		$return .= "&nbsp;Surveys</A>";
		if ($mode != 'compact') {
			$return .= ' ( <B>'. project_get_survey_count($group_id) .'</B> surveys )';
		}
	}

	// ######################### CVS 

	if ($project->usesCVS()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/scm/?group_id='.$group_id.'">';
		$return .= html_image("ic/cvs16b.png","20","20",array("BORDER"=>"0","ALT"=>"CVS"));
		$return .= "&nbsp;CVS&nbsp;Tree</A>";

		if ($mode != 'compact') {
			$sql = "SELECT SUM(commits) AS commits,SUM(adds) AS adds from stats_cvs_group where group_id='$group_id'";
			$result = db_query($sql);
			$return .= ' ( <B>'.db_result($result,0,0).'</B> commits, <B>'.db_result($result,0,1).'</B> adds )';
		}
	}

	// ######################## Released Files
	
	if ($project->isActive()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="/project/showfiles.php?group_id='.$group_id.'">';
		$return .= html_image("ic/ftp16b.png","20","20",array("BORDER"=>"0","ALT"=>"FTP"));
		$return .= "&nbsp;Released&nbsp;Files</A>";
	}

	if (!$no_table) {
		$return .= '

		</TD></TR></TABLE>';
	}

	return $return;
}

?>
