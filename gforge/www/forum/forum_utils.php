<?php
/**
  *
  * SourceForge Forums Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: forum_utils.php,v 1.205 2001/05/22 19:42:19 pfalcon Exp $
  *
  */


/*

	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

*/

require_once('pre.php');
require_once('www/news/news_utils.php');

function forum_header($params) {
	global $DOCUMENT_ROOT,$HTML,$group_id,$forum_name,$thread_id,$msg_id,$forum_id,$REQUEST_URI,$sys_datefmt,$et,$et_cookie,$sys_news_group,$Language;

	$params['group']=$group_id;
	$params['toptab']='forums';

	/*

		bastardization for news

		Show icon bar unless it's a news forum

	*/
	if ($group_id == $sys_news_group) {
		//this is a news item, not a regular forum
		if ($forum_id) {
			/*
				Show this news item at the top of the page
			*/
			$sql="SELECT * FROM news_bytes WHERE forum_id='$forum_id'";
			$result=db_query($sql);


			//backwards shim for all "generic news" that used to be submitted
			//as of may, "generic news" is not permitted - only project-specific news
	       		if (db_result($result,0,'group_id') != $sys_news_group) {
				$params['group']=db_result($result,0,'group_id');
				$params['toptab']='news';
				site_project_header($params);
			} else {
				$HTML->header($params);
				echo '
					<H2>'.$GLOBALS['sys_name'].' <A HREF="/news/">'.$Language->getText('forum_utils','news').'</A></H2><P>';
			}


			echo '<TABLE><TR><TD VALIGN="TOP">';
			if (!$result || db_numrows($result) < 1) {
				echo '
					<h3>'.$Language->getText('forum_utils','nonewsitem').'</h3>';
			} else {
				echo '
				<B>Posted By:</B> '.user_getname( db_result($result,0,'submitted_by')).'<BR>
				<B>Date:</B> '. date($sys_datefmt,db_result($result,0,'date')).'<BR>
				<B>Summary:</B><A HREF="/forum/forum.php?forum_id='.db_result($result,0,'forum_id').'">'. db_result($result,0,'summary').'</A>
				<P>
				'. util_make_links( nl2br( db_result($result,0,'details')));

				echo '<P>';
			}
			echo '</TD><TD VALIGN="TOP" WIDTH="35%">';
			//echo $HTML->box1_top('Latest News',0,$GLOBALS['COLOR_LTBACK2']);
			echo $HTML->box1_top($Language->getText('forum_utils','latest'));
			echo news_show_latest($sys_news_group,5,false);
			echo $HTML->box1_bottom();
			echo '</TD></TR></TABLE>';
		} else {
			site_project_header($params);
		}
	} else {
		//this is just a regular forum, not a news item
		site_project_header($params);
	}

	/*
		Show horizontal forum links
	*/
	if ($forum_id && $forum_name) {
		echo '<P><H3>'.$Language->getText('forum_utils','discussionforum').' <A HREF="/forum/forum.php?forum_id='.$forum_id.'">'.$forum_name.'</A></H3>';
	}
	echo '<P><B>';

	if ($forum_id && user_isloggedin() ) {
		echo '<A HREF="/forum/monitor.php?forum_id='.$forum_id.'">' . 
			html_image('images/ic/check.png','16','15',array()).' '.$Language->getText('forum_utils','monitor').'</A> | '.
			'<A HREF="/forum/save.php?forum_id='.$forum_id.'">';
		echo  html_image('images/ic/save.png','24','24',array()) .' '.$Language->getText('forum_utils','saveplace').'</A> | ';
	}

	echo '  <A HREF="/forum/admin/?group_id='.$group_id.'">'.$Language->getText('forum_utils','admin').'</A></B>';
	echo '<P>';
}

function forum_footer($params) {
	global $group_id,$HTML,$sys_news_group;
	/*
		if general news, show general site footer

		Otherwise, show project footer
	*/

	//backwards compatibility for "general news" which is no longer permitted to be submitted
	if ($group_id == $sys_news_group) {
		$HTML->footer($params);
	} else {
		site_project_footer($params);
	}
}

