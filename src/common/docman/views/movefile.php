<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2014-2016, Franck Villaume - TrivialDev
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
global $g; // Group object
global $group_id; // id of the group
global $dirid; // id of doc_group
global $dgf; // document directory factory of this group
global $dm; // the Document Manager object
global $HTML; // Layout object
global $warning_msg;
global $childgroup_id;

$actionurl = '/docman/?group_id='.$group_id.'&dirid='.$dirid.'&action=movefile';
// plugin projects-hierarchy support
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

echo html_ao('div', array('class' => 'docmanDivIncluded'));
echo $HTML->openForm(array('name' => 'movefile', 'action' => $actionurl, 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'fileid', 'id' => 'movefileinput'));
echo html_e('span', array(), _('Move selected files to')).$dm->showSelectNestedGroups($dgf->getNested(array(1, 5)), 'moveto_dirid', false).
			html_e('input', array('type' => 'submit', 'value' => _('Go'),  'name' => 'submit'));
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
