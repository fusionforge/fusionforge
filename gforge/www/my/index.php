<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');
require ('vote_function.php');

global $G_SESSION;

if (user_isloggedin() || $sf_user_hash) {

	/*
	 *  If user has valid "remember-me" hash, instantiate not-logged in
	 *  session for one.
	 */
	if (!user_isloggedin()) {
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

	echo site_user_header(array('title'=>'My Personal Page'));
	?>

	<H3><?php echo $Language->MY_PERSONAL_PAGE; ?>: <?php print $G_SESSION->getUnixName(); ?></H3>
	<P>
	<?php echo $Language->MY_about_blurb; ?>
	<P>
	<TABLE width="100%" border="0">
	<TR><TD VALIGN="TOP" WIDTH="50%">
	<?php

	/*
		Bugs assigned to or submitted by this person
	*/
	$last_group=0;
	echo $HTML->box1_top('My Bugs',false,false,false);

	$sql="SELECT groups.group_name,bug.group_id,bug.bug_id,bug.priority,bug.summary 
		FROM bug,groups 
		WHERE bug.status_id=1 
		AND groups.group_id=bug.group_id  
		AND bug.assigned_to='".user_getid()."' 
		ORDER BY groups.group_name ASC";

	$result=db_query($sql,100);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_open_bugs.'</TD></TR>';
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<TR><TD COLSPAN="2"><B><A HREF="/bugs/?group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'group_name').'</A></TD></TR>';
			}
			echo '
			<TR BGCOLOR="'.get_priority_color(db_result($result,$i,'priority')).'"><TD><A HREF="/bugs/?func=detailbug&group_id='.
				db_result($result,$i,'group_id').'&bug_id='.db_result($result,$i,'bug_id').
				'">'.db_result($result,$i,'bug_id').'</A></TD>'.
				'<TD>'.stripslashes(db_result($result,$i,'summary')).'</TD></TR>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	/*
		Support Requests for this User
	*/
	$last_group=0;
	echo $HTML->box1_middle('My Support Requests',false,false);

	$sql="SELECT groups.group_name,support.group_id,support.support_id,support.priority,support.summary 
		FROM support,groups 
		WHERE support.support_status_id = '1' 
		AND support.assigned_to='".user_getid()."' 
		AND groups.group_id=support.group_id 
		ORDER BY groups.group_name ASC";

	$result=db_query($sql,100);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_open_request.'</TD></TR>';
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<TR><TD COLSPAN="2"><B><A HREF="/support/?group_id='.
				db_result($result,$i,'group_id').'">'.
				group_getname(db_result($result,$i,'group_id')).'</A></TD></TR>';
			}
			echo '
			<TR BGCOLOR="'.get_priority_color(db_result($result,$i,'priority')).'"><TD><A HREF="/support/?func=detailsupport&group_id='.
				db_result($result,$i,'group_id').'&support_id='.db_result($result,$i,'support_id').
				'">'.db_result($result,$i,'support_id').'</A></TD>'.
				'<TD>'.stripslashes(db_result($result,$i,'summary')).'</TD></TR>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	/*
		Forums that are actively monitored
	*/
	$last_group=0;
	echo $HTML->box1_middle('Monitored Forums',false,false);
	$sql="SELECT groups.group_name,groups.group_id,forum_group_list.group_forum_id,forum_group_list.forum_name ".
		"FROM groups,forum_group_list,forum_monitored_forums ".
		"WHERE groups.group_id=forum_group_list.group_id ".
		"AND forum_group_list.group_forum_id=forum_monitored_forums.forum_id ".
		"AND forum_monitored_forums.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_monitored_forums.'</TD></TR>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<TR bgcolor="'. html_get_alt_row_color($i) .'"><TD COLSPAN="2"><B><A HREF="/forum/?group_id='.
					db_result($result,$i,'group_id').'">'.
					db_result($result,$i,'group_name').'</A></TD></TR>';
			}
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD ALIGN="MIDDLE"><A HREF="/forum/monitor.php?forum_id='.
				db_result($result,$i,'group_forum_id').
				'"><IMG SRC="/images/ic/trash.png" HEIGHT="16" WIDTH="16" '.
				'BORDER=0"></A></TD><TD WIDTH="99%"><A HREF="/forum/forum.php?forum_id='.
				db_result($result,$i,'group_forum_id').'">'.
				stripslashes(db_result($result,$i,'forum_name')).'</A></TD></TR>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	/*
		Filemodules that are actively monitored
	*/
	$last_group=0;

	echo $HTML->box1_middle('Monitored FileModules',false,false);

	$sql="SELECT groups.group_name,groups.group_id,frs_package.name,filemodule_monitor.filemodule_id ".
		"FROM groups,filemodule_monitor,frs_package ".
		"WHERE groups.group_id=frs_package.group_id ".
		"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
		"AND filemodule_monitor.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_monitored_filemodules.'</TD></TR>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<TR bgcolor="'. html_get_alt_row_color($i) .'"><TD COLSPAN="2"><B><A HREF="/project/?group_id='.
				db_result($result,$i,'group_id').'">'.
				db_result($result,$i,'group_name').'</A></TD></TR>';
			}
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD ALIGN="MIDDLE"><A HREF="/project/filemodule_monitor.php?filemodule_id='.
			db_result($result,$i,'filemodule_id').
			'"><IMG SRC="/images/ic/trash.png" HEIGHT="16" WIDTH="16" '.
			'BORDER=0"></A></TD><TD WIDTH="99%"><A HREF="/project/filelist.php?group_id='.
			db_result($result,$i,'group_id').'">'.
			db_result($result,$i,'name').'</A></TD></TR>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	echo $HTML->box1_bottom();

