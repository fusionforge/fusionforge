<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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

if (!forge_check_perm('docman', $group_id, 'submit')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerAddItem;

jQuery(document).ready(function() {
	controllerAddItem = new DocManAddItemController({
		injectZip:	jQuery('#injectzip'),
		submitZip:	jQuery('#submitinjectzip')
	});
});

jQuery(document).ready(function() {
	jQuery("#tabs").tabs();
});
//]]>
<?php
echo html_ac(html_ap() - 1);
echo html_ao('div', array('id' => 'tabs'));
echo html_ao('ul');
echo html_e('li', array(), util_make_link('#tabs-new-document', _('New Document'), array('id' => 'tab-new-document', 'class' => 'tabtitle', 'title' => _('Submit a new document in this folder.')), true), false);

if (forge_check_perm('docman', $group_id, 'approve')) {
	echo html_e('li', array(), util_make_link('#tabs-new-folder', _('New Folder'), array('id' => 'tab-new-folder', 'class' => 'tabtitle', 'title' => _('Create a folder based on this name.')), true), false);
	echo html_e('li', array(), util_make_link('#tabs-inject-tree', _('Inject Tree'), array('id' => 'tab-inject-tree', 'class' => 'tabtitle', 'title' => _('Create a full folders tree using an compressed archive. Only ZIP format support.')), true), false);
}

echo html_ac(html_ap() -1);
echo html_ao('div', array('id' => 'tabs-new-document'));
echo html_ao('div', array('class' => 'docman_div_include', 'id' => 'addfile'));
include ($gfcommon.'docman/views/addfile.php');
echo html_ac(html_ap() -2);

if (forge_check_perm('docman', $group_id, 'approve')) {
	echo html_ao('div', array('id' => 'tabs-new-folder'));
	echo html_ao('div', array('class' => 'docman_div_include', 'id' => 'addsubdocgroup'));
	include ($gfcommon.'docman/views/addsubdocgroup.php');
	echo html_ac(html_ap() -2);
	echo html_ao('div', array('id' => 'tabs-inject-tree'));
	echo html_ao('div', array('class' => 'docman_div_include', 'id' => 'zipinject'));
	echo $HTML->openForm(array('id' => 'injectzip', 'name' => 'injectzip', 'method' => 'post', 'action' => util_make_uri('/docman/?group_id='.$group_id.'&action=injectzip&dirid='.$dirid), 'enctype' => 'multipart/form-data'));
	echo html_ao('p');
	echo html_e('label', array(), _('Upload archive:'), false);
	echo html_e('input', array('type' => 'file', 'name' => 'uploaded_zip', 'required' => 'required'));
	echo html_e('span', array(), sprintf(_('(max upload size: %s)'),human_readable_bytes(util_get_maxuploadfilesize())), false);
	echo html_e('input', array('id' => 'submitinjectzip', 'type' => 'button', 'value' => _('Inject Tree')));
	echo html_ac(html_ap() -1);
	echo $HTML->closeForm();
	echo html_ac(html_ap() -2);
}

echo html_ac(html_ap() -1);
