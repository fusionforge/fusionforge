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

if (session_loggedin()) {

	$u =& session_get_user();

	if ($submit) {
		// set $is_public
		if ($is_public) {
			$is_public = '1';
		} else {
			$is_public = '0';
		}
		//make changes to the database
		if ($update) {
			//updating an existing diary entry
			$res=db_query("UPDATE user_diary SET summary='". htmlspecialchars($summary) ."',details='". htmlspecialchars($details) ."',is_public='$is_public' ".
			"WHERE user_id='". user_getid() ."' AND id='$diary_id'");
			if ($res && db_affected_rows($res) > 0) {
				$feedback .= ' Diary Updated ';
			} else {
				echo db_error();
				$feedback .= ' Nothing Updated ';
			}
		} else if ($add) {
			//inserting a new diary entry

			$sql="INSERT INTO user_diary (user_id,date_posted,summary,details,is_public) VALUES ".
			"('". user_getid() ."','". time() ."','". htmlspecialchars($summary) ."','". htmlspecialchars($details) ."','$is_public')";
			$res=db_query($sql);
			if ($res && db_affected_rows($res) > 0) {
				$feedback .= ' Item Added ';
				if ($is_public) {

					//send an email if users are monitoring
					$sql="SELECT users.email from user_diary_monitor,users ".
					"WHERE user_diary_monitor.user_id=users.user_id ".
					"AND user_diary_monitor.monitored_user='". user_getid() ."'";

					$result=db_query($sql);
					$rows=db_numrows($result);

					if ($result && $rows > 0) {
						$tolist=implode(util_result_column_to_array($result),', ');

						$to = ''; // send to noreply@
						$subject = "[ SF User Notes: ". $u->getRealName() ."] ".stripslashes($summary);

						$body = util_line_wrap(stripslashes($details)).
						"\n\n______________________________________________________________________".
						"\nYou are receiving this email because you elected to monitor this user.".
						"\nTo stop monitoring this user, login to ".$GLOBALS['sys_name']." and visit: ".
						"\nhttp://$GLOBALS[sys_default_domain]/developer/monitor.php?diary_user=". user_getid();

						util_send_message($to, $subject, $body, $to, $tolist); 

						$feedback .= " email sent - ($rows) people monitoring ";

					} else {
						$feedback .= ' email not sent - no one monitoring ';
						echo db_error();
					}

				} else {
					//don't send an email to monitoring users
					//since this is a private note
				}
			} else {
				$feedback .= ' Error Adding Item ';
				echo db_error();
			}
		}


	}


	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='". user_getid() ."' AND id='$diary_id'";
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			$feedback .= ' Entry not found or does not belong to you ';
			$proc_str='add';
			$info_str='Add a New Entry';
		} else {
			$proc_str='update';
			$info_str='Update an Entry';
			$_summary=db_result($res,0,'summary');
			$_details=db_result($res,0,'details');
			$_is_public=db_result($res,0,'is_public');
			$_diary_id=db_result($res,0,'id');
		}
	} else {
		$proc_str='add';
		$info_str='Add a New Entry';
	}

	echo site_user_header(array('title'=>'My Diary And Notes','pagename'=>'my_diary'));

	echo '
	<p>&nbsp;</p>
	<h3>'. $info_str .'</h3>
	<p>
	<table>
	<form action="'. $PHP_SELF .'" method="post">
	<input type="hidden" name="'. $proc_str .'" value="1">
	<input type="hidden" name="diary_id" value="'. $_diary_id .'">
	<tr><td colspan="2"><strong>Summary:</strong><br />
		<input type="text" name="summary" size="45" maxlength="60" value="'. $_summary .'" />
	</td></tr>

	<tr><td colspan="2"><strong>Details:</strong><br />
		<textarea name="details" rows="15" cols="60" wrap="hard">'. $_details .'</textarea>
	</td></tr>
	<tr><td colspan="2">
		<p>
		<input type="submit" name="submit" value="SUBMIT ONLY ONCE" />
		&nbsp; <input type="checkbox" name="is_public" value="1" '. (($_is_public)?'checked=\"checked\"':'') .' /> Is Public
		</p>
		<p>If marked as public, your entry will be mailed to any
		monitoring users when it is first submitted.
		</p>
		</form>
	</td></tr>

	</table></p>

	<p>';

	echo $HTML->boxTop('Existing Diary And Note Entries');

	$sql="SELECT * FROM user_diary WHERE user_id='". user_getid() ."' ORDER BY id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<strong>You Have No Diary Entries</strong>';
		echo db_error();
	} else {
		echo '&nbsp;</td></tr>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'. $PHP_SELF .'?diary_id='.
				db_result($result,$i,'id').'">'.db_result($result,$i,'summary').'</a></td>'.
				'<td>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" style="background-color:'.$HTML->COLOR_CONTENT_BACK.'">&nbsp;</td></tr>';
	}

	echo $HTML->boxBottom();

	echo site_user_footer(array());

} else {

	exit_not_logged_in();

}

?>
