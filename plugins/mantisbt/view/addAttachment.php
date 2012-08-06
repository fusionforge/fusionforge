<?php
/**
 * MantisBT plugin
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

global $group_id;
global $mantisbt;
global $type;
global $idBug;

echo '<form method="POST" Action="?type='.$type.'&group_id='.$group_id.'&pluginname='.$mantisbt->name.'&idBug='.$idBug.'&action=addAttachment&view=viewIssue" enctype="multipart/form-data">';
echo	'<table>';
echo		'<tr><td>';
echo		_('File:'). ' '.'<input type="file" name="attachment" />';
echo	'</td></tr></table>';
echo	'<br/><input type="button" onclick="this.form.submit();this.disabled=true;" value="'._('Upload File').'" name="send">';
echo '</form>';

?>
