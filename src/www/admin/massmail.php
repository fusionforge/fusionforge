<?php
/**
 * Site Admin page for setting up massmailings.
 *
 * This is frontend of SF massmail facility, which allows to prepare
 * messages for delivery to target categories of site users. very
 * delivery is performed via cronjob.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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
require_once $gfwww.'admin/admin_utils.php';

global $HTML;
global $error_msg, $feedback;

session_require_global_perm ('forge_admin');

if (getStringFromRequest('action')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit('admin');
	}
	switch (getStringFromRequest('action')) {
		case 'add': {
			$mail_type = getStringFromRequest('mail_type');
			$mail_message = getStringFromRequest('mail_message');
			$mail_subject = getStringFromRequest('mail_subject');

			if (!$mail_type) {
				form_release_key(getStringFromRequest('form_key'));
				exit_missing_param('',array(_('Target Audience')),'admin');
			}

			if (!trim($mail_message)) {
				form_release_key(getStringFromRequest('form_key'));
				exit_missing_param('',array(_('No Message')),'admin');
			}

			if (trim($mail_subject) == '['.forge_get_config ('forge_name').']') {
				form_release_key(getStringFromRequest('form_key'));
				exit_missing_param('',array(_('No Subject')),'admin');
			}

			$res = db_query_params ('
				INSERT INTO massmail_queue(type,subject,message,queued_date)
				VALUES ($1,$2,$3,$4)
			',
					array($mail_type,
						$mail_subject,
						$mail_message,
						time()));

			if (!$res || db_affected_rows($res)<1) {
				form_release_key(getStringFromRequest('form_key'));
				$error_msg = _('Scheduling Mailing, Could not schedule mailing, database error')._(': ').db_error();
			} else {
				$systasksq = new SysTasksQ();
				$systasksq->add(SYSTASK_CORE, 'MASSMAIL', null, user_getid());
				$feedback = _('Mailing successfully scheduled for delivery');
			}
			break;
		}
		case 'del': {
			$id = getIntFromRequest('id');
			if (!$id) {
				form_release_key(getStringFromRequest('form_key'));
				exit_missing_param('',array(_('Delivery Id')),'admin');
			}
			$res = db_query_params('DELETE FROM massmail_queue WHERE id = $1',
						array($id));
			if (!$res || db_affected_rows($res)<1) {
				form_release_key(getStringFromRequest('form_key'));
				$error_msg = _('Scheduling Mailing, Could not delete mailing, database error')._(': ').db_error();
			} else {
				$feedback = _('Mailing successfully deleted');
			}
			break;
		}
	}
}

$title = sprintf(_('Mail Engine for %s Subscribers'), forge_get_config('forge_name'));
site_admin_header(array('title' => $title));

echo html_e('p', array(), util_make_link('/admin/massmail.php#active', _('Active Deliveries')));
echo html_e('p', array(), _('Be <span class="important">VERY</span> careful with this form, because submitting it WILL lead to sending email to lots of users.'));

echo $HTML->openForm(array('action' => '/admin/massmail.php?action=add', 'method' => 'post'))
	.html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()))
	.html_e('strong', array(), _('Target Audience').utils_requiredField()._(':'))
	.html_e('br')
	.html_build_select_box_from_arrays(
			array(0,'SITE','COMMNTY','DVLPR','ADMIN','ALL','SFDVLPR'),
			array(
				_('(select)'),
				_('Subscribers to “Site Updates”'),
				_('Subscribers to “Additional Community Mailings”'),
				_('All Project Developers'),
				_('All Project Admins'),
				_('All Users'),
				forge_get_config('forge_name'). _('Developers (test)')
			),
			'mail_type',false,false
		)
	.html_e('br');

echo html_e('p', array(),
		html_e('strong', array(), _('Subject').utils_requiredField()._(':'))
		.html_e('br')
		.html_e('input', array('type' => 'text', 'required' => 'required', 'name' => 'mail_subject', 'size' => 50, 'value' => '['.forge_get_config('forge_name').']')));
echo html_e('p', array(),
		html_e('strong', array(), _('Text of Message').utils_requiredField()._(': '))._('(will be appended with unsubscription information, if applicable)'));

echo html_e('textarea', array('required' => 'required', 'name' => 'mail_message', 'cols' => 70, 'rows' => 20), '' , false);

echo html_e('p', array(), html_e('input', array('type' => 'submit', 'value' => _('Schedule for Mailing'))));
echo $HTML->closeForm();

$res = db_query_params('
	SELECT *
	FROM massmail_queue
	WHERE finished_date=0
',
			array()) ;

$title=array();
$title[]='&nbsp;';
$title[]=_('Id');
$title[]=_('Type');
$title[]=_('Subject');
$title[]=_('Date');
$title[]=_('Last user_id mailed');

echo html_e('h2', array('id' => 'active'), _('Active Deliveries')._(':'));

$seen = false;

$i = 0;
while ($row = db_fetch_array($res)) {
	if (!$seen) {
		echo $HTML->listTableTop($title);
		$seen = true;
	}
	$cells = array();
	$cells[][] = util_make_link('/admin/massmail.php?id='.$row['id'].'&action=del', _('Delete'));
	$cells[][] = $row['id'];
	$cells[][] = $row['type'];
	$cells[][] = $row['subject'];
	$cells[][] = date(_('Y-m-d H:i'), $row['queued_date']);
	$cells[][] = $row['last_userid'];
	echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
}

if ($seen) {
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('No deliveries active.'));
}

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
