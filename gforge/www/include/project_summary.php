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
	$res=db_query("SELECT type, count FROM project_sums_agg WHERE group_id=$group_id");
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
?>
