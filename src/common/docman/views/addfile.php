<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* display the add new documentation form */

require_once ('docman/DocumentGroupFactory.class.php');
require_once ('docman/include/DocumentGroupHTML.class.php');

$group_id = getIntFromRequest('group_id');
$dirid = getIntFromRequest('dirid');
//session_require_perm ('docman', $group_id, 'submit') ;

echo '<p>'. _('<strong>Document Title</strong>:  Refers to the relatively brief title of the document (e.g. How to use the download server)<br /><strong>Description:</strong> A brief description to be placed just under the title<br />') .'</p>';

if ($g->useDocmanSearch()) 
	echo '<p>'. _('Both fields are used by document search engine.'). '</p>';

echo '<form name="adddata" action="?group_id='.$group_id.'&action=addfile" method="post" enctype="multipart/form-data">
		<table border="0" width="75%">
			<tr>
				<td>
					<strong>'. _('Document Title').' :</strong>'. utils_requiredField(). sprintf(_('(at least %1$s characters)'), 5).'<br />
					<input type="text" name="title" size="40" maxlength="255" />
				</td>
			</tr>
			<tr>
				<td>
					<strong>'. _('Description') .' :</strong>'. utils_requiredField() . sprintf(_('(at least %1$s characters)'), 10).'<br />
					<input type="text" name="description" size="50" maxlength="255" />
				</td>
			</tr>';
echo '
			<tr>
				<td>
					<strong>'. _('Upload File') .' :</strong>'. utils_requiredField() .'<br />
					<input type="file" name="uploaded_data" size="30" /><br /><br />
					<input type="hidden" name="type" value="httpupload">
				</td>
			</tr>';
if ($dirid) {
	echo '<input type="hidden" name="doc_group" value="'.$dirid.'">';
} else {
	echo '
			<tr>
				<td>
					<strong>'. _('Group that document belongs in').' :</strong><br />';
	$dgf = new DocumentGroupFactory($g);

	if ($dgf->isError())
		exit_error('Error',$dgf->getErrorMessage());

	$dgh = new DocumentGroupHTML($g);

	if ($dgh->isError())
		exit_error('Error',$dgh->getErrorMessage());

	$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $dirid);
	echo '
				</td>
			</tr>';
}
echo '
		</table>
		<input type="submit" name="submit" value="'. _('Submit Information').' " />
	</form>';
?>
