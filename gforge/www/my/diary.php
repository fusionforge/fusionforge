<?php
/**
 * GForge User's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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


require_once('pre.php');
require_once('vote_function.php');

if (!session_loggedin()) {

	exit_not_logged_in();

} else {

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
				$feedback .= $Language->getText('my_diary','diary_updated');
			} else {
				echo db_error();
				$feedback .= $Language->getText('my_diary','nothing_updated');
			}
		} else if ($add) {
			//inserting a new diary entry

			$sql="INSERT INTO user_diary (user_id,date_posted,summary,details,is_public) VALUES ".
			"('". user_getid() ."','". time() ."','". htmlspecialchars($summary) ."','". htmlspecialchars($details) ."','$is_public')";
			$res=db_query($sql);
			if ($res && db_affected_rows($res) > 0) {
				$feedback .= $Language->getText('my_diary','item_added');
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
				$feedback .= $Language->getText('my_diary','error_adding_item');
				echo db_error();
			}
		}


	}


	if ($diary_id) {
		$sql="SELECT * FROM user_diary WHERE user_id='". user_getid() ."' AND id='$diary_id'";
		$res=db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			$feedback .= $Language->getText('my_diary','entry_not_found');
			$proc_str='add';
			$info_str=$Language->getText('my_diary','add_new_entry');
		} else {
			$proc_str='update';
			$info_str=$Language->getText('my_diary','update_entry');
			$_summary=db_result($res,0,'summary');
			$_details=db_result($res,0,'details');
			$_is_public=db_result($res,0,'is_public');
			$_diary_id=db_result($res,0,'id');
		}
	} else {
		$proc_str='add';
		$info_str=$Language->getText('my_diary','add_new_entry');
	}

	echo site_user_header(array('title'=>$Language->getText('my_diary','title'),'pagename'=>'my_diary'));

	echo '
	<p>&nbsp;</p>
	<h3>'. $info_str .'</h3>
	<p />
	<form action="'. $PHP_SELF .'" method="post">
	<input type="hidden" name="'. $proc_str .'" value="1" />
	<input type="hidden" name="diary_id" value="'. $_diary_id .'" />
	<table>
	<tr><td colspan="2"><strong>'.$Language->getText('my_diary','summary').':</strong><br />
		<input type="text" name="summary" size="45" maxlength="60" value="'. $_summary .'" />
	</td></tr>

	<tr><td colspan="2"><strong>'.$Language->getText('my_diary','details').':</strong><br />
		<textarea name="details" rows="15" cols="60">'. $_details .'</textarea>
	</td></tr>
	<tr><td colspan="2">
		<p>
		<input type="submit" name="submit" value="'.$Language->getText('my_diary','submit_only_once').'" />
		&nbsp; <input type="checkbox" name="is_public" value="1" '. (($_is_public)?'checked="checked"':'') .' /> '.$Language->getText('my_diary','is_public').'
		</p>
		<p>'.$Language->getText('my_diary','is_public_expl').'
		</p>
	</td></tr>

	</table></form>

	<p />';

	echo $HTML->boxTop($Language->getText('my_diary','existing_entries'));

	$sql="SELECT * FROM user_diary WHERE user_id='". user_getid() ."' ORDER BY id DESC";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<strong>'.$Language->getText('my_diary','no_entries').'</strong>';
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
		<tr><td colspan="2" style="background-color:'.$HTML->COLOR_CONTENT_BACK.'">';
	}

	echo $HTML->boxBottom();

	echo site_user_footer(array());

}

?>
