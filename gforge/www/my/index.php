<?php
/**
  *
  * SourceForge User's Personal Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('vote_function.php');

global $G_SESSION;

if (session_loggedin() || $sf_user_hash) {

	/*
	 *  If user has valid "remember-me" hash, instantiate not-logged in
	 *  session for one.
	 */
	if (!session_loggedin()) {
			list($user_id,$hash)=explode('_',$sf_user_hash);
			$sql="SELECT * 
			FROM users 
			WHERE user_id='".$user_id."' AND user_pw LIKE '".$hash."%'";

		$result=db_query($sql);
		$rows=db_numrows($result);
		if (!$result || $rows != 1) {
			exit_not_logged_in();
		}
		$user_id=db_result($result,0,'user_id');
		$G_SESSION=user_get_object($user_id,$result);
	}

	echo site_user_header(array('title'=>$Language->getText('my','title',user_getname()),'pagename'=>'my','titlevals'=>array(user_getname())));
	?>

	<p>
    <?php echo $Language->getText('my', 'about_blurb'); ?>
	<p>
	<table width="100%" border="0">
	<tr><td valign="TOP" width="50%">
	<?php
	/*
		Artifacts
	*/
	$last_group=0;
	echo $HTML->boxTop($Language->getText('my', 'assigneditems'));
	
	$sql="SELECT g.group_name,agl.name,agl.group_id,a.group_artifact_id,
		a.assigned_to,a.summary,a.artifact_id,a.priority 
		FROM artifact a, groups g, artifact_group_list agl 
		WHERE 
		a.group_artifact_id=agl.group_artifact_id 
		AND agl.group_id=g.group_id 
    AND g.status = 'A'
		AND a.assigned_to='". user_getid() ."' 
		AND a.status_id='1' 
		AND g.status='A'
		ORDER BY agl.group_id,a.group_artifact_id,a.assigned_to,a.status_id";
 
	$result=db_query($sql);
	$rows=db_numrows($result);
	
	if ($rows > 0) {
		for ($i=0; $i < $rows; $i++) {
			if (db_result($result,$i,'group_artifact_id') != $last_group) {
				echo '
				<tr><td colspan="2"><strong><a href="/tracker/?group_id='.
				db_result($result,$i,'group_id').'&atid='.
				db_result($result,$i,'group_artifact_id').'">'.
				db_result($result,$i,'group_name').' - '.
				db_result($result,$i,'name').'</a></strong></td></tr>';
			}   
			echo '
			<tr style="background-color:'.html_get_priority_color(db_result($result,$i,'priority')).'">
			<td><a href="/tracker/?func=detail&amp;aid='.
			db_result($result, $i, 'artifact_id').
			'&amp;group_id='.db_result($result, $i, 'group_id').
			'&amp;atid='.db_result($result, $i, 'group_artifact_id').'">'.
			db_result($result, $i, 'artifact_id').'</td>
			<td>' . stripslashes(db_result($result, $i, 'summary')) . '</td></tr>';

			$last_group = db_result($result,$i,'group_artifact_id');
		}   
	} else {
		echo '
		<tr><td colspan="2"><strong>'.$Language->getText('my', 'no_tracker_items_assigned').'</strong></td></tr>';
		echo db_error();
	}   

	$last_group=0;
	echo $HTML->boxMiddle($Language->getText('my', 'submitteditems'),false,false);
	
	$sql="SELECT g.group_name,agl.name,agl.group_id,a.group_artifact_id,
		a.assigned_to,a.summary,a.artifact_id,a.priority 
		FROM artifact a, groups g, artifact_group_list agl 
		WHERE 
		a.group_artifact_id=agl.group_artifact_id 
		AND agl.group_id=g.group_id 
    AND g.status = 'A' 
		AND a.submitted_by='". user_getid() ."' 
		AND a.status_id='1' 
		ORDER BY agl.group_id,a.group_artifact_id,a.submitted_by,a.status_id";
	
	$result=db_query($sql);
	$rows=db_numrows($result);
	
	if ($rows > 0) {
		for ($i=0; $i < $rows; $i++) {
			if (db_result($result,$i,'group_artifact_id') != $last_group) {
				echo '
				<tr><td colspan="2"><strong><a href="/tracker/?group_id='.
				db_result($result,$i,'group_id').'&amp;atid='.
				db_result($result,$i,'group_artifact_id').'">'.
				db_result($result,$i,'group_name').' - '.
				db_result($result,$i,'name').'</a></strong></td></tr>';
			}	
			echo '
			<tr style="background-color:'.html_get_priority_color(db_result($result,$i,'priority')).'">
			<td><a href="/tracker/?func=detail&amp;aid='.
			db_result($result, $i, 'artifact_id').
			'&amp;group_id='.db_result($result, $i, 'group_id').
			'&amp;atid='.db_result($result, $i, 'group_artifact_id').'">'.
			db_result($result, $i, 'artifact_id').'</td>
			<td>' . stripslashes(db_result($result, $i, 'summary')) . '</td></tr>';
			
			$last_group = db_result($result,$i,'group_artifact_id');
		}	
	} else { 
		echo '
		<tr><td colspan="2"><strong>'.$Language->getText('my', 'no_tracker_items_submitted').'</strong></td></tr>';
		echo db_error();
	}


	/*
		Forums that are actively monitored
	*/
	$last_group=0;
	echo $HTML->boxMiddle($Language->getText('my', 'monitoredforum'),false,false);
	$sql="SELECT groups.group_name,groups.group_id,forum_group_list.group_forum_id,forum_group_list.forum_name ".
		"FROM groups,forum_group_list,forum_monitored_forums ".
		"WHERE groups.group_id=forum_group_list.group_id AND groups.status ='A' ".
		"AND forum_group_list.group_forum_id=forum_monitored_forums.forum_id ".
		"AND forum_monitored_forums.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<tr><td colspan="2">'.$Language->getText('my', 'no_monitored_forums').'
		</td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2"><strong><a href="/forum/?group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'group_name').'</a></strong></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center"><a href="/forum/monitor.php?forum_id='.
				db_result($result,$i,'group_forum_id').
				'&amp;stop=1&amp;group_id='.db_result($result,$i,'group_id').'"><img src="/images/ic/trash.png" height="16" width="16" '.
				'border="0" alt="" /></a></td><td width="99%"><a href="/forum/forum.php?forum_id='.
				db_result($result,$i,'group_forum_id').'">'.
				db_result($result,$i,'forum_name').'</a></td></tr>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	/*
		Filemodules that are actively monitored
	*/
	$last_group=0;

	echo $HTML->boxMiddle($Language->getText('my', 'monitoredfile'),false,false);

	$sql="SELECT groups.group_name,groups.group_id,frs_package.name,filemodule_monitor.filemodule_id ".
		"FROM groups,filemodule_monitor,frs_package ".
		"WHERE groups.group_id=frs_package.group_id AND groups.status = 'A' ".
		"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
		"AND filemodule_monitor.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<tr><td colspan="2">'.$Language->getText('my', 'no_monitored_filemodules').'
		</td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2"><strong><a href="/project/?group_id='.
				db_result($result,$i,'group_id').'">'.
				db_result($result,$i,'group_name').'</a></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="MIDDLE"><a href="/project/filemodule_monitor.php?filemodule_id='.
			db_result($result,$i,'filemodule_id').
			'&amp;group_id='.db_result($result,$i,'group_id'). '&amp;stop=1"><img src="/images/ic/trash.png" height="16" width="16" '.
			'BORDER=0"></a></td><td width="99%"><a href="/project/showfiles.php?group_id='.
			db_result($result,$i,'group_id').'">'.
			db_result($result,$i,'name').'</a></td></tr>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	echo $HTML->boxBottom();