function forum_create_forum($group_id,$forum_name,$is_public=1,$create_default_message=1,$description='') {
	global $feedback,$Language;
	/*
		Adding forums to this group
	*/
	$sql="INSERT INTO forum_group_list (group_id,forum_name,is_public,description) ".
		"VALUES ('$group_id','". htmlspecialchars($forum_name) ."','$is_public','". htmlspecialchars($description) ."')";

	$result=db_query($sql);
	if (!$result) {
		$feedback .= " ".$Language->getText('forum_utils','erroradd')." ";
	} else {
		$feedback .= " ".$Language->getText('forum_utils','added')." ";
	}
	$forum_id=db_insertid($result,'forum_group_list','group_forum_id');

	if ($create_default_message) {
		//set up a cheap default message
		$result2=db_query("INSERT INTO forum ".
			"(group_forum_id,posted_by,subject,body,date,is_followup_to,thread_id) ".
			"VALUES ('$forum_id','100','Welcome to $forum_name',".
			"'Welcome to $forum_name','".time()."','0','".get_next_thread_id()."')");
	}
	return $forum_id;
}

function make_links ($data="") {
	//moved make links to /include/utils.php
	util_make_links($data);
}

function get_forum_name($id) {
	global $Language;
	/*
		Takes an ID and returns the corresponding forum name
	*/
	$sql="SELECT forum_name FROM forum_group_list WHERE group_forum_id='$id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		return $Language->getText('forum_utils','notfound');
	} else {
		return db_result($result, 0, "forum_name");
	}

}

function forum_show_a_nested_message ( &$result ) {
	/*
	
		accepts a database result handle to display a single message
		in the format appropriate for the nested messages

	*/
	global $sys_datefmt,$Language;
	/*
		See if this message is new or not
		If so, highlite it in bold
	*/
	if (get_forum_saved_date($result['group_forum_id']) < $result['date']) {
		$bold_begin='<B>';
		$bold_end='</B>';
	}
	$ret_val = '
		<TABLE BORDER="0">
			<TR>
				<TD BGCOLOR="#DDDDDD" NOWRAP>'.$Language->getText('forum_utils','by').' <A HREF="/users/'.
					$result['user_name'] .'/">'. 
					$result['user_name'] .'</A>'.
					' ( '. $result['realname'] . ' ) '.
					'<BR><A HREF="/forum/message.php?msg_id='.
					$result['msg_id'] .'">'.
					html_image("images/msg.png","10","12",array("BORDER"=>"0")) .
					$bold_begin.$result['subject'] .' [ '.$Language->getText('forum_utils','reply').' ]'. $bold_end .'</A> &nbsp; '.
					'<BR>'. date($sys_datefmt,$result['date']) .'
				</TD>   
			</TR>
			<TR>
				<TD>
					'. util_make_links( nl2br ( $result['body'] ) ) .'
				</TD>
			</TR>
		</TABLE>';
	return $ret_val;
}       

function forum_show_nested_messages ( &$msg_arr, $msg_id ) {
	global $total_rows,$sys_datefmt;

	$rows=count($msg_arr[$msg_id]);
	$ret_val='';

	if ($msg_arr[$msg_id] && $rows > 0) {
		$ret_val .= '
			<UL>';

		/*

			iterate and show the messages in this result

			for each message, recurse to show any submessages

		*/
		for ($i=($rows-1); $i >= 0; $i--) {
			//      increment the global total count
			$total_rows++;

			//      show the actual nested message
			$ret_val .= forum_show_a_nested_message ($msg_arr[$msg_id][$i]).'<P>';

			if ($msg_arr[$msg_id][$i]['has_followups'] > 0) {
				//      Call yourself if there are followups
				$ret_val .= forum_show_nested_messages ( $msg_arr,$msg_arr[$msg_id][$i]['msg_id'] );
			}
		}
		$ret_val .= '
			</UL>';
	} else {
		//$ret_val .= "<P><B>no messages actually follow up to $msg_id</B>";
	}

	return $ret_val;
}

