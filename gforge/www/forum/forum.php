<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002
*/

require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');

if ($forum_id) {

	/*
		Get the group_id based on this forum_id
	*/
	$result=db_query("SELECT group_id
		FROM forum_group_list
		WHERE group_forum_id='$forum_id'");
	if (!$result || db_numrows($result) < 1) {
		exit_error('ERROR','Forum not found '.db_error());
	}
	$group_id=db_result($result,0,'group_id');

	//
	//	Set up local objects
	//
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$f=new Forum($g,$forum_id);
	if (!$f || !is_object($f)) {
		exit_error('Error','Error Getting Forum');
	} elseif ($f->isError()) {
		exit_error('Error',$f->getErrorMessage());
	}

	/*
		if necessary, insert a new message into the forum
	*/
	if ($post_message) {
		$fm=new ForumMessage($f);
		if (!$fm || !is_object($fm)) {
			exit_error('Error','Error Getting New ForumMessage');
		} elseif ($fm->isError()) {
			exit_error('Error','Error Getting New ForumMessage: '.$fm->getErrorMessage());
		}

		if (!$fm->create($subject, $body, $thread_id, $is_followup_to) || $fm->isError()) {
			exit_error('ERROR','Error Creating Forum Message: '.$fm->getErrorMessage());
		} else {
			$feedback=$Language->getText('forum_forum','postsuccess');
			$style='';
			$thread_id='';
		}
	}


	$fmf = new ForumMessageFactory($f);
	if (!$fmf || !is_object($fmf)) {
		exit_error('Error','Error Getting New ForumMessageFactory');
	} elseif ($fmf->isError()) {
		exit_error('Error',$fmf->getErrorMessage());
	}

//echo "<BR> style: $style|max_rows: $max_rows|offset: $offset+";
	$fmf->setUp($offset,$style,$max_rows,$set);

	$style=$fmf->getStyle();
	$max_rows=$fmf->max_rows;
	$offset=$fmf->offset;

//echo "<BR> style: $style|max_rows: $max_rows|offset: $offset+";

	$fh = new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error('Error','Error Getting New ForumHTML');
	} elseif ($fh->isError()) {
		exit_error('Error',$fh->getErrorMessage());
	}

	forum_header(array('title'=>$f->getName(),'pagename'=>'forum_forum',
	'sectionvals'=>$g->getPublicName(),'forum_id'=>$forum_id));

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

	//create a pop-up select box listing the forums for this project
	//determine if this person can see private forums or not
	if (session_loggedin() && user_ismember($group_id)) {
		$public_flag='0,1';
	} else {
		$public_flag='1';
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
	<TABLE BORDER="0" WIDTH="33%">
	<FORM ACTION="'. $PHP_SELF .'" METHOD="GET">
	<INPUT TYPE="HIDDEN" NAME="set" VALUE="custom">
	<INPUT TYPE="HIDDEN" NAME="forum_id" VALUE="'.$forum_id.'">
		<TR><TD><FONT SIZE="-1">'. $options_popup .
			'</TD><TD><FONT SIZE="-1">'. $max_row_popup .
			'</TD><TD><FONT SIZE="-1"><INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="'.
			$Language->getText('forum_forum','changeview').'">
		</TD></TR>
	</TABLE></FORM>
	<P>';

	if ($style=='nested') {

		$msg_arr =& $fmf->nestArray($fmf->getNested());

		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}

		$rows=count($msg_arr["0"]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}

		$i=0;
		while (($i < $rows) && ($total_rows < $max_rows)) {

			$total_rows++;
			/* 
				New slashdot-inspired nested threads,
				showing all submessages and bodies
			*/
			$ret_val .= $fh->showNestedMessage ( $msg_arr["0"][$i] ).'<BR>';
				
			if ( $msg_arr["0"][$i]->hasFollowups() ) {
				//show submessages for this message
				$tempid=$msg_arr["0"][$i]->getID();
//				echo "<P>before showNestedMessages() $tempid | ". count( $msg_arr["$tempid"] );
				$ret_val .= $fh->showNestedMessages ( $msg_arr, $tempid );
			}
			$i++;
		}

	} else if ($style=='threaded') {

		$msg_arr =& $fmf->nestArray($fmf->getThreaded());
		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}

		$title_arr=array();
		$title_arr[]=$Language->getText('forum_forum','thread');
		$title_arr[]=$Language->getText('forum_forum','author');
		$title_arr[]=$Language->getText('forum_forum','date');

		$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);

		$rows=count($msg_arr[0]);
		$avail_rows=$fmf->fetched_rows;
		if ($rows > $max_rows) {
			$rows=$max_rows;
		}	   
		$i=0;	 
		while (($i < $rows) && ($total_rows < $max_rows)) {
			$msg =& $msg_arr["0"][$i];
			$total_rows++;

			$ret_val .= '<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'>
				<TD><A HREF="/forum/message.php?msg_id='.$msg->getID().'">'.
				html_image('ic/msg.png',"10","12",array("BORDER"=>"0"));
			/*	  
				See if this message is new or not
				If so, highlite it in bold
			*/
			if ($f->getSavedDate() < $msg->getPostDate()) {
				$bold_begin='<B>';
				$bold_end='</B>';
			} else {
				$bold_begin='';
				$bold_end='';
			}
			/*	  
				show the subject and poster
			*/
			$ret_val .= $bold_begin.$msg->getSubject() .$bold_end.'</A></TD>'.
				'<TD>'. $msg->getPosterRealName() .'</TD>'.
				'<TD>'. date($sys_datefmt,$msg->getPostDate()) .'</TD></TR>';
				 
			if ($msg->hasFollowups()) {
				$ret_val .= $fh->showSubmessages($msg_arr,$msg->getID(),1);
			}
			$i++;
		}

		$ret_val .= $GLOBALS['HTML']->listTableBottom();

	} else if ($style=='flat' || ($style=='ultimate' && $thread_id)) {

		$msg_arr =& $fmf->getFlat($thread_id);
		if ($fmf->isError()) {
			echo $fmf->getErrorMessage();
		}
		$avail_rows=$fmf->fetched_rows;

		for ($i=0; ($i<count($msg_arr) && ($i < $max_rows)); $i++) {
			$ret_val .= $fh->showNestedMessage ( $msg_arr[$i] ).'<BR>';
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

		$avail_rows=db_numrows($result);

		echo db_error();

		$title_arr=array();
		$title_arr[]=$Language->getText('forum_forum','topic');
		$title_arr[]=$Language->getText('forum_forum','topicstarter');
		$title_arr[]=$Language->getText('forum_forum','replies');
		$title_arr[]=$Language->getText('forum_forum','lastpost');
	
		$ret_val .= $GLOBALS['HTML']->listTableTop ($title_arr);
		$i=0;
		while (($row=db_fetch_array($result)) && ($i < $max_rows)) {
			$ret_val .= '
				<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><TD><A HREF="/forum/forum.php?thread_id='.
				$row['thread_id'].'&forum_id='.$forum_id.'">'.
				html_image('ic/cfolder15.png',"15","13",array("border"=>"0")) . '  &nbsp; ';
			/*	  
					See if this message is new or not
					If so, highlite it in bold
			*/
			if ($f->getSavedDate() < $row['recent']) {
				$bold_begin='<B>';
				$bold_end='</B>';
			} else {
				$bold_begin='';
				$bold_end='';
			}
			/* 
					show the subject and poster
			*/
			$ret_val .= $bold_begin.$row['subject'] .$bold_end.'</A></TD>'.
				'<TD>'. $row['user_name'] .'</TD>'.
				'<TD>'. $row['followups'] .'</TD>'.
				'<TD>'.date($sys_datefmt,$row['recent']).'</TD></TR>';
			$i++;
		}

		$ret_val .= $GLOBALS['HTML']->listTableBottom();

	}

	/*
		This code puts the nice next/prev.
	*/
	$ret_val .= '<TABLE WIDTH="100%" BORDER="0">
		<TR BGCOLOR="#EEEEEE"><TD WIDTH="50%">';
	if ($offset != 0) {
		$ret_val .= '<FONT face="Arial, Helvetica" SIZE="3" STYLE="text-decoration: none"><B>
		<A HREF="javascript:history.back()"><B>' .
		html_image('t2.png',"15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . ' Previous Messages</A></B></FONT>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</TD><TD>&nbsp;</TD><TD ALIGN="RIGHT" WIDTH="50%">';

	if ($avail_rows > $max_rows) {
		$ret_val .= '<FONT face="Arial, Helvetica" SIZE=3 STYLE="text-decoration: none"><B>
		<A HREF="/forum/forum.php?max_rows='.$max_rows.'&style='.$style.'&offset='.($offset+$i).'&forum_id='.$forum_id.'">
		<B>Next Messages ' .
		html_image('t.png',"15","15",array("BORDER"=>"0","ALIGN"=>"MIDDLE")) . '</A>';
	} else {
		$ret_val .= '&nbsp;';
	}

	$ret_val .= '</TABLE>';

	echo $ret_val;
/*
	echo '<P>&nbsp;<P>';

	if (!$thread_id) {
		//
		//	Viewing an entire message forum in a given format
		//
		echo '<CENTER><h3>'.$Language->getText('forum_message', 'thread').'</H3></CENTER>';
		$fh->showPostForm();
	}
*/
	forum_footer(array());

} else {

	exit_error('ERROR','No Forum Chosen');

}

?>
