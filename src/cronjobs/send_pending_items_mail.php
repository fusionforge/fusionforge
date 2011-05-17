#! /usr/bin/php
<?php
/**
 *	send_pending_items_mail.php
 *
 *	Sends mail out for all pending tracker and pm items
 *
 *
 *       usage:
 *            ./send_pending_items_mail.php all
 *              sends mails both for tracker and pm items
 *
 *            ./send_pending_items_mail.php tracker
 *              sends mail for tracker items
 *
 *            ./send_pending_items_mail.php pm
 *              sends mail for pm items 
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';


$options=$GLOBALS['argv'];
if ($options!=FALSE){
	$option=$options[1];
	if (($option=='pm')|| ($option=='all')){
		echo "running pm";
		send_pending_pm_items_mail();
	}
	if (($option=='tracker')|| ($option=='all')){
		echo "\nrunning tracker";
		send_pending_tracker_items_mail();
	}
}

function send_pending_pm_items_mail(){
	$time = time();

	/* first we check the tasks from the project_manager */
	$res = db_query_params ('SELECT project_task.project_task_id, project_task.group_project_id, project_group_list.group_project_id, group_id, project_task.summary,project_task.created_by, project_task.status_id, project_task_vw.user_name, project_task_vw.status_name, project_task_vw.percent_complete FROM project_task, project_group_list NATURAL JOIN project_task_vw WHERE project_task.end_date > 0 AND project_task.end_date < $1 AND project_task.group_project_id=project_group_list.group_project_id AND project_task.status_id=1;',
			array($time));
	for($i = 0; $i < db_numrows($res); $i++) {
		$summary= db_result($res,$i,'summary');
		$status_name=db_result($res,$i,'status_name');
		$user_name= db_result($res,$i,'user_name');
		$project_task_id=db_result($res,$i,'project_task_id');
		$hyperlink=util_make_url('/pm/task.php?func=detailtask&project_task_id='.db_result($res,$i,"project_task_id").'&group_id='.db_result($res,$i,"group_id")
					 .'&group_project_id='.db_result($res,$i,"group_project_id"));

		$userres = db_query_params ('SELECT * FROM users WHERE users.status=$1 AND (user_id = $2 OR user_id IN (SELECT assigned_to_id FROM project_assigned_to WHERE project_task_id = $3))',
					    array ('A',
						   db_result($res,$i,"created_by"),
						   db_result($res,$i,"project_task_id")));
		/* now, for each user, send the mail */
		for ($usercount=0;$usercount<db_numrows($userres);$usercount++){
			$mailto=db_result($userres,$usercount,"email");
			$language=db_result($userres,$usercount,"language");
			setup_gettext_from_language_id($language);
			$subject=_('Pending task manager items notification');
			$messagebody=stripcslashes(sprintf(_('This mail is sent to you to remind you of pending/overdue tasks. 
The task manager item #%1$s is pending: 
Task Summary: %2$s
Submitted by: %4$s
Status:%5$s
Percent Complete: %6$s

Click here to visit the item %3$s'), $project_task_id, $summary, $hyperlink, $user_name, $status_name, db_result($res, $i,'percent_complete')));
			util_send_message($mailto,$subject,$messagebody);	
		}
	}
	cron_entry(19,db_error());
}


function send_pending_tracker_items_mail(){
	/* first, get all the items that are considered overdue */
	$time = time();
	$res = db_query_params ('SELECT artifact_id, submitted_by, group_id, assigned_to, summary,  details, description,  assigned_realname, submitted_realname, status_name, category_name, group_name, group_artifact_id, open_date	FROM artifact_vw a NATURAL JOIN artifact_group_list agl	WHERE (agl.due_period+a.open_date) < $1 AND a.status_id=1',
			array($time));
	
	for ($tmp=0; $tmp<db_numrows($res); $tmp++) {
		$realopendate=date(_('Y-m-d H:i'), db_result($res,$tmp,'open_date'));
		$status_name=db_result($res,$tmp,'status_name');
		$details=db_result($res,$tmp,'detail');
		$summary= db_result($res,$tmp,'summary');
		$users='('.db_result($res,$tmp,"submitted_by").','.db_result($res,$tmp,"assigned_to").')';
		$hyperlink=util_make_url('/tracker/index.php?func=detail&aid='.db_result($res,$tmp,"artifact_id").'&group_id='.db_result($res,$tmp,"group_id")
					 .'&atid='.db_result($res,$tmp,"group_artifact_id"));
		$artifact=db_result($res,$tmp,"artifact_id");
		$opendate=db_result($res,$tmp,"open_date");

		/* now, get all the users */
		$userres = db_query_params ('SELECT * FROM users WHERE user_id = ANY ($1) AND user_id > 100',
					    array(db_int_array_to_any_clause ($users)));
		for ($usercount=0;$usercount<db_numrows($userres);$usercount++){
			$mailto=db_result($userres,$usercount,"email");
			$language=db_result($userres,$usercount,"language");
			setup_gettext_from_language_id($language);
			$subject=_('Pending tracker items notification');
			$messagebody=stripcslashes(sprintf(_('This mail is sent to you to remind you of pending/overdue tracker items. The item #%1$s is pending:
Summary: %3$s
Status: %5$s
Open Date:%6$s
Assigned To: %7$s
Submitted by: %8$s
Details: %9$s


Click here to visit the item: %4$s'),  $artifact, $opendate, $summary, $hyperlink, $status_name, $realopendate, db_result($res,$tmp,'assigned_realname'), db_result($res,$tmp,'submitted_realname'),  db_result($res,$tmp,'details')));
			/* and finally send the email */
			util_send_message($mailto,$subject,$messagebody);	
		}
	}
	cron_entry(19,db_error());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
