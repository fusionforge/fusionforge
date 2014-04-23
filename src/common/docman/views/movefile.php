<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $group_id; // id of the group
global $dirid; // id of doc_group
global $dgf; // document directory factory of this group
global $dm; // the Document Manager object
global $HTML;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

// plugin projects-hierarchy
$actionurl = '/docman/?group_id='.$group_id.'&dirid='.$dirid.'&action=movefile';
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&childgroup_id='.$childgroup_id;
}

echo html_ao('div', array('class' => 'docmanDivIncluded'));
echo $HTML->openForm(array('name' => 'movefile', 'action' => util_make_uri($actionurl), 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'fileid', 'id' => 'movefileinput'));
echo html_ao('p', array());
echo _('Move files to');
echo $dm->showSelectNestedGroups($dgf->getNested(), 'moveto_dirid', false);
echo html_e('input', array('type' => 'submit', 'value' => _('Go'),  'name' => 'submit'));
echo html_ac(html_ap() - 1);
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
