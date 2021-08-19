<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2016,2021, Franck Villaume - TrivialDev
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
global $HTML;
global $warning_msg;
global $childgroup_id;

$actionurl = DOCMAN_BASEURL.$group_id.'&action=editdocgroup';

// plugin projects-hierarchy support
if ($childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&childgroup_id='.$childgroup_id;
}

if (!forge_check_perm('docman', $g->getID(), 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect(DOCMAN_BASEURL.$group_id);
}

$dg = new DocumentGroup($g, $dirid);
if ($dg->isError()) {
	$error_msg = $dg->getErrorMessage();
	session_redirect(DOCMAN_BASEURL.$group_id);
}

echo html_ao('div', array('class' => 'docmanDivIncluded'));
echo $HTML->openForm(array('name' => 'editgroup', 'action' => $actionurl, 'method' => 'post'));
echo html_e('input', array('type' => 'hidden', 'name' => 'dirid', 'value' => $dirid));
echo $HTML->listTableTop();
$cells[][] = _('Folder Name');
$cells[][] = '<input required="required" type="text" name="groupname" value="'.$dg->getName().'" />';
$cells[][] = '&nbsp;';
$cells[][] = _('Status');
$cells[][] = doc_get_state_box($dg->getState(), array(2, 3, 4)); /** no direct deleted, pending, hidden status */
$cells[][] = '&nbsp;';
$cells[][] = _('belongs to');
if ($dg->getState() == 2) {
	$newdgf = new DocumentGroupFactory($g);
	$cells[][] = $dm->showSelectNestedGroups($newdgf->getNested(array(1, 5)), 'parent_dirid', true, false);
	$labelSubmit = _('Restore');
} else {
	$cells[][] = $dm->showSelectNestedGroups($dgf->getNested(array(1, 5)), 'parent_dirid', true, $dg->getParentID(), array($dg->getID()));
	$labelSubmit = _('Edit');
}
$cells[][] = '<input type="submit" value="'.$labelSubmit.'" name="submit" />';
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo html_e('p', array(), _('Folder name will be used as a title, so it should be formatted correspondingly.'), false);
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
