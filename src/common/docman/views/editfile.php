<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

echo '<div id="editFile" >';
echo '<form id="editdocdata" name="editdocdata" method="post" enctype="multipart/form-data">';
echo '<table>';
echo '	<tr>';
echo '		<td><strong>'. _('Document Title')._(': ').'</strong>'. utils_requiredField() .'<br />';
echo '		<input pattern=".{5,}" title="'.sprintf(_('(at least %s characters)'), 5).'" id="title" type="text" name="title" size="40" maxlength="255"/></td>';
echo '	</tr>';
echo '	<tr>';
echo '		<td><strong>'. _('Description')._(': ').'</strong>'. utils_requiredField() .'<br />';
echo '		<input pattern=".{10,}" title="'.sprintf(_('(at least %s characters)'), 10).'" id="description" type="text" name="description" size="40" maxlength="255"/></td>';
echo '	</tr>';
if ($g->useDocmanSearch()) {
	echo '	<tr>';
	echo '		<td>'. _('Both fields are used by the document search engine.') .'</td>';
	echo '	</tr>';
}
echo '	<tr>';
echo '		<td><strong>'. _('File')._(': ').'</strong>';
echo '			<a id="filelink"></a>';
echo '		</td>';
echo '	</tr>';
if ($g->useCreateOnline()) {
	echo '	<tr id="editonlineroweditfile" >';
	echo '		<td>'. _('Edit the contents to your desire or leave them as they are to remain unmodified.') .'<br />';
	echo '			<textarea id="defaulteditzone" name="details" rows="15" cols="70"></textarea><br />';
	echo '			<input id="defaulteditfiletype" type="hidden" name="filetype" value="text/plain" />';
	echo '			<input id="editor" type="hidden" name="editor" value="online" />';
	echo '		</td>';
	echo '	</tr>';
}
echo '	<tr>';
echo '		<td><strong>'. _('Folder that document belongs to:') .'</strong><br />';
echo '			<select name="doc_group" id="doc_group"></select>';
echo '		</td>';
echo '	</tr>';
echo '	<tr>';
echo '		<td><strong>'. _('State')._(': ').'</strong><br />';
echo '			<select name="stateid" id="stateid"></select>';
echo '		</td>';
echo '	</tr>';
echo '	<tr id="fileurlroweditfile">';
echo '		<td><strong>'. _('Specify an new outside URL where the file will be referenced:') .'</strong>'. utils_requiredField() .'<br />';
echo '			<input id="fileurl" type="url" name="file_url" size="50" pattern="ftp://.+|https?://.+" />';
echo '		</td>';
echo '	</tr>';
echo '	<tr id="uploadnewroweditfile">';
echo '		<td><strong>'. _('OPTIONAL: Upload new file:') .'</strong><br />';
echo '			<input type="file" name="uploaded_data" /><br />'.sprintf(_('(max upload size: %s)'),human_readable_bytes(util_get_maxuploadfilesize()));
echo '		</td>';
echo '	</tr>';
echo '</table>';
echo '<input type="hidden" id="docid" name="docid" />';
echo '</form>';
echo '</div>';