function show_thread( $thread_id ) {
	/*
		Takes a thread_id and fetches it, then invokes show_submessages to nest the threads

		$et is whether or not the forum is "expanded" or in flat mode
	*/
	global $total_rows,$sys_datefmt,$is_followup_to,$subject,$forum_id,$current_message,$Language;

	$sql="SELECT forum.group_forum_id,users.user_name,users.realname,forum.has_followups, ".
		"users.user_id,forum.msg_id,forum.subject,forum.thread_id, ".
		"forum.body,forum.date,forum.is_followup_to ".
		"FROM forum,users ".
		"WHERE forum.thread_id='$thread_id' ".
		"AND users.user_id=forum.posted_by ".
		"ORDER BY msg_id ASC";

	$result=db_query($sql);

	$total_rows=0;

	if (!$result || db_numrows($result) < 1) {
		return $Language->getText('forum_utils','brokenthread');
	} else {
		/*
			Build associative array containing row information
		*/
		while ($row=db_fetch_array($result)) {
			$msg_arr["$row[is_followup_to]"][]=$row;
		}

		/*
			Build table header row
		*/
		$title_arr=array();
		$title_arr[]=$Language->getText('forum_utils','thread');
		$title_arr[]=$Language->getText('forum_utils','author');
		$title_arr[]=$Language->getText('forum_utils','date');

		$ret_val .= html_build_list_table_top ($title_arr);

		reset($msg_arr["0"]);
		$thread =& $msg_arr["0"][0];

		//echo "<BR>count: ". count($msg_arr["0"]) ." thread: ". $thread['thread_id'];

		$ret_val .= '<TR BGCOLOR="'. html_get_alt_row_color($total_rows) .'"><TD>'. 
			(($current_message != $thread['msg_id'])?'<A HREF="/forum/message.php?msg_id='.$thread['msg_id'].'">':'').
			html_image("images/msg.png","10","12",array("BORDER"=>"0"));
		/*
			See if this message is new or not
		*/
		if (get_forum_saved_date($thread['group_forum_id']) < $thread['date']) { $ret_val .= '<B>'; }

		$ret_val .= $thread['subject'] .'</A></TD>'.
			'<TD>'. $thread['user_name'] .'</TD>'.
			'<TD>'.date($sys_datefmt, $thread['date'] ).'</TD></TR>';

		/*
			Now call the recursive function to show nested messages
		*/
		if ( $thread['has_followups'] > 0) {
			$ret_val .= show_submessages($msg_arr,$thread['msg_id'],1);
		}

		/*
			end table
		*/
		$ret_val .= '</TABLE>';
	}
	return $ret_val;
}

function show_submessages($msg_arr, $msg_id, $level) {
	/*
		Recursive. Selects this message's id in this thread, 
		then checks if any messages are nested underneath it. 
		If there are, it calls itself, incrementing $level
		$level is used for indentation of the threads.
	*/
	global $total_rows,$sys_datefmt,$forum_id,$current_message;

	$rows=count($msg_arr[$msg_id]);

	if ($rows > 0) {
		for ($i=($rows-1); $i >= 0; $i--) {
			/*
				Is this row's background shaded or not?
			*/
			$total_rows++;

			$ret_val .= '
				<TR BGCOLOR="'. html_get_alt_row_color($total_rows) .'"><TD NOWRAP>';
			/*
				How far should it indent?
			*/
			for ($i2=0; $i2<$level; $i2++) {
				$ret_val .= ' &nbsp; &nbsp; &nbsp; ';
			}

			/*
				If it this is the message being displayed, don't show a link to it
			*/
			$ret_val .= (($current_message != $msg_arr[$msg_id][$i]['msg_id'])?
				'<A HREF="/forum/message.php?msg_id='. $msg_arr[$msg_id][$i]['msg_id'].'">':'').
				html_image("images/msg.png","10","12",array("BORDER"=>"0"));
			/*
				See if this message is new or not
			*/
			if (get_forum_saved_date($forum_id) < $msg_arr[$msg_id][$i]['date']) { $ret_val .= '<B>'; }

			$ret_val .= $msg_arr[$msg_id][$i]['subject'] .'</A></TD>'.
				'<TD>'. $msg_arr[$msg_id][$i]['user_name'] .'</TD>'.
				'<TD>'.date($sys_datefmt, $msg_arr[$msg_id][$i]['date'] ).'</TD></TR>';

			if ($msg_arr[$msg_id][$i]['has_followups'] > 0) {
				/*
					Call yourself, incrementing the level
				*/
				$ret_val .= show_submessages($msg_arr,$msg_arr[$msg_id][$i]['msg_id'],($level+1));
			}
		}
	}
	return $ret_val;
}

