#! /usr/bin/php
<?php
/**
 * $Id$
 *
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
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('pre.php');
require ('common/include/cron_utils.php');


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
	$sql='select project_task.project_task_id, project_task.group_project_id, project_group_list.group_project_id, group_id, project_task.summary,project_task.created_by, project_task.status_id, project_task_vw.user_name, project_task_vw.status_name, project_task_vw.percent_complete from project_task, project_group_list natural join project_task_vw where project_task.end_date >0 and project_task.end_date<'.$time.' and project_task.group_project_id=project_group_list.group_project_id and project_task.status_id=1;';
	$res = db_query($sql);
	global $sys_default_domain;
	for($i = 0; $i < db_numrows($res); $i++) {
		$summary= db_result($res,$i,'summary');
		$status_name=db_result($res,$i,'status_name');
		$user_name= db_result($res,$i,'user_name');
		$project_task_id=db_result($res,$i,'project_task_id');
		$hyperlink='http://'.$sys_default_domain.'/pm/task.php?func=detailtask&project_task_id='.db_result($res,$i,"project_task_id").'&group_id='.db_result($res,$i,"group_id")
		.'&group_project_id='.db_result($res,$i,"group_project_id").'';
		$sql="select * from users where users.status='A' and user_id in (".db_result($res,$i,"created_by").", (select assigned_to_id from project_assigned_to where project_task_id=".db_result($res,$i,"project_task_id")."))";			
		$userres=db_query($sql);
		/* now, for each user, send the mail */
		for ($usercount=0;$usercount<db_numrows($userres);$usercount++){
			$mailto=db_result($userres,$usercount,"email");
			$language=db_result($userres,$usercount,"language");
			$Lang = new BaseLanguage() ;
			$Lang->loadLanguageID($language);
			$subject=$Lang->getText('send_pending_items_mail','pm_subject');
			$messagebody=stripcslashes($Lang->getText('send_pending_items_mail','pm_message',array($project_task_id, $summary,$hyperlink, $user_name, $status_name, db_result($res,$i,'percent_complete'))));
			util_send_message($mailto,$subject,$messagebody);	
		}
	}
	cron_entry(19,db_error());
}


function send_pending_tracker_items_mail(){
	global $sys_default_domain;
	global $sys_datefmt;
	/* first, get all the items that are considered overdue */
	$time = time();
	$sql = 	'SELECT artifact_id, submitted_by, group_id, assigned_to, summary,  details, description,  assigned_realname, submitted_realname, status_name, category_name, group_name, group_artifact_id, open_date	FROM artifact_vw a NATURAL JOIN artifact_group_list agl	WHERE (agl.due_period+a.open_date) < '.$time.' AND	a.status_id=1';	
	$res=db_query($sql);
	
	for ($tmp=0; $tmp<db_numrows($res); $tmp++) {
		$realopendate=date($sys_datefmt, db_result($res,$tmp,'open_date'));
		$status_name=db_result($res,$tmp,'status_name');
		$details=db_result($res,$tmp,'detail');
		$summary= db_result($res,$tmp,'summary');
		$users='('.db_result($res,$tmp,"submitted_by").','.db_result($res,$tmp,"assigned_to").')';
		$hyperlink='http://'.$GLOBALS['sys_default_domain'].'/tracker/index.php?func=detail&aid='.db_result($res,$tmp,"artifact_id").'&group_id='.db_result($res,$tmp,"group_id")
			.'&atid='.db_result($res,$tmp,"group_artifact_id").'';
		$artifact=db_result($res,$tmp,"artifact_id");
		$opendate=db_result($res,$tmp,"open_date");

		/* now, get all the users */
		$sql2='select * from users where user_id in '.$users.' and user_id>100';
		$userres=db_query($sql2);
		for ($usercount=0;$usercount<db_numrows($userres);$usercount++){
			$mailto=db_result($userres,$usercount,"email");
			$language=db_result($userres,$usercount,"language");
			$Lang = new BaseLanguage() ;
			$Lang->loadLanguageID($language);
			$subject=$Lang->getText('send_pending_items_mail','tracker_subject');
			$messagebody=stripcslashes($Lang->getText('send_pending_items_mail','tracker_message',array( $artifact,$opendate,$summary,  $hyperlink,$status_name, $realopendate,db_result($res,$tmp,'assigned_realname'), db_result($res,$tmp,'submitted_realname'),  db_result($res,$tmp,'details') )));
			/* and finally send the email */
			util_send_message($mailto,$subject,$messagebody);	
		}
	}
	cron_entry(19,db_error());
}


?>
