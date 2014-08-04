<?php
/**
 * FusionForge File Release Facility
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $HTML; // html object
global $group_id; // id of group
global $g; // group object
global $warning_msg; // warning message

$package_id = getIntFromRequest('package_id');
$frspf = new FRSPackageFactory($g);
$packages = $frspf->getFRSs();
if (!count($packages)) {
	$warning_msg = _('No package found. You need to create one before creating releases.');
	session_redirect('/frs/?view=admin&group_id='.$group_id);
}

echo html_e('h2', array(), _('Quick Release System'));
echo $HTML->openForm(array('enctype' => 'multipart/form-data', 'method' => 'post', 'action' => util_make_uri('/frs/?group_id='.$group_id.'&action=addrelease')));
echo $HTML->listTableTop();
$cells = array();
$cells[][] = html_e('strong', array(), _('Package ID')._(':'));
$packageIds = array();
$packageNames = array();
foreach ($packages as $onepackage) {
	$packageIds[] = $onepackage->getID();
	$packageNames[] = html_entity_decode($onepackage->getName());
}
$cells[][] = html_build_select_box_from_arrays($packageIds, $packageNames, 'package_id', $package_id, false).'&nbsp;&nbsp;'.sprintf(_('Or %1$s create a new package %2$s'), '<a href="'.util_make_url ('/frs/?view=admin&group_id='.$group_id).'">', '</a>');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('Release Name').utils_requiredField()._(':'));
$cells[][] = html_e('input', array('type' => 'text', 'required' => 'required', 'name' => 'release_name', 'pattern' => '.{3,}', 'title' => _('At least 3 characters')));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('Release Date')._(':'));
$cells[][] = html_e('input', array('type' => 'text', 'name' => 'release_date', 'value' => date('Y-m-d H:i'), 'size' => 16, 'maxlength' => 16));
$cells = array();
$cells[][] = html_e('strong', array(), _('File Name')._(':'));
$content = $HTML->information(sprintf(_('You can probably not upload files larger than about %.2f MiB in size.'), human_readable_bytes(util_get_maxuploadfilesize()))).
		_('Upload a new file')._(': ').html_e('input', array('type' => 'file', 'name' => 'userfile', 'size' => 30));
if (forge_get_config('use_ftp_uploads')) {
	include ($gfcommon.'frs/views/useftpuploads.php');
}
if (forge_get_config('use_manual_uploads')) {
	include ($gfcommon.'frs/views/usemanualuploads.php');
}
if ($g->usesDocman()) {
	include ($gfcommon.'frs/views/docmanfile.php');
}
$cells[][] = $content;
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('File Type')._(':'));
$cells[][] = frs_show_filetype_popup('type_id');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('Processor Type')._(':'));
$cells[][] = frs_show_processor_popup('processor_id');
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('Release Notes')._(':'));
$cells[][] = html_e('textarea', array('name' => 'release_notes', 'rows' => 7, 'cols' => 50), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = html_e('strong', array(), _('Change Log')._(':'));
$cells[][] = html_e('textarea', array('name' => 'release_changes', 'rows' => 7, 'cols' => 50), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<input type="checkbox" name="preformatted" value="1" />'._('Preserve my pre-formatted text').'<p><input type="submit" name="submit" value="'._('Create release').'" /></p>', 'colspan' => 2, 'style' => 'text-align:center');
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