//second column of "my" page

	?>
	</td><td valign="top" width="50%">
	<?php
	/*
		Tasks assigned to me
	*/
	$last_group=0;
	echo $HTML->boxTop($Language->getText('my', 'tasks'));

	$sql="SELECT groups.group_name,project_group_list.project_name,project_group_list.group_id, ".
		"project_task.group_project_id,project_task.priority,project_task.project_task_id,project_task.summary,project_task.percent_complete ".
		"FROM groups,project_group_list,project_task,project_assigned_to ".
		"WHERE project_task.project_task_id=project_assigned_to.project_task_id ".
		"AND project_assigned_to.assigned_to_id='".user_getid()."' AND project_task.status_id='1'  ".
		"AND project_group_list.group_id=groups.group_id ".
		"AND project_group_list.group_project_id=project_task.group_project_id AND groups.status = 'A'".
		"ORDER BY group_name,project_name";

	$result=db_query($sql);
	$rows=db_numrows($result);

	if ($rows > 0) {
		for ($i=0; $i < $rows; $i++) {
			/* Deduce summary style */
			$style_begin='';
			$style_end='';
			if (db_result($result,$i,'percent_complete')==100) {
				$style_begin='<span style="text-decoration:underline">';
				$style_end='</span>';
			}
			if (db_result($result,$i,'group_project_id') != $last_group) {
				echo '
				<tr><td colspan="2"><strong><a href="/pm/task.php?group_id='.
				db_result($result,$i,'group_id').'&amp;group_project_id='.
				db_result($result,$i,'group_project_id').'">'.
				db_result($result,$i,'group_name').' - '.
				db_result($result,$i,'project_name').'</a></strong></td></tr>';
			}
			echo '
			<tr style="background-color:'.html_get_priority_color(db_result($result,$i,'priority')).'">
			<td><a href="/pm/task.php?func=detailtask&amp;project_task_id='.
			db_result($result, $i, 'project_task_id').
			'&amp;group_id='.db_result($result, $i, 'group_id').
			'&amp;group_project_id='.db_result($result, $i, 'group_project_id').'">'.
			db_result($result, $i, 'project_task_id').'</td>
			<td>'.$style_begin.stripslashes(db_result($result, $i, 'summary')).$style_end.'</td></tr>';

			$last_group = db_result($result,$i,'group_project_id');
		}
	} else {
		echo '
		<tr><td colspan="2"><strong>'.$Language->getText('my', 'no_open_tasks').'</strong></td></tr>';
		echo db_error();
	}


	/*
		DEVELOPER SURVEYS

		This needs to be updated manually to display any given survey
	*/

	$sql="SELECT * from survey_responses ".
		"WHERE survey_id='1' AND user_id='".user_getid()."' AND group_id='1'";

	$result=db_query($sql);

	echo $HTML->boxMiddle($Language->getText('my', 'survey'),false,false);

	if (db_numrows($result) < 1) {
		show_survey(1,1);
	} else {
		echo '
		<tr><td colspan="2"><strong>'.$Language->getText('my','survey_taken').'</strong></td></tr>';
	}

	/*
	 * Pending projects and news bytes
	 */
	$admingroup = group_get_object (1) ;
	exit_assert_object($admingroup,'Group');
	$perm =& $admingroup->getPermission( session_get_user() );
	if ($perm && is_object($perm) && $perm->isAdmin()) {
                $sql="SELECT group_name FROM groups where status='P';";
                $result=db_query($sql);
                $rows=db_numrows($result);
                if ($rows) {
                        echo $HTML->boxMiddle($Language->getText('my','pending_projects'), false, false);
		       
                        echo "<tr><td colspan=\"2\">";

			if ($rows==1){
			  echo $Language->getText('my','pending_projects_1');
			}
			else{
			  echo $Language->getText('my','pending_projects_2',$rows);		
			}
			
			/*    echo (($rows!=1)?"are ": "is "). "$rows project";
                        echo (($rows!=1)?"s":"");
			*/
                        echo " <a href=\"/admin/approve-pending.php\">";
			echo $Language->getText('my','pending_projects_3');
                        echo "</a>.</td></tr>";
                }
	}
	$newsgroup = group_get_object ($GLOBALS['sys_news_group']) ;
	exit_assert_object($newsgroup,'Group');
	$perm =& $newsgroup->getPermission( session_get_user() );
	if ($perm && is_object($perm) && $perm->isAdmin()) {
                $sql="SELECT * FROM news_bytes WHERE is_approved=0";
                $result=db_query($sql);
                $rows=db_numrows($result);
                if ($rows) {
                        echo $HTML->boxMiddle($Language->getText('my','pending_news_bytes'), false, false);


			if ($rows==1){
			  echo $Language->getText('my','pending_news_bytes_1');
			}
			else{
			  echo $Language->getText('my','pending_news_bytes_2',$rows);		
			}
			
	
			echo " <a href=\"/news/admin/?group_id=".$GLOBALS['sys_news_group']."\">";

			echo $Language->getText('my','pending_news_bytes_3');
                        echo "</a>.</td></tr>";


                       
		}
	}		
	/*
		   Personal bookmarks
	*/
	echo $HTML->boxMiddle($Language->getText('my', 'bookmarks'),false,false);

	$result = db_query("SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where ".
		"user_id='". user_getid() ."' ORDER BY bookmark_title");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<tr><td colspan="2"><strong>'.$Language->getText('my', 'no_bookmarks').'</strong></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center">
			<a href="/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">
			<img src="/images/ic/trash.png" height="16" width="16" border="0" alt="" /></a></td>
			<td><strong><a href="'. db_result($result,$i,'bookmark_url') .'">'.
			db_result($result,$i,'bookmark_title') .'</a></strong> &nbsp;
			<span style="font-size:small"><a href="/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">['.$Language->getText('general','edit').']</a></span></td></tr>';
		}
	}

	/*
		PROJECT LIST
	*/

	echo $HTML->boxMiddle($Language->getText('my', 'projects'),false,false);
	// Include both groups and foundries; developers should be similarly
	// aware of membership in either.
	$result = db_query("SELECT groups.group_name,"
		. "groups.group_id,"
		. "groups.unix_group_name,"
		. "groups.status,"
		. "groups.type,"
		. "user_group.admin_flags "
		. "FROM groups,user_group "
		. "WHERE groups.group_id=user_group.group_id "
		. "AND user_group.user_id='". user_getid() ."' "
		. "AND groups.status='A'");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<tr><td colspan=\"2\"><strong>'.$Language->getText('my', 'no_projects').'</strong></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {

			$admin_flags = db_result($result, $i, 'admin_flags');
			if (stristr($admin_flags, 'A')) {
				$img="trash-x.png";
			} else {
				$img="trash.png";
			}

			if (db_result($result, $i, 'type')==2) {
				$type = 'foundry';
			} else {
				$type = 'projects';
			}

			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center">
			<a href="rmproject.php?group_id='. db_result($result,$i,'group_id') .'">
			<img src="/images/ic/'.$img.'" alt="Delete" height="16" width="16" border="0" /></a></td>
			<td><a href="/'.$type.'/'. db_result($result,$i,'unix_group_name') .'/">'. htmlspecialchars(db_result($result,$i,'group_name')) .'</a></td></tr>';
		}
	}
	echo $HTML->boxBottom();

	echo '</td></tr>


	<!--  Bottom Row   -->


	<tr><td colspan=2>';

	echo show_priority_colors_key();

	echo '
	</td></tr>

	</table>';

	echo site_user_footer(array());

} else {

	exit_not_logged_in();

}

?>
