<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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
global $group_id; // id of the group
global $dirid; // id of the doc_group

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

// plugin projects-hierarchy
$actionurl = '/docman/?group_id='.$group_id.'&action=addsubdocgroup&dirid='.$dirid;
if (isset($childgroup_id) && $childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&childgroup_id='.$childgroup_id;
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
function doItAddSubGroup() {
	document.getElementById('addsubgroup').submit();
	document.getElementById('submitaddsubgroup').disabled = true;
}
//]]>
<?php
echo html_ac(html_ap() - 1);
echo html_ao('div', array('class' => 'docmanDivIncluded'));
echo $HTML->openForm(array('id' => 'addsubgroup', 'name' => 'addsubgroup', 'method' => 'post', 'action' => util_make_uri($actionurl)));
if ($dirid) {
	$folderMessage = _('Name of the document subfolder to create');
} else {
	$folderMessage = _('Name of the document folder to create');
}
echo html_e('span', array(), $folderMessage._(': '), false);
echo html_e('input', array('required' => 'required', 'type' => 'text',  'name' => 'groupname', 'size' => 40, 'maxlength' => 255, 'placeholder' => $folderMessage));
echo html_e('input', array('id' => 'submitaddsubgroup', 'type' => 'button', 'value' => _('Create'), 'onclick' => 'javascript:doItAddSubGroup()'));
echo $HTML->closeForm();
echo html_ac(html_ap() -1);