//second column of "my" page

	?>
	</TD><TD VALIGN="TOP" WIDTH="50%">
	<?php
	
	/*
		Tasks assigned to me
	*/
	$last_group=0;
	echo $HTML->box1_top('My Tasks',false,false,false);

	$sql="SELECT groups.group_name,project_group_list.project_name,project_group_list.group_id, ".
		"project_task.group_project_id,project_task.priority,project_task.project_task_id,project_task.summary,project_task.percent_complete ".
		"FROM groups,project_group_list,project_task,project_assigned_to ".
		"WHERE project_task.project_task_id=project_assigned_to.project_task_id ".
		"AND project_assigned_to.assigned_to_id='".user_getid()."' AND project_task.status_id='1'  ".
		"AND project_group_list.group_id=groups.group_id ".
		"AND project_group_list.group_project_id=project_task.group_project_id ORDER BY project_name";

	$result=db_query($sql);
	$rows=db_numrows($result);

	if ($rows > 0) {
		for ($i=0; $i < $rows; $i++) {
			/* Deduce summary style */
			$style_begin='';
			$style_end='';
			if (db_result($result,$i,'percent_complete')==100) {
				$style_begin='<u>';
				$style_end='</u>';
			}
			if (db_result($result,$i,'group_project_id') != $last_group) {
				echo '
				<TR><TD COLSPAN="2"><B><A HREF="/pm/task.php?group_id='.
				db_result($result,$i,'group_id').'&group_project_id='.
				db_result($result,$i,'group_project_id').'">'.
				db_result($result,$i,'group_name').' - '.
				db_result($result,$i,'project_name').'</A></TD></TR>';
			}
			echo '
			<TR BGCOLOR="'.get_priority_color(db_result($result,$i,'priority')).'">
			<TD><A HREF="/pm/task.php?func=detailtask&project_task_id='.
			db_result($result, $i, 'project_task_id').
			'&group_id='.db_result($result, $i, 'group_id').
			'&group_project_id='.db_result($result, $i, 'group_project_id').'">'.
			db_result($result, $i, 'project_task_id').'</TD>
			<TD>'.$style_begin.stripslashes(db_result($result, $i, 'summary')).$style_end.'</TD></TR>';

			$last_group = db_result($result,$i,'group_project_id');
		}
	} else {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_task.'</TD></TR>';
		echo db_error();
	}


	/*
		DEVELOPER SURVEYS

		This needs to be updated manually to display any given survey
	*/

	$sql="SELECT * from survey_responses ".
		"WHERE survey_id='1' AND user_id='".user_getid()."' AND group_id='1'";

	$result=db_query($sql);

	echo $HTML->box1_middle('Quick Survey',false,false);

	if (db_numrows($result) < 1) {
		show_survey(1,1);
	} else {
		echo '
		<TR><TD COLSPAN="2"><B>You have taken your developer survey</B></TD></TR>';
	}

	/*
	 * Pending projects and news bytes
	 */
	if (user_ismember(1, 'A')) {
		$sql="SELECT group_name FROM groups where status='P';";
		$result=db_query($sql);
		$rows=db_numrows($result);
		if ($rows) {
			echo $HTML->box1_middle('Pending Projects', false, false);
			echo "<TR><TD COLSPAN=\"2\">There ";
			echo (($rows!=1)?"are ": "is "). "$rows project";
			echo (($rows!=1)?"s":"");
			echo " awaiting <a href=\"/admin/approve-pending.php\">";
			echo "review</a>.</td></tr>";
		}
	}
	if (user_ismember($GLOBALS['sys_news_group'], 'A')) {
		$sql="SELECT * FROM news_bytes WHERE is_approved=0";
		$result=db_query($sql);
		$rows=db_numrows($result);
		if ($rows) {
			echo $HTML->box1_middle('Pending News Bytes', false, false);
			echo "<TR><TD COLSPAN=\"2\">There ";
			echo (($rows!=1)?"are ": "is "). "$rows news byte";
			echo (($rows!=1)?"s":"");
			echo " awaiting <a href=\"/news/admin/?group_id=".$GLOBALS['sys_news_group']."\">";
			echo "review</a>.</td></tr>";
		}
	}
	/*
		   Personal bookmarks
	*/
	echo $HTML->box1_middle('My Bookmarks',false,false);

	$result = db_query("SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where ".
		"user_id='". user_getid() ."' ORDER BY bookmark_title");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<TR><TD COLSPAN="2">'.$Language->MY_no_bookmarks.'</TD></TR>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD ALIGN="MIDDLE">
			<A HREF="/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">
			<IMG SRC="/images/ic/trash.png" HEIGHT="16" WIDTH="16" BORDER="0"></A></TD>
			<TD><B><A HREF="'. db_result($result,$i,'bookmark_url') .'">'.
			db_result($result,$i,'bookmark_title') .'</A></B> &nbsp;
			<SMALL><A HREF="/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">[Edit]</A></SMALL></TD</TR>';
		}
	}

	/*
		PROJECT LIST
	*/

	echo $HTML->box1_middle('My Projects',false,false);
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
		. "AND groups.status='A' AND groups.is_public=1");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo "<TR><TD COLSPAN=\"2\">$Language->MY_no_projects</TD></TR>";
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$gid = db_result ($result,$i,'group_id') ;
			if (user_ismember ($gid, 'A')) {
				$img="trash-x.png";
			} else {
				$img="trash.png";
			}
			if (db_result ($result, $i, 'type') == 1) {
				$link = "/projects/" . db_result ($result,$i,'unix_group_name') ;
			} else {
				$link = "/foundry/" . db_result ($result,$i,'unix_group_name') ;
			}
 			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD ALIGN="MIDDLE">
			<A href="rmproject.php?group_id='.$gid.'"><IMG SRC="/images/ic/'.$img.'" ALT="DELETE" HEIGHT="16" WIDTH="16" BORDER="0"></A></TD>
			<TD><A href="'.$link.'">'. db_result($result,$i,'group_name') .'</A></TD></TR>';
		}
	}
	echo $HTML->box1_bottom();

	echo '</TD></TR>


	<!--  Bottom Row   -->


	<TR><TD COLSPAN=2>';

	echo show_priority_colors_key();

	echo '
	</TD></TR>

	</TABLE>';

	echo site_user_footer(array());

} else {

	exit_not_logged_in();

}

?>
