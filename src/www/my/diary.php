<?php
/**
 * User's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - TrivialDev
 * Copyright 2014, Stéphane-Eymeric Bredthauer
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';

global $HTML; // Layout object

if (!forge_get_config('use_diary')) {
	exit_disabled('my');
}

if (!session_loggedin()) {
	exit_not_logged_in();
} else {

	$u = session_get_user();
	$diary_id = getIntFromRequest('diary_id');

	if (getStringFromRequest('submit')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('my');
		}

		$summary   = getHtmlStringFromRequest('summary');
		$details   = getHtmlTextFromRequest('details');
		$is_public = getIntFromRequest('is_public', 0);

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
		} elseif (getStringFromRequest('add')) {
			//inserting a new diary entry
			$res = db_query_params ('INSERT INTO user_diary (user_id,date_posted,summary,details,is_public) VALUES
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
					$result = db_query_params ('SELECT users.email from user_diary_monitor,users
										WHERE user_diary_monitor.user_id=users.user_id
										AND user_diary_monitor.monitored_user=$1',
										array(user_getid() ));
					$rows = db_numrows($result);

					if ($result) {
						if ($rows > 0) {
							$tolist=implode(util_result_column_to_array($result),', ');

							$to = ''; // send to noreply@
							$subject = sprintf (_("[%s User Notes: %s] %s"),
									    forge_get_config ('forge_name'),
									    $u->getRealName(),
									    $summary) ;
							$sanitizer = new TextSanitizer();
							$body = $details;
							if (strstr($body,'<br/>') || strstr($body,'<br />')) {
								$body = preg_replace("/[\n\r]/", '', $body);
							}
							$body = $sanitizer->convertNeededTagsForEmail($body);
							$body = strip_tags($body);
							$body = $sanitizer->convertExtendedCharsForEmail($body);

							$body .= "\n\n";
							$body .= '______________________________________________________________________';
							$body .= "\n";
							$body .= _('You are receiving this email because you elected to monitor this user.');
							$body .= "\n";
							$body .= _('To stop monitoring this user, visit the following link:');
							$body .= "\n";

							$body .= util_make_url('/developer/monitor.php?diary_user='.user_getid());

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
		$res=db_query_params('SELECT * FROM user_diary WHERE user_id=$1 AND id=$2',
					array(user_getid(),
						$diary_id));
		if (!$res || db_numrows($res) < 1) {
			$feedback .= _('Entry not found or does not belong to you');
			$proc_str = 'add';
			$info_str = _('Add A New Entry');
		} else {
			$proc_str = 'update';
			$info_str = _('Update An Entry');
			$_summary = db_result($res,0,'summary');
			$_details = db_result($res,0,'details');
			$_is_public = db_result($res,0,'is_public');
			$_diary_id = db_result($res,0,'id');
		}
	} else {
		$proc_str = 'add';
		$info_str = _('Add A New Entry');
		$_diary_id = '';
	}

	site_user_header(array('title' => _('My Diary and Notes')));

	$params['name'] = 'details';
	$params['body'] = $_details;
	$params['height'] = '350';
	$params['width'] = '100%';
	$params['content'] = html_e('textarea', array('required' => 'required', 'name' => 'details', 'rows' => 10, 'cols' => 60), $_details, false);
	$params['user_id'] = $u->getID();
	plugin_hook_by_reference("text_editor", $params);

	echo html_e('h2', array(), $info_str);

	echo html_ao('form', array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
	echo html_e('input', array('type' => 'hidden', 'name' => $proc_str, 'value' => '1'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'diary_id', 'value' => $_diary_id));
	echo html_ao('table', array('class' => 'fullwidth'));
	echo html_ao('tr').html_ao('td');
	echo html_e('strong', array(),_('Summary')._(':')).'<br />';
	echo html_e('input', array( 'required' => 'required', 'type' => 'text', 'name' => 'summary', 'size' => '60', 'maxlength' => '60', 'value' => $_summary));
	echo html_ac(html_ap()-2);

	echo html_ao('tr').html_ao('td');
	echo html_e('strong', array(),_('Details')._(':')).'<br />';
	echo $params['content'];
	echo html_ac(html_ap()-2);

	echo html_ao('tr').html_ao('td');
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit'))).'&nbsp;';
	if ($_is_public) {
		echo html_e('input', array('type' => 'checkbox', 'name' => 'is_public', 'value' => '1', 'checked' => 'checked'))._('Is Public');
	} else {
		echo html_e('input', array('type' => 'checkbox', 'name' => 'is_public', 'value' => '1'))._('Is Public');
	}
	echo html_ac(html_ap()-1);

	echo html_e('p', array(), _('If marked as public, your entry will be mailed to any monitoring users when it is first submitted.'));
	echo html_ac(html_ap()-4);

	echo html_e('h2', array(), _('Existing Diary and Notes Entries'));
	$result=db_query_params('SELECT * FROM user_diary WHERE user_id=$1 ORDER BY id DESC',
				array(user_getid()));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo $HTML->information(_('You Have No Diary Entries'));
	} else {
		echo $HTML->listTableTop(array(),array(),'fullwidth');
		for ($i=0; $i<$rows; $i++) {
			$date   = relative_date(db_result($result,$i,'date_posted'));
			$public = db_result($result,$i,'is_public') ? _('Public') : _('Private');
			$row_attrs = array('class' => $HTML->boxGetAltRowStyle($i,true));
			$cell_data = array();
			$cell_data [] = array(util_make_link('/my/diary.php?diary_id='.db_result($result,$i,'id'), db_result($result,$i,'summary')));
			$cell_data [] = array($date);
			$cell_data [] = array($public);
			echo $HTML->multiTableRow($row_attrs, $cell_data);
		}
		echo $HTML->listTableBottom();
	}

	site_user_footer();
}
