<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');
require ('vote_function.php');

if (user_isloggedin()) {

	if ($submit) {
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
						$tolist=implode(result_column_to_array($result),', ');

						$body = "To: noreply@$GLOBALS[sys_default_domain]".
						"\nBCC: $tolist".
						"\nSubject: [ SF User Notes: ". user_getrealname( user_getid() ) ."] ".stripslashes($summary) .
						"\n\n" . util_line_wrap(stripslashes($details)).
						"\n\n______________________________________________________________________".
						"\nYou are receiving this email because you elected to monitor this user.".
						"\nTo stop monitoring this user, login to SourceForge and visit: ".
						"\nhttp://$GLOBALS[sys_default_domain]/developer/monitor.php?user_id=". user_getid();

						exec ("/bin/echo \"". util_prep_string_for_sendmail($body) ."\" | /usr/sbin/sendmail -fnoreply@$GLOBALS[HTTP_HOST] -t -i >& /dev/null &");

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

	echo site_user_header(array('title'=>'My Diary And Notes'));

	echo '
	<H2>Diary And Notes</H2>
	<P>
	<H3>'. $info_str .'</H3>
	<P>
	<table>
	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
	<INPUT TYPE="HIDDEN" NAME="'. $proc_str .'" VALUE="1">
	<INPUT TYPE="HIDDEN" NAME="diary_id" VALUE="'. $_diary_id .'">
	<TR><TD COLSPAN="2"><B>Summary:</B><BR>
		<INPUT TYPE="TEXT" NAME="summary" SIZE="45" MAXLENGTH="60" VALUE="'. $_summary .'">
	</TD></TR>

	<TR><TD COLSPAN="2"><B>Details:</B><BR>
		<TEXTAREA NAME="details" ROWS="15" COLS="60" WRAP="HARD">'. $_details .'</TEXTAREA>
	</TD></TR>
	<TR><TD COLSPAN="2">
		<P>
		<INPUT TYPE="SUBMIT" NAME="submit" VALUE="SUBMIT ONLY ONCE">
		&nbsp; <INPUT TYPE="CHECKBOX" NAME="is_public" VALUE="1" '. (($_is_public)?'CHECKED':'') .'> Is Public
		<P>
		If marked as public, your entry will be mailed to any 
		monitoring users when it is first submitted.
		<P>
		</FORM>
	</TD></TR>

	</TABLE>

	<P>';

	echo $HTML->box1_top('Existing Diary And Note Entries');

	$sql="SELECT * FROM user_diary WHERE user_id='". user_getid() ."' ORDER BY id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<B>You Have No Diary Entries</B>';
		echo db_error();
	} else {
		echo '&nbsp;</TD></TR>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD><A HREF="'. $PHP_SELF .'?diary_id='.
				db_result($result,$i,'id').'">'.db_result($result,$i,'summary').'</A></TD>'.
				'<TD>'. date($sys_datefmt, db_result($result,$i,'date_posted')).'</TD></TR>';
		}
		echo '
		<TR><TD COLSPAN="2" BGCOLOR="'.$HTML->COLOR_CONTENT_BACK.'">&nbsp;</TD></TR>';
	}

	echo $HTML->box1_bottom();

	echo site_user_footer(array());

} else {

	exit_not_logged_in();

}

?>
