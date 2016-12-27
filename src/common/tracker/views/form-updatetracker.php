<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org
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

global $HTML;

$name = getStringFromRequest('name', $ath->getName());
$description = getStringFromRequest('description', $ath->getDescription());
$email_address = getStringFromRequest('email_address', $ath->getEmailAddress());
$email_all = getStringFromRequest('email_all', $ath->emailAll());
$due_period = getStringFromRequest('due_period', $ath->getDuePeriod() / 86400);
$status_timeout = getStringFromRequest('status_timeout', $ath->getStatusTimeout() / 86400);
$submit_instructions = getStringFromRequest('submit_instructions', $ath->getSubmitInstructions());
$browse_instructions = getStringFromRequest('browse_instructions', $ath->getBrowseInstructions());

//
//	FORM TO UPDATE ARTIFACT TYPES
//
$ath->adminHeader(array('title'=>sprintf(_('Update settings for %s'),
	$ath->getName()),
	'modal'=>1));

echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));

echo html_e('input', array('type'=>'hidden', 'name'=>'update_type', 'value'=>'y'));

echo html_ao('p');
if ($ath->getDataType()) {
	echo html_e('strong',array(), _('Name')._(':')).' '._('(examples: meeting minutes, test results, RFP Docs)').html_e('br');
	echo $ath->getName();
} else  {
	echo html_e('label', array('for'=>'name'), html_e('strong',array(), _('Name')._(':')).' '._('(examples: meeting minutes, test results, RFP Docs)').utils_requiredField()).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'name', 'value'=>$ath->getName(), 'required'=>'required'));
}
echo html_ac(html_ap()-1);

echo html_ao('p');
if ($ath->getDataType()) {
	echo html_e('strong',array(), _('Description')._(':')).html_e('br');
	echo $ath->getDescription();
} else  {
	echo html_e('label', array('for'=>'description'), html_e('strong',array(),_('Description')._(':')).utils_requiredField()).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'description', 'value'=>$ath->getDescription(), 'size'=>'50', 'required'=>'required'));
}
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('label', array('for'=>'email_address'), html_e('strong',array(), _('Send email on new submission to address')._(':'))).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'email_address', 'value'=> $email_address));
echo html_ac(html_ap()-1);

echo html_ao('p');
if ($email_all) {
	echo html_e('input', array('type'=>'checkbox', 'name'=>'email_all', 'value'=>'1', 'checked'=>'checked'));
} else {
	echo html_e('input', array('type'=>'checkbox', 'name'=>'email_all', 'value'=>'1'));
}
echo html_e('label', array('for'=>'email_all'), html_e('strong',array(), _('Send email on all changes')));
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('label', array('for'=>'due_period'), html_e('strong',array(),  _('Days till considered overdue')._(':'))).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'due_period', 'value'=>$due_period));
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('label', array('for'=>'status_timeout'), html_e('strong',array(), _('Days till pending tracker items time out')._(':'))).html_e('br');
echo html_e('input', array('type'=>'text', 'name'=>'status_timeout', 'value'=>$status_timeout));
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('label', array('for'=>'submit_instructions'), html_e('strong',array(), _('Free form text for the “Submit New” page')._(':'))).html_e('br');
echo html_e('textarea', array('name'=>'submit_instructions', 'rows'=>'10', 'cols'=>'55'), $submit_instructions, false);
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('label', array('for'=>'browse_instructions'), html_e('strong',array(), _('Free form text for the Browse page')._(':'))).html_e('br');
echo html_e('textarea', array('name'=>'browse_instructions', 'rows'=>'10', 'cols'=>'55'), $browse_instructions, false);
echo html_ac(html_ap()-1);

echo html_ao('p');
echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Submit')));
echo html_ac(html_ap()-1);

echo $HTML->closeForm();
$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
