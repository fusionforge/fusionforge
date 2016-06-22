<?php
/**
 * Generic Tracker facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems; 2005 GForge, LLC
 * Copyright 2012,2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org/
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
require_once 'note.php';
function artifact_submission_form($ath, $group, $summary='', $details='', $assigned_to=100, $priority, $extra_fields=array()) {
	global $HTML;
	/*
		Show the free-form text submitted by the project admin
	*/
	echo notepad_func();
	echo $ath->renderSubmitInstructions();
	echo $HTML->openForm(array('id' => 'trackeraddform', 'action' => '/tracker/?group_id='.$group->getID().'&atid='.$ath->getID(), 'method' => 'post', 'enctype' => 'multipart/form-data'));
	echo html_e('input', array( 'type'=>'hidden', 'name'=>'form_key', 'value'=>form_generate_key()));
	echo html_e('input', array( 'type'=>'hidden', 'name'=>'func', 'value'=>'postadd'));
	echo html_e('input', array( 'type'=>'hidden', 'name'=>'MAX_FILE_SIZE', 'value'=>'10000000'));
	echo $HTML->listTableTop();
	if (!session_loggedin()) {
		$content = html_ao('div', array('class'=>'login_warning_msg'));
		$content .= $HTML->warning_msg(_('Please').' '.util_make_link('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI')), _('login')));
		$content .= _('If you <strong>cannot</strong> login, then enter your email address here')._(':');
		$content .= html_e('p',array(), html_e('input', array('type'=>'text', 'name'=>'user_email', 'size'=>'50', 'maxlength'=>'255')));
		$content .= html_ac(html_ap() - 1);
		$cells = array();
		$cells[][] = $content;
		echo $HTML->multiTableRow(array(), $cells);
	}
	$cells = array();
	$cells[] = array(html_e('strong',array(),_('For project')._(':')).html_e('br').$group->getPublicName(), 'class'=>'top');
	$cells[] = array(html_e('input', array('type'=>'submit', 'name'=>'submit', 'value'=>_('Submit'))), 'class'=>'top');
	echo $HTML->multiTableRow(array(), $cells);

	$ath->renderExtraFields($extra_fields,true,'none',false,'Any',array(),false,'UPDATE');

	if (forge_check_perm ('tracker', $ath->getID(), 'manager')) {
		$content = html_e('strong', array(), _('Assigned to')._(':')).html_e('br');
		$content .= $ath->technicianBox('assigned_to', $assigned_to);
		$content .= '&nbsp;'.util_make_link('/tracker/admin/?group_id='.$group->getID().'&atid='.$ath->getID().'&update_users=1', '('._('Admin').')' );
		$cells = array();
		$cells[][] = $content;

		$content = html_e('strong', array(), _('Priority')._(':')).html_e('br');
		if (empty($priority)) {
			$content .= html_build_priority_select_box('priority');
		} else {
			$content .= html_build_priority_select_box('priority',$priority);
		}
		$cells[][] = $content;

		echo $HTML->multiTableRow(array(), $cells);
	}
	$content = html_e('strong', array(), _('Summary').utils_requiredField()._(':')).html_e('br');
	$content .= html_e('input', array('id'=>'tracker-summary', 'value'=>$summary, 'required'=>'required', 'type'=>'text', 'name'=>'summary', 'size'=>'80', 'maxlength'=>'255', 'title'=>util_html_secure(html_get_tooltip_description('summary'))));
	$cells = array();
	$cells[] = array($content, 'colspan'=>'2');
	echo $HTML->multiTableRow(array(), $cells);

	$content = html_e('strong', array(), _('Detailed description').utils_requiredField()._(':'));
	$content .= notepad_button('document.forms.trackeraddform.details').html_e('br');
	$content .= html_e('textarea', array('id'=>'tracker-description', 'required'=>'required', 'name'=>'details', 'rows'=>'20', 'cols'=>'79', 'title'=>util_html_secure(html_get_tooltip_description('description'))), $details);
	$cells = array();
	$cells[] = array($content, 'colspan'=>'2');
	echo $HTML->multiTableRow(array(), $cells);

	$content = '';
	if (!session_loggedin()) {
		$content .= html_ao('div', array('class'=>'login_warning_msg'));
		$content .= $HTML->warning_msg(_('Please').' '.util_make_link('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI')), _('login')));
		$content .= _('If you <strong>cannot</strong> login, then enter your email address here')._(':');
		$content .= html_e('p',array(), html_e('input', array('type'=>'text', 'name'=>'user_email', 'size'=>'50', 'maxlength'=>'255')));
		$content .= html_ac(html_ap() - 1);
	}
	$content .= html_e('p', array(), '&nbsp;');
	$content .= html_e('span', array('class'=>'important'), _('DO NOT enter passwords or confidential information in your message!'));
	$cells = array();
	$cells[] = array($content, 'colspan'=>'2');
	echo $HTML->multiTableRow(array(), $cells);

	$content = html_ao('div', array('class'=>'file_attachments'));
	$content .= html_ao('p');
	$content .= html_e('strong', array(), _('Attach Files')._(':'));
	$content .= '('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')'.html_e('br');
	$content .= html_e('input', array('type'=>'file', 'name'=>'input_file0')).html_e('br');
	$content .= html_e('input', array('type'=>'file', 'name'=>'input_file1')).html_e('br');
	$content .= html_e('input', array('type'=>'file', 'name'=>'input_file2')).html_e('br');
	$content .= html_e('input', array('type'=>'file', 'name'=>'input_file3')).html_e('br');
	$content .= html_e('input', array('type'=>'file', 'name'=>'input_file4'));
	$content .= html_ac(html_ap() - 2);
	$cells = array();
	$cells[] = array($content, 'colspan'=>'2');
	echo $HTML->multiTableRow(array(), $cells);

	$content = html_e('input', array('type'=>'submit', 'name'=>'submit', 'value'=>_('Submit')));
	$cells = array();
	$cells[] = array($content, 'colspan'=>'2');
	echo $HTML->multiTableRow(array(), $cells);

	echo $HTML->listTableBottom();

	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
}
