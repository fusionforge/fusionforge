<?php
/**
  *
  * SourceForge Forums Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

/*

	Forum written 11/99 by Tim Perdue
	Massive re-write 7/2000 by Tim Perdue (nesting/multiple views/etc)

	Massive optimization 11/00 to eliminate recursive queries

*/

require_once('pre.php');
require_once('www/forum/forum_utils.php');

if ($forum_id) {

	/*
		Set up global vars that are expected by some forum functions
	*/
	$result=db_query("SELECT group_id,forum_name,is_public,allow_anonymous,send_all_posts_to ".
		"FROM forum_group_list ".
		"WHERE group_forum_id='$forum_id'");
	if (!$result || db_numrows($result) < 1) {
		exit_error('ERROR','Forum not found '.db_error());
	}
	$group_id=db_result($result,0,'group_id');
	$forum_name=db_result($result,0,'forum_name');
	$allow_anonymous=db_result($result,0,'allow_anonymous');
	$send_all_posts_to=db_result($result,0,'send_all_posts_to');

	//
	//	Set up local objects
	//
	$g =& group_get_object($group_id);

	if (user_isloggedin()) {
		$u =& session_get_user();
		$perm =& $g->getPermission($u);
	}

	//private forum check
	if (db_result($result,0,'is_public') != 1) {
		if (!user_isloggedin() || (user_isloggedin() && !$perm->isMember())) {
			/*
				If this is a private forum, kick 'em out
			*/
			exit_error('ERROR','Forum is restricted to members of this group');
		}	 
	}


	/*
		if necessary, insert a new message into the forum
	*/
	if ($post_message) {
		if (!post_message($thread_id, $is_followup_to, $subject, $body, $forum_id)) {
			exit_error('ERROR',$feedback);
		} else {
			$feedback='Message Posted Successfully';
			$style='';
			$thread_id='';
		}
	}

	/*
		set up some defaults if they aren't provided
	*/
	if ((!$offset) || ($offset < 0)) {
		$offset=0;
	} 
	if ($thread_id) {
		$style='nested';
	}
	if (!$style || ($style != 'ultimate' && $style != 'flat' && $style != 'nested' && $style != 'threaded')) {
		$style='ultimate';
	}

	if (!$max_rows || $max_rows < 5) {
		$max_rows=25;
	}

	/*
		take care of setting up/saving prefs

		If they're logged in and a "custom set" was NOT just POSTed,
			see if they have a pref set
				if so, use it
			if it was a custom set just posted && logged in, set pref if it's changed
	*/
	if (!$thread_id && user_isloggedin()) {
		$_pref=$style.'|'.$max_rows;
		if ($set=='custom') {
			if ($u->getPreference('forum_style')) {
				if ($_pref == $u->getPreference('forum_style')) {
					//do nothing - pref already stored
				} else {
					//set the pref
					$u->setPreference ('forum_style',$_pref);
				}
			} else {
					//set the pref
					$u->setPreference ('forum_style',$_pref);
			}
		} else {
			if ($u->getPreference('forum_style')) {
				$_pref_arr=explode ('|',$u->getPreference('forum_style'));
				$style=$_pref_arr[0];
				$max_rows=$_pref_arr[1];
			} else {
				//no saved pref and we're not setting 
				//one because this is all default settings
			}
		}
	}
	if (!$style || ($style != 'ultimate' && $style != 'flat' && $style != 'nested' && $style != 'threaded')) {
		$style='ultimate';
	}

	if (!$max_rows || $max_rows < 5) {
		$max_rows=25; 
	}

//echo "<P>style: $style";
	forum_header(array('title'=>$forum_name,'pagename'=>'forum_forum','sectionvals'=>group_getname($group_id),'forum_id'=>$forum_id));

/**
 *
 *	Forum styles include Nested, threaded, flat, ultimate
 *
 *	threaded indents and shows subjects/authors of all messages/followups
 *	nested indents and shows the entirety of all messages/followups
 *	flat shows entiretly of messages in date order descending
 *	ultimate is based roughly on "Ultimate BB"
 *
 */

	//
	//	Don't show the forum view prefs in thread mode
	//
	if (!$thread_id) {
		//create a pop-up select box listing the forums for this project
		//determine if this person can see private forums or not
		if (user_isloggedin() && user_ismember($group_id)) {
			$public_flag='0,1';
		} else {
			$public_flag='1';
		}
		if ($group_id==$GLOBALS['sys_news_group']) {
			echo '<INPUT TYPE="HIDDEN" NAME="forum_id" VALUE="'.$forum_id.'">';
		} else {
			$res=db_query("SELECT group_forum_id,forum_name ".
				"FROM forum_group_list ".
				"WHERE group_id='$group_id' AND is_public IN ($public_flag)");
			$vals=util_result_column_to_array($res,0);
			$texts=util_result_column_to_array($res,1);

			$forum_popup = html_build_select_box_from_arrays ($vals,$texts,'forum_id',$forum_id,false);
		}
		//create a pop-up select box showing options for viewing threads

		$vals=array('nested','flat','threaded','ultimate');
		$texts=array('Nested','Flat','Threaded','Ultimate');

		$options_popup=html_build_select_box_from_arrays ($vals,$texts,'style',$style,false);

		//create a pop-up select box showing options for max_row count
		$vals=array(25,50,75,100);
		$texts=array('Show 25','Show 50','Show 75','Show 100');

		$max_row_popup=html_build_select_box_from_arrays ($vals,$texts,'max_rows',$max_rows,false);

		//now show the popup boxes in a form
		$ret_val .= '
		<TABLE BORDER="0" WIDTH="50%">
			<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
			<INPUT TYPE="HIDDEN" NAME="set" VALUE="custom">
			<INPUT TYPE="HIDDEN" NAME="forum_id" VALUE="'.$forum_id.'">
			<TR><TD><FONT SIZE="-1">'. $forum_popup .
				'</TD><TD><FONT SIZE="-1">'. $options_popup .
				'</TD><TD><FONT SIZE="-1">'. $max_row_popup .
				'</TD><TD><FONT SIZE="-1"><INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="'.$Language->getText('forum_forum','changeview').'">
			</TD></TR>
		</TABLE></FORM>';
	}

	if ($style=='nested') {
		//
		//	if viewing a particular thread, add some limiting SQL
		//
		if ($thread_id) {
			$thread_sql=" AND forum.thread_id='$thread_id' ";
		}

		$sql="SELECT users.user_name,users.realname,forum.has_followups, ".
		"users.user_id,forum.msg_id,forum.subject,forum.thread_id, ".
		"forum.body,forum.date,forum.is_followup_to,forum.most_recent_date,forum.group_forum_id ".
		"FROM forum,users ".
		"WHERE forum.group_forum_id='$forum_id' ".
		$thread_sql .
		"AND users.user_id=forum.posted_by ".
		"ORDER BY forum.most_recent_date DESC";

		$result=db_query($sql,($max_rows+25),$offset);

		echo db_error();

		while ($row=db_fetch_array($result)) {
			$msg_arr["$row[is_followup_to]"][]=$row;
		}

		$rows=count($msg_arr[0]);
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}
		$i=0;
		while (($i < $rows) && ($total_rows < $max_rows)) {
			$thread=$msg_arr["0"][$i];
			
			$total_rows++;
			/* 
				New slashdot-inspired nested threads,
				showing all submessages and bodies
			*/
			$ret_val .= forum_show_a_nested_message ( $thread ).'<BR>';
				
			if ($thread['has_followups'] > 0) {
				//show submessages for this message
				$ret_val .= forum_show_nested_messages ( $msg_arr, $thread['msg_id'] );
			}
			$i++;
		}

	} else if ($style=='threaded') {

		$sql="SELECT users.user_name,users.realname,forum.has_followups, ".
		"users.user_id,forum.msg_id,forum.subject,forum.thread_id, ".
		"forum.body,forum.date,forum.is_followup_to,forum.most_recent_date,forum.group_forum_id ".
		"FROM forum,users ".
		"WHERE forum.group_forum_id='$forum_id' AND users.user_id=forum.posted_by ".
		"ORDER BY forum.most_recent_date DESC";
		
		$result=db_query($sql,($max_rows+25),$offset);

		echo db_error();

		while ($row=db_fetch_array($result)) {
			$msg_arr["$row[is_followup_to]"][]=$row;
		}

		$title_arr=array();
		$title_arr[]=$Language->getText('forum_forum','thread');
		$title_arr[]=$Language->getText('forum_forum','author');
		$title_arr[]=$Language->getText('forum_forum','date');

		$ret_val .= html_build_list_table_top ($title_arr);

		$rows=count($msg_arr[0]);
			 
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}	   
		$i=0;	 
		while (($i < $rows) && ($total_rows < $max_rows)) {
			$thread=$msg_arr["0"][$i];
			$total_rows++;

			$ret_val .= '<TR BGCOLOR="'. html_get_alt_row_color($total_rows) .'"><TD><A HREF="/forum/message.php?msg_id='.
				$thread['msg_id'].'">'.
				html_image("images/msg.png","12","10",array("BORDER"=>"0"));
			/*	  
				See if this message is new or not
				If so, highlite it in bold
			*/
			if (get_forum_saved_date($forum_id) < $thread['date']) {
				$ret_val .= '<B>';
			}
			/*	  
				show the subject and poster
			*/
			$ret_val .= $thread['subject'] .'</A></TD>'.
				'<TD>'. $thread['user_name'] .'</TD>'.
				'<TD>'.date($sys_datefmt,$thread['date']).'</TD></TR>';
				 
			/*
			 
				Show subjects for submessages in this thread

				show_submessages() is recursive

			*/
			if ($thread['has_followups'] > 0) {
				$ret_val .= show_submessages($msg_arr,$thread['msg_id'],1);
			}
			$i++;
		}

		$ret_val .= '</TABLE>';

	} else if ($style=='flat') {

		$sql="SELECT users.user_name,users.realname,forum.has_followups, ".
		"users.user_id,forum.msg_id,forum.subject,forum.thread_id, ".
		"forum.body,forum.date,forum.is_followup_to,forum.group_forum_id ".
		"FROM forum,users ".
		"WHERE forum.group_forum_id='$forum_id' AND users.user_id=forum.posted_by ".
		"ORDER BY forum.msg_id DESC";

		$result=db_query($sql,($max_rows+1),$offset);

		echo db_error();

		$i=0;	 
		while (($row=db_fetch_array($result)) && ($i < $max_rows)) {
			$ret_val .= forum_show_a_nested_message ( $row ).'<BR>';

			$i++;
		}

	} else {
		/*
			This is the view that is most similar to the "Ultimate BB view"
		*/

		$sql="SELECT f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id,".
			"(count(f2.thread_id)-1) AS followups,max(f2.date) AS recent ".
			"FROM forum f, forum f2, users ".
			"WHERE f.group_forum_id='$forum_id' ".
			"AND f.is_followup_to=0 ".
			"AND users.user_id=f.posted_by ".
			"AND f.thread_id=f2.thread_id ".
			"GROUP BY f.most_recent_date,users.user_name,users.realname,users.user_id,f.msg_id,f.subject,f.thread_id ".
			"ORDER BY f.most_recent_date DESC";

		$result=db_query($sql,($max_rows+1),$offset);

		echo db_error();

		$title_arr=array();
		$title_arr[]='Topic';
		$title_arr[]='Topic Starter';
		$title_arr[]='Replies';
		$title_arr[]='Last Post';
	
		$ret_val .= html_build_list_table_top ($title_arr);
		$i=0;
		while (($row=db_fetch_array($result)) && ($i < $max_rows)) {
			$ret_val .= '
				<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD><A HREF="/forum/forum.php?thread_id='.
				$row['thread_id'].'&forum_id='.$forum_id.'">'.
				html_image("images/ic/cfolder15.png","15","13",array("border"=>"0")) . '  &nbsp; ';
			/*	  
					See if this message is new or not
					If so, highlite it in bold
			*/
			if (get_forum_saved_date($forum_id) < $row['recent']) {
					$ret_val .= '<B>';
			}
			/* 
					show the subject and poster
			*/
			$ret_val .= $row['subject'] .'</A></TD>'.
				'<TD>'. $row['user_name'] .'</TD>'.
				'<TD>'. $row['followups'] .'</TD>'.
				'<TD>'.date($sys_datefmt,$row['recent']).'</TD></TR>';
			$i++;
		}

		$ret_val .= '</TABLE>';

	}

	/*
		This code puts the nice next/prev.
	*/
	$ret_val .= '<TABLE WIDTH="100%" BORDER="0">
		<TR BGCOLOR="#EEEEEE"><TD WIDTH="50%">';
	if ($offset != 0) {
		$ret_val .= '<FONT face="Arial, Helvetica" SIZE="3" STYLE="text-decoration: none"><B>
		<A HREF="javascript:history.back()"><B>' .
		html_image("images/t2.png","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . ' Previous Messages</A></B></FONT>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</TD><TD>&nbsp;</TD><TD ALIGN="RIGHT" WIDTH="50%">';

	if (db_numrows($result) > $max_rows) {
		$ret_val .= '<FONT face="Arial, Helvetica" SIZE=3 STYLE="text-decoration: none"><B>
		<A HREF="/forum/forum.php?max_rows='.$max_rows.'&style='.$style.'&offset='.($offset+$i).'&forum_id='.$forum_id.'">
		<B>Next Messages ' .
		html_image("images/t.png","15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . '</A>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</TABLE>';

	echo $ret_val;

	echo '<P>&nbsp;<P>';

	if ($thread_id) {
		//
		//	Viewing a particular thread in nested view
		//
		echo '<CENTER><h3>'.$Language->getText('forum_message', 'msg').'</H3></CENTER>';
				show_post_form($forum_id,$thread_id,$msg_arr["0"][0]['msg_id'],$msg_arr["0"][0]['subject']);
	} else {
		//
		//	Viewing an entire message forum in a given format
		//
		echo '<CENTER><h3>'.$Language->getText('forum_message', 'thread').'</H3></CENTER>';
		show_post_form($forum_id);
	}

	forum_footer(array());

} else {

	exit_error('ERROR','No Forum Chosen');

}

?>
