<?php
/**
 * FusionForge Documentation Manager
 *
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
global $g; //group object
global $group_id; // id of the group
global $HTML;

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

echo html_ao('div', array('id' => 'editFile'));
echo $HTML->openForm(array('id' => 'editdocdata', 'name' => 'editdocdata', 'method' => 'post', 'enctype' => 'multipart/form-data'));
echo $HTML->listTableTop(array());
$cells = array();
$cells[] = array(_('Document Title').utils_requiredField()._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('pattern' => '.{5,}', 'title' => sprintf(_('(at least %s characters)'), 5), 'id' => 'title', 'type' => 'text', 'name' => 'title', 'size' => '40', 'maxlength' => '255'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Description').utils_requiredField()._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('pattern' => '.{10,}', 'title' => sprintf(_('(at least %s characters)'), 10), 'id' => 'description', 'type' => 'text', 'name' => 'description', 'size' => '40', 'maxlength' => '255'));
echo $HTML->multiTableRow(array(), $cells);
if ($g->useDocmanSearch()) {
	$cells = array();
	$cells[] =  array(_('Both fields are used by the document search engine.'), 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
}
$cells = array();
$cells[] = array(_('File')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('a', array('id' => 'filelink'), '', false);
echo $HTML->multiTableRow(array(), $cells);
if ($g->useCreateOnline()) {
	$cells = array();
	$cells[] = array(_('Edit the contents to your desire or leave them as they are to remain unmodified.').html_e('br').
			html_e('textarea', array('id' => 'defaulteditzone', 'name' => 'details', 'rows' => '15', 'cols' => '70'), '', false).
			html_e('input', array('id' => 'defaulteditfiletype', 'type' => 'hidden', 'name' => 'filetype', 'value' => 'text/plain')).
			html_e('input', array('id' => 'editor', 'type' => 'hidden', 'name' => 'editor', 'value' => 'online')),
			'colspan' => 2);
	echo $HTML->multiTableRow(array('id' => 'editonlineroweditfile'), $cells);
}
$cells = array();
$cells[] = array(_('Folder that document belongs to')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('select', array('name' => 'doc_group', 'id' => 'doc_group'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('State')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('select', array('name' => 'stateid', 'id' => 'stateid'), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Specify an new outside URL where the file will be referenced').utils_requiredField()._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('id' => 'fileurl', 'type' => 'url', 'name' => 'file_url', 'size' => '50', 'pattern' => 'ftp://.+|https?://.+'));
echo $HTML->multiTableRow(array('id' => 'fileurlroweditfile'), $cells);
$cells = array();
$cells[] = array(_('OPTIONAL Upload new file')._(':'), 'class' => 'docman_editfile_title');
$cells[][] = html_e('input', array('type' => 'file', 'name' => 'uploaded_data')).html_e('br').sprintf(_('(max upload size: %s)'),human_readable_bytes(util_get_maxuploadfilesize()));
echo $HTML->multiTableRow(array('id' => 'uploadnewroweditfile'), $cells);
echo $HTML->listTableBottom();
echo html_e('input', array('type' => 'hidden', 'id' => 'docid', 'name' => 'docid'));
echo $HTML->closeForm();
echo html_ac(html_ap() -1);