function get_next_thread_id() {
	global $sys_database_type,$Language;

	if ($sys_database_type=='mysql') {
		/*
			Get around limitation in MySQL - Must use a separate table with an auto-increment
		*/
		$result=db_query("INSERT INTO forum_thread_id VALUES ('')");

		if (!$result) {
			echo '<H1>'.$Language->getText('forum_utils','error').'</H1>';
			echo db_error();
			exit;
		} else {
			return db_insertid($result,'forum_thread_id','thread_id');
		}
	} else {
		$result=db_query("SELECT nextval('forum_thread_seq')");
		if (!$result || db_numrows($result) < 1) {
			echo db_error();
			return false;
		} else {
			return db_result($result,0,0);
		}
	}
}

function get_forum_saved_date($forum_id) {
	/*
		return the save_date for this user
	*/
	global $forum_saved_date;

	if ($forum_saved_date["$forum_id"]) {
		return $forum_saved_date["$forum_id"];
	} else {
		if (user_isloggedin() && $forum_id) {
			$sql="SELECT save_date FROM forum_saved_place 
				WHERE user_id='".user_getid()."' AND forum_id='$forum_id';";
			$result = db_query($sql);
			if ($result && db_numrows($result) > 0) {
				$forum_saved_date["$forum_id"]=db_result($result,0,'save_date');
				return $forum_saved_date["$forum_id"];
			} else {
				//highlight new messages from the past week only
				$forum_saved_date["$forum_id"]=(time()-604800);
				return $forum_saved_date["$forum_id"];
			}
		} else {
			//highlight new messages from the past week only
			$forum_saved_date["$forum_id"]=(time()-604800);
			return $forum_saved_date["$forum_id"];
		}
	}
}

/**
 *	assumes $allow_anonymous var is setup correctly
 *	added checks and tests to allow anonymous posting
 */
