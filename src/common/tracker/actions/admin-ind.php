<?php
/**
 * FusionForge Tracker Listing
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, FusionForge Team
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2016, Franck Villaume - TrivialDev
 * Copyright 2016-2017, Stéphane-Eymeric Bredthauer - TrivialDev
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

global $group;
global $HTML;

if (getStringFromRequest('post_changes')) {
	$name = getStringFromRequest('name');
	$description = getStringFromRequest('description');
	$email_all = getStringFromRequest('email_all');
	$email_address = getStringFromRequest('email_address');
	$due_period = getStringFromRequest('due_period');
	$use_resolution = getStringFromRequest('use_resolution');
	$submit_instructions = getStringFromRequest('submit_instructions');
	$browse_instructions = getStringFromRequest('browse_instructions');

	if (!forge_check_perm ('tracker_admin', $group->getID())) {
		exit_permission_denied('','tracker');
	}

	if (getStringFromRequest('add_at')) {
		$res=new ArtifactTypeHtml($group);
		if (!$res->create($name,$description,$email_all,$email_address,
				$due_period,$use_resolution,$submit_instructions,$browse_instructions)) {
			exit_error($res->getErrorMessage(),'tracker');
		} else {
			$feedback .= _('Tracker created successfully');
			$feedback .= html_e('br');
			$feedback .= _("Please configure also the roles (by default, it's “No Access”)");
		}
		$group->normalizeAllRoles () ;
	}
}

//
//	Display existing artifact types
//
$atf = new ArtifactTypeFactoryHtml($group);
if (!$atf || !is_object($atf) || $atf->isError()) {
	exit_error(_('Could Not Get ArtifactTypeFactory'),'tracker');
}

// Only keep the Artifacts where the user has admin rights.
$arr = $atf->getArtifactTypes();
$i=0;
for ($j = 0; $j < count($arr); $j++) {
	if (forge_check_perm ('tracker', $arr[$j]->getID(), 'manager')) {
		$at_arr[$i++] =& $arr[$j];
	}
}
// If no more tracker now,
if ($i==0 && $j>0) {
	exit_permission_denied('','tracker');
}

//required params for site_project_header();
$params['group']=$group_id;
$params['toptab']='tracker';
if(isset($page_title)){
	$params['title'] = $page_title;
} else {
	$params['title'] = '';
}

$atf->header( array('title' => _('Trackers Administration')));

if (!isset($at_arr) || !$at_arr || empty($at_arr)) {
	echo $HTML->warning_msg(_('No trackers found'));
} else {

	echo html_e('p', array(), _('Choose a data type and you can set up prefs, categories, groups, users, and permissions').'.');

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/
	$tablearr = array(_('Tracker'),_('Description'));
	echo $HTML->listTableTop($tablearr);

	for ($j = 0; $j < count($at_arr); $j++) {
		$cells = array();
		$cells[][] = util_make_link('/tracker/admin/?atid='.$at_arr[$j]->getID().'&group_id='.$group_id, $HTML->getFollowPic().'&nbsp;'.$at_arr[$j]->getName());
		$cells[][] = $at_arr[$j]->getDescription();
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();

	$roadmap_factory = new RoadmapFactory($group);
	$roadmaps = $roadmap_factory->getRoadmaps(true);
	if (!empty($roadmaps)) {
		echo html_e('p', array('id' => 'roadmapadminlink'), util_make_link('/tracker/admin/?group_id='.$group_id.'&admin_roadmap=1', _('Manage your roadmaps.')));
	}
}

//
//	Set up blank ArtifactType
//

if (forge_check_perm ('tracker_admin', $group->getID())) {
	echo html_e('h3', array(), _('Create a new tracker.'));
	echo html_e('p', array(), _('You can use this system to track virtually any kind of data, with each tracker having separate user, group, category, and permission lists. You can also easily move items between trackers when needed.'));
	echo html_e('p', array(), _('Trackers are referred to as “Artifact Types” and individual pieces of data are “Artifacts”. “Bugs” might be an Artifact Type, whiles a bug report would be an Artifact. You can create as many Artifact Types as you want, but remember you need to set up categories, groups, and permission for each type, which can get time-consuming.'));

	echo $HTML->openForm(array('method' => 'post', 'action' => '/tracker/admin/?group_id='.$group_id));

	echo html_e('input', array('type'=>'hidden', 'name'=>'add_at', 'value'=>'y'));

	echo html_ao('p');
	echo html_e('label', array('for'=>'name'), html_e('strong',array(), _('Name')._(':')).' '._('(examples: meeting minutes, test results, RFP Docs)').utils_requiredField()).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'name', 'value'=>'', 'required'=>'required'));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'description'), html_e('strong',array(), _('Description')._(':').utils_requiredField())).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'description', 'value'=>'', 'size'=>'50', 'required'=>'required'));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'email_address'), html_e('strong',array(), _('Send email on new submission to address')._(':'))).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'email_address', 'value'=>''));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('input', array('type'=>'checkbox', 'name'=>'email_all', 'value'=>'1'));
	echo html_e('label', array('for'=>'email_all'), html_e('strong',array(), _('Send email on all changes')));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'due_period'), html_e('strong',array(),  _('Days till considered overdue')._(':'))).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'due_period', 'value'=>'30'));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'status_timeout'), html_e('strong',array(), _('Days till pending tracker items time out')._(':'))).html_e('br');
	echo html_e('input', array('type'=>'text', 'name'=>'status_timeout', 'value'=>'14'));
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'submit_instructions'), html_e('strong',array(), _('Free form text for the “Submit New” page')._(':'))).html_e('br');
	echo html_e('textarea', array('name'=>'submit_instructions', 'rows'=>'10', 'cols'=>'55'), '', false);
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('label', array('for'=>'browse_instructions'), html_e('strong',array(), _('Free form text for the Browse page')._(':'))).html_e('br');
	echo html_e('textarea', array('name'=>'browse_instructions', 'rows'=>'10', 'cols'=>'55'), '', false);
	echo html_ac(html_ap()-1);

	echo html_ao('p');
	echo html_e('input', array('type'=>'submit', 'name'=>'post_changes', 'value'=>_('Submit')));
	echo html_ac(html_ap()-1);

	echo $HTML->closeForm();
}

$atf->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
