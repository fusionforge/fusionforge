<?php
/**
 * User's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';

if (!forge_get_config('use_diary')) {
	exit_disabled('my');
}

if (!session_loggedin()) {

	exit_not_logged_in();

} else {

	$u =& session_get_user();
	$diary_id = getIntFromRequest('diary_id');

	if (getStringFromRequest('submit')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('my');
		}

		$summary = getStringFromRequest('summary');
		$details = getStringFromRequest('details');
		$is_public = getIntFromRequest('is_public', 0);

		// Secure code sent by user.
		$summary = htmlspecialchars($summary);
		if (getStringFromRequest('_details_content_type') == 'html') {
			$details = TextSanitizer::purify($details);
		} else {
			$details = htmlspecialchars($details);
		}

		//make changes to the database
		if (getStringFromRequest('update')) {
			//updating an existing diary entry
			$res=db_query_params ('UPDATE user_diary SET summary=$1,details=$2,is_public=$3 
WHERE user_id=$4 AND id=$5',
			array($summary,
				$details,
				$is_public,
				user_getid() ,
				$diary_id));
			if ($res && db_affected_rows($res) > 0) {
				$feedback .= _('Diary Updated');
			} else {
				form_release_key(getStringFromRequest("form_key"));
				echo db_error();
				$feedback .= _('Nothing Updated');
			}
		} else if (getStringFromRequest('add')) {
			//inserting a new diary entry


			$res=db_query_params ('INSERT INTO user_diary (user_id,date_posted,summary,details,is_public) VALUES 
($1,$2,$3,$4,$5)',
			array(user_getid() ,
				time() ,
				$summary,
				$details,
				$is_public));
			if ($res && db_affected_rows($res) > 0) {
				$feedback .= _('Item Added');
				if ($is_public) {

					//send an email if users are monitoring
					$result=db_query_params ('SELECT users.email from user_diary_monitor,users 
WHERE user_diary_monitor.user_id=users.user_id 
AND user_diary_monitor.monitored_user=$1',
			array(user_getid() ));
					$rows=db_numrows($result);

					if ($result) {
						if ($rows > 0) {
							$tolist=implode(util_result_column_to_array($result),', ');
							
							$to = ''; // send to noreply@
							$subject = sprintf (_("[%s User Notes: %s] %s"),
									    forge_get_config ('forge_name'),
									    $u->getRealName(),
									    $summary) ;
							$body = util_line_wrap($details) ;
							$body .= _("

______________________________________________________________________
You are receiving this email because you elected to monitor this user.
To stop monitoring this user, login to %s and visit the following link:

%s
",
								   forge_get_config ('forge_name'),
								   util_make_url ("/developer/monitor.php?diary_user=". user_getid())) ;
							
							util_send_message($to, $subject, $body, $to, $tolist);
							
							$feedback .= " ".sprintf(ngettext("email sent to %s monitoring user",
											  "email sent to %s monitoring users",
											  $rows),
										 $rows);
						} else {
							$feedback .= " "._("email not sent - no one monitoring") ;
						}
					} else {
						echo db_error();
					}

				} else {
					//don't send an email to monitoring users
					//since this is a private note
				}
			} else {
				form_release_key(getStringFromRequest("form_key"));
				$error_msg .= _('Error Adding Item: '). db_error();
			}
		}

	}

	$_summary = '';
	$_details = '';
	$_is_public = '';

	if ($diary_id) {

		$res=db_query_params ('SELECT * FROM user_diary WHERE user_id=$1 AND id=$2',
			array(user_getid() ,
				$diary_id));
		if (!$res || db_numrows($res) < 1) {
			$feedback .= _('Entry not found or does not belong to you');
			$proc_str='add';
			$info_str=_('Add A New Entry');
		} else {
			$proc_str='update';
			$info_str=_('Update An Entry');
			$_summary=db_result($res,0,'summary');
			$_details=db_result($res,0,'details');
			$_is_public=db_result($res,0,'is_public');
			$_diary_id=db_result($res,0,'id');
		}
	} else {
		$proc_str='add';
		$info_str=_('Add A New Entry');
		$_diary_id = '';
	}

    site_user_header(array('title'=>_('My Diary And Notes')));
	echo '<h1>' . _('My Diary And Notes') . '</h1>';

	echo '
	<h2>'. $info_str .'</h2>

	<form action="'. getStringFromServer('PHP_SELF') .'" method="post">
	<input type="hidden" name="form_key" value="'.form_generate_key().'"/>
	<input type="hidden" name="'. $proc_str .'" value="1" />
	<input type="hidden" name="diary_id" value="'. $_diary_id .'" />
	<table width="100%">
	<tr><td colspan="2"><strong>'._('Summary').':</strong><br />
		<input type="text" name="summary" size="60" maxlength="60" value="'. $_summary .'" />
	</td></tr>

	<tr><td colspan="2"><strong>'._('Details').':</strong><br />
		<textarea name="details" rows="15" cols="60">'. $_details .'</textarea>
	</td></tr>
	<tr><td colspan="2">
		<p>
		<input type="submit" name="submit" value="'._('SUBMIT ONLY ONCE').'" />
		&nbsp; <input type="checkbox" name="is_public" value="1" '. (($_is_public)?'checked="checked"':'') .' /> '._('Is Public').'
		</p>
		<p>'._('If marked as public, your entry will be mailed to any monitoring users when it is first submitted.').'
		</p>
	</td></tr>

	</table></form>

	<p />';

	echo $HTML->boxTop(_('Existing Diary And Note Entries'));

	$result=db_query_params ('SELECT * FROM user_diary WHERE user_id=$1 ORDER BY id DESC',
			array(user_getid() ));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<strong>'._('You Have No Diary Entries').'</strong>';
		echo db_error();
	} else {
		echo '&nbsp;</td></tr>';
		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="'. getStringFromServer('PHP_SELF') .'?diary_id='.
				db_result($result,$i,'id').'">'.db_result($result,$i,'summary').'</a></td>'.
				'<td>'. date(_('Y-m-d H:i'), db_result($result,$i,'date_posted')).'</td></tr>';
		}
		echo '
		<tr><td colspan="2" class="tablecontent">';
	}

	echo $HTML->boxBottom();

	site_user_footer(array());

}

?>