function post_message($thread_id, $is_followup_to, $subject, $body, $group_forum_id) {
	global $feedback,$allow_anonymous,$Language;
	if (user_isloggedin() || $allow_anonymous) {
		if (!$group_forum_id) {
			$feedback=$Language->getText('forum_utils','noid');
			return false;
		}
		if (!$body || !$subject) {
			$feedback=$Language->getText('forum_utils','mustinclude');
			return false;
		}
		if (!user_isloggedin()) {
			$user_id=100;
		} else {
			$user_id=user_getid();
		}

	//see if that message has been posted already for all the idiots that double-post
		$res3=db_query("SELECT * FROM forum ".
			"WHERE is_followup_to='$is_followup_to' ".
			"AND subject='".  htmlspecialchars($subject) ."' ".
			"AND group_forum_id='$group_forum_id' ".
			"AND posted_by='$user_id'");

		if (db_numrows($res3) > 0) {
			//already posted this message
			$feedback=$Language->getText('forum_utils','doublepost');
			return false;
		} else {
			echo db_error();
		}
		db_begin();
		if (!$thread_id) {
			$thread_id=get_next_thread_id();
			$is_followup_to=0;
			if (!$thread_id) {
				$feedback .= $Language->getText('forum_utils','nextfailed');
				db_rollback();
				return false;
			}
		} else {
			if ($is_followup_to) {
				//
				//	increment the parent's followup count if necessary
				//
				$res2=db_query("SELECT * FROM forum WHERE msg_id='$is_followup_to' AND group_forum_id='$group_forum_id'");

				if (db_numrows($res2) > 0) {
					//
					//	get thread_id from the parent's row, 
					//	which is more trustworthy than the HTML form
					//
					$thread_id=db_result($res2,0,'thread_id');

					//
					//	now we need to update the first message in 
					//	this thread with the current time
					//
					$res4=db_query("UPDATE forum SET most_recent_date='". time() ."' ".
						"WHERE thread_id='$thread_id' AND is_followup_to='0'");
					if (!$res4 || db_affected_rows($res4) < 1) {
						$feedback=$Language->getText('forum_utils','errormaster');
						db_rollback();
						return false;
					} else {
						//
						//	mark the parent with followups as an optimization later
						//
						$res3=db_query("UPDATE forum SET has_followups='1',most_recent_date='". time() ."' ".
							"WHERE msg_id='$is_followup_to'");
						if (!$res3) {
							$feedback=$Language->getText('forum_utils','errorparent');
							db_rollback();
							return false;
						}
					}
				} else {
					$feedback=$Language->getText('forum_utils','errorfollowup');
					db_rollback();
					return false;
				}
			} else {
				//should never happen except with shoddy 
				//browsers or mucking with the HTML form
				$feedback=$Language->getText('forum_utils','errorfollowup2');
				db_rollback();
				return false;
			}
		}

		$sql="INSERT INTO forum (group_forum_id,posted_by,subject,body,date,is_followup_to,thread_id,most_recent_date) ".
			"VALUES ('$group_forum_id', '$user_id', '". htmlspecialchars($subject) ."', '". htmlspecialchars($body) ."', '". time() ."','$is_followup_to','$thread_id','". time() ."')";

		$result=db_query($sql);

		if (!$result) {
			$feedback .= ' '.$Language->getText('forum_utils','errorfollowup2').' '.db_error();
			db_rollback();
			return false;
		} else {
			$msg_id=db_insertid($result,'forum','msg_id');

			if (!$msg_id) {
				db_rollback();
				$feedback .= $Language->getText('forum_utils','errorinsert');
				return false;
			} else {
				handle_monitoring($group_forum_id,$msg_id);
				db_commit();
				$feedback .= ' '.$Language->getText('forum_utils','posted').' ';
				return true;
			}
		}
	} else {
		$feedback .= '
			<H3>'.$Language->getText('forum_utils','feedbacklogin').'</H3>';
		return false;
	}
}

/**
 *	assumes $allow_anonymous var is set up
 *	added checks and tests to allow anonymous posting
 */
function show_post_form($forum_id, $thread_id=0, $is_followup_to=0, $subject="") {
	global $allow_anonymous,$REQUEST_URI,$Language;

	if (user_isloggedin() || $allow_anonymous) {
		if ($subject) {
			//if this is a followup, put a RE: before it if needed
			if (!eregi('RE:',$subject,$test)) {
				$subject ='RE: '.$subject;
			}
		}

		?>
		<CENTER>
		<FORM ACTION="/forum/forum.php" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="post_message" VALUE="y">
		<INPUT TYPE="HIDDEN" NAME="forum_id" VALUE="<?php echo $forum_id; ?>">
		<INPUT TYPE="HIDDEN" NAME="thread_id" VALUE="<?php echo $thread_id; ?>">
		<INPUT TYPE="HIDDEN" NAME="msg_id" VALUE="<?php echo $is_followup_to; ?>">
		<INPUT TYPE="HIDDEN" NAME="is_followup_to" VALUE="<?php echo $is_followup_to; ?>">
		<TABLE>
		<TR>
			<TD><B><?php echo $Language->getText('forum_utils','subject'); ?></B><BR>
			<INPUT TYPE="TEXT" NAME="subject" VALUE="<?php echo $subject; ?>" SIZE="45" MAXLENGTH="45">
		</TD></TR>
		<TR><TD><B><?php echo $Language->getText('forum_utils','message'); ?></B><BR>
		<TEXTAREA NAME="body" VALUE="" ROWS="10" COLS="50" WRAP="SOFT"></TEXTAREA>
		</TD></TR>
		<TR><TD ALIGN="MIDDLE">
		<B><FONT COLOR="RED"><?php echo $Language->getText('forum_utils','htmltag'); ?></FONT></B>
		<P>
		<?php 
		if (!user_isloggedin()) {
			echo '<B><FONT COLOR="RED">'.$Language->getText('forum_utils','postanon').' <A HREF="/account/login.php?return_to='. urlencode($REQUEST_URI) .'">['.$Language->getText('forum_utils','loggedin').']</A></FONT></B>';
		} 
		?>
		<BR>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="<?php echo $Language->getText('forum_utils','postcomment'); echo ((!user_isloggedin())?' '.$Language->getText('forum_utils','anonymously'):''); ?>">
		</TD></TR></TABLE>
		</FORM>
		</CENTER>
		<?php

	} else {
		echo "<CENTER>";
		echo '<H3><FONT COLOR="RED">'.$Language->getText('forum_utils','couldpostif').' '.
			'<A HREF="/account/login.php?return_to='. urlencode($REQUEST_URI) .'">['.$Language->getText('forum_utils','loggedin').']</A></FONT></H3>';
		echo "</CENTER>";
	}

}

