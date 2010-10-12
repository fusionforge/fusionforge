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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; // group object
global $group_id; // id of the group
global $dirid; //id of the doc_group
global $dgh; // document group html object

if ( $dgf->getNested() == NULL ) {
	echo '<div class="docmanDivIncluded">';
	echo '<div class="warning">'. _('You MUST first create at least one directory to store your document.') .'</p></div>';
} else {
	/* display the add new documentation form */
	echo '<div class="docmanDivIncluded">';
	echo '<p>'. _('<strong>Document Title</strong>:  Refers to the relatively brief title of the document (e.g. How to use the download server)<br /><strong>Description:</strong> A brief description to be placed just under the title<br />') .'</p>';

	if ($g->useDocmanSearch()) 
		echo '<p>'. _('Both fields are used by document search engine.'). '</p>';

	echo '<form name="adddata" action="?group_id='.$group_id.'&action=addfile" method="post" enctype="multipart/form-data">
			<table>
				<tr>
					<td style="text-align:right;">
						<strong>'. _('Document Title').'</strong>'.utils_requiredField()
                	.'</td><td>'
                    	.'&nbsp;<input type="text" name="title" size="40" maxlength="255" />&nbsp;'
                    	.sprintf(_('(at least %1$s characters)'), 5)
					.'</td>
				</tr>
				<tr>
					<td style="text-align:right;">
						<strong>'. _('Description') .'</strong>'.utils_requiredField()
                	.'</td><td>'
                    	.'&nbsp;<input type="text" name="description" size="50" maxlength="255" />&nbsp;'
                    	.sprintf(_('(at least %1$s characters)'), 10)
					.'</td>
				</tr>';
	echo '
				<tr>
					<td style="text-align:right;">
						<strong>'. _('Upload File') .'</strong>'. utils_requiredField()
                	.'</td><td>'
						.'&nbsp;<input type="file" name="uploaded_data" size="30" />
						<input type="hidden" name="type" value="httpupload">
					</td>
				</tr>';
	if ($dirid) {
		echo '<input type="hidden" name="doc_group" value="'.$dirid.'">';
	} else {
		echo '
				<tr>
					<td>
						<strong>'. _('Directory that document belongs in').'</strong>
                	</td><td>';
		$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $dirid);
		echo '
					</td>
				</tr>';
	}
	echo '  </table>';
	echo utils_requiredField() . ' '. _('Mandatory field');
	echo '  <div class="docmanSubmitDiv">
		    	<input type="submit" name="submit" value="'. _('Submit Information').' " />
        	</div>
		</form>';
}

echo '</div>';
?>
