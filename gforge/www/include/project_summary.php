<?php

function project_get_total_bug_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM bug WHERE group_id=$group_id");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_open_bug_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM bug WHERE group_id=$group_id AND status_id != 3");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_total_support_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM support WHERE group_id=$group_id");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_open_support_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM support WHERE group_id=$group_id AND support_status_id='1'");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_total_patch_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM patch WHERE group_id=$group_id");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_open_patch_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM patch WHERE group_id=$group_id AND patch_status_id='1'");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_mail_list_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM mail_group_list WHERE group_id=$group_id AND is_public=1");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_survey_count($group_id) {
	$res=db_query("SELECT count(*) AS count from surveys where group_id='$group_id' AND is_active='1'");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}       

function project_get_public_forum_count($group_id) {
	$res = db_query("SELECT count(forum.msg_id) AS count FROM forum,forum_group_list WHERE "
	. "forum_group_list.group_id=$group_id AND forum.group_forum_id=forum_group_list.group_forum_id "
	. "AND forum_group_list.is_public=1");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

function project_get_public_forum_message_count($group_id) {
	$res = db_query("SELECT count(*) AS count FROM forum_group_list WHERE group_id=$group_id AND is_public=1");
	$count=db_result($res,0,'count');
	db_free_result($res);
	return $count;
}

/*

	Build a project summary box that projects can insert into their project pages

*/

function project_summary($group_id,$mode,$no_table) {
	if (!$group_id) {
		return 'Error - No Group ID';
	}
	if (!$mode) {
		$mode='full';
	}

	$project=project_get_object($group_id);
	// ################## forums

	if (!$no_table) {
		$return .= '

		<TABLE BORDER=0 WIDTH="100%"><TR><TD BGCOLOR="#EAECEF">';
	}

	if ($project->usesForum()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/forum/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/notes16.png","20","20",array("BORDER"=>"0","ALT"=>"Forums"));
		$return .= '&nbsp;Forums</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_public_forum_count($group_id) ."</B> messages in ";
			$return .= "<B>". project_get_public_forum_message_count($group_id) ."</B> forums )\n";
		}
	}

	// ##################### Bug tracking

	if ($project->usesBugs()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/bugs/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/bug16b.png","20","20",array("BORDER"=>"0","ALT"=>"Bugs"));
		$return .= '&nbsp;Bug&nbsp;Tracker</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_open_bug_count($group_id) ."</B>";
			$return .= " open bugs, <B>". project_get_total_bug_count($group_id) ."</B> total )";
		}
	}

	// ##################### Support Manager
 
	if ($project->usesSupport()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/support/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/support16b.jpg","20","20",array("BORDER"=>"0","ALT"=>"Support"));
		$return .= '&nbsp;Tech&nbsp;Support</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_open_support_count($group_id) ."</B>";
			$return .= " open requests, <B>". project_get_total_support_count($group_id) ."</B> total )";
		}
	}

	// ##################### Doc Manager

	if ($project->usesDocman()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/docman/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/docman16b.png","20","20",array("BORDER"=>"0","ALT"=>"Docs"));
		$return .= '&nbsp;Doc&nbsp;Manager</A>';
	}

	// ##################### Patch Manager

	if ($project->usesPatch()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/patch/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/patch.png","20","20",array("BORDER"=>"0","ALT"=>"Patches"));
		$return .= '&nbsp;Patch&nbsp;Manager</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_open_patch_count($group_id) ."</B>";
			$return .= " open patches, <B>". project_get_total_patch_count($group_id) ."</B> total )";
		}
	}       

	// ##################### Mailing lists

	if ($project->usesMail()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/mail/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/mail16b.png","20","20",array("BORDER"=>"0","ALT"=>"Mail Lists"));
		$return .= '&nbsp;Mailing&nbsp;Lists</A>';

		if ($mode != 'compact') {
			$return .= " ( <B>". project_get_mail_list_count($group_id) ."</B> public lists )";
		}
	}

	// ##################### Task Manager 

	if ($project->usesPm()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/pm/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/taskman16b.png","20","20",array("BORDER"=>"0","ALT"=>"Tasks"));
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
					<BR> &nbsp; - <A HREF="http://'.$GLOBALS['sys_default_domain'].'/pm/task.php?group_project_id='.db_result($result, $j, 'group_project_id').
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
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/survey/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/survey16b.png","20","20",array("BORDER"=>"0","ALT"=>"Surveys"));
		$return .= "&nbsp;Surveys</A>";
		if ($mode != 'compact') {
			$return .= ' ( <B>'. project_get_survey_count($group_id) .'</B> surveys )';
		}
	}

	// ######################### CVS 

	if ($project->usesCVS()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/cvs/?group_id='.$group_id.'">';
		$return .= html_image("images/ic/cvs16b.png","20","20",array("BORDER"=>"0","ALT"=>"CVS"));
		$return .= "&nbsp;CVS&nbsp;Tree</A>";

		if ($mode != 'compact') {
			$sql = "SELECT SUM(cvs_commits) AS commits,SUM(cvs_adds) AS adds from stats_project where group_id='$group_id'";
			$result = db_query($sql);
			$return .= ' ( <B>'.db_result($result,0,0).'</B> commits, <B>'.db_result($result,0,1).'</B> adds )';
		}
	}

	// ######################## Released Files
	
	if ($project->isActive()) {
		$return .= '

			<HR SIZE="1" NoShade>';
		$return .= '<A href="http://'.$GLOBALS['sys_default_domain'].'/project/showfiles.php?group_id='.$group_id.'">';
		$return .= html_image("images/ic/ftp16b.png","20","20",array("BORDER"=>"0","ALT"=>"FTP"));
		$return .= "&nbsp;Released&nbsp;Files</A>";
	}

	if (!$no_table) {
		$return .= '

		</TD></TR></TABLE>';
	}

	return $return;
}

?>