/**
 *	assumes $send_all_posts_to var is set up
 */
function handle_monitoring($forum_id,$msg_id) {
	global $feedback,$send_all_posts_to;
	/*
		Checks to see if anyone is monitoring this forum
		If someone is, it sends them the message in email format
	*/

	$sql="SELECT users.email from forum_monitored_forums,users ".
		"WHERE forum_monitored_forums.user_id=users.user_id AND forum_monitored_forums.forum_id='$forum_id'";

	$result=db_query($sql);
	$rows=db_numrows($result);

	if (($result && $rows > 0) || $send_all_posts_to) {

		$tolist=$send_all_posts_to . ', ' . implode(result_column_to_array($result),', ');

		$sql="SELECT groups.unix_group_name,users.user_name,forum_group_list.forum_name,".
			"forum.group_forum_id,forum.thread_id,forum.subject,forum.date,forum.body ".
			"FROM forum,users,forum_group_list,groups ".
			"WHERE users.user_id=forum.posted_by ".
			"AND forum_group_list.group_forum_id=forum.group_forum_id ".
			"AND groups.group_id=forum_group_list.group_id ".
			"AND forum.msg_id='$msg_id'";

		$result = db_query ($sql);

		if ($result && db_numrows($result) > 0) {
			$body = "\nRead and respond to this message at: ".
				"\nhttp://$GLOBALS[sys_default_domain]/forum/message.php?msg_id=".$msg_id.
				"\nBy: " . db_result($result,0, 'user_name') .
				"\n\n" . util_line_wrap(util_unconvert_htmlspecialchars(db_result($result,0, 'body'))).
				"\n\n______________________________________________________________________".
				"\nYou are receiving this email because you elected to monitor this forum.".
				"\nTo stop monitoring this forum, login to ".$GLOBALS['sys_name']." and visit: ".
				"\nhttp://$GLOBALS[sys_default_domain]/forum/monitor.php?forum_id=$forum_id";

			util_send_mail("noreply@$GLOBALS[sys_default_domain]",
				"[" .db_result($result,0,'unix_group_name'). " - " . db_result($result,0,'forum_name')."] ".util_unconvert_htmlspecialchars(db_result($result,0,'subject')),
				$body,"noreply@$GLOBALS[sys_default_domain]",
				$tolist);
			$feedback .= ' email sent - people monitoring ';
		} else {
			$feedback .= ' email not sent - people monitoring ';
			echo db_error();
		}
	} else {
		$feedback .= ' email not sent - no one monitoring ';
		echo db_error();
	}
}

function recursive_delete($msg_id,$forum_id) {
	/*
		Take a message id and recurse, deleting all followups
	*/

	if ($msg_id=='' || $msg_id=='0' || (strlen($msg_id) < 1)) {
		return 0;
	}

	$sql="SELECT msg_id FROM forum WHERE is_followup_to='$msg_id' AND group_forum_id='$forum_id'";
	$result=db_query($sql);
	$rows=db_numrows($result);
	$count=1;

	for ($i=0;$i<$rows;$i++) {
		$count += recursive_delete(db_result($result,$i,'msg_id'),$forum_id);
	}
	$sql="DELETE FROM forum WHERE msg_id='$msg_id' AND group_forum_id='$forum_id'";
	$toss=db_query($sql);

	return $count;
}

?>
