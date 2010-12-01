<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Franck Villaume - Capgemini
 * http://fusionforge.org
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
global $group_id; // id of the group
global $dirid; //id of the doc_group

?>

<script type="text/javascript">
var controller;

jQuery(document).ready(function() {
	controllerAddItem = new DocManAddItemController({

		tipsyElements:	[
					{selector: '#labelDoc', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
					{selector: '#labelDir', options:{gravity: 'nw', delayIn: 500, delayOut: 0, fade: true}},
					{selector: '#labelZip', options:{gravity: 'w', delayIn: 500, delayOut: 0, fade: true}}
				],

		divCreateDir:	jQuery('#addsubdocgroup'),
		divCreateDoc:	jQuery('#addfile'),
		divZipInject:	jQuery('#zipinject'),
		buttonDoc:	jQuery('#buttonDoc'),
		buttonDir:	jQuery('#buttonDir'),
		buttonZip:	jQuery('#buttonZip')
	});
});

</script>
<script type="text/javascript">
function doItInject() {
	document.getElementById('injectzip').submit();
	document.getElementById('submitinjectzip').disabled = true;
}
</script>
<?php
echo '<div class="docmanDivIncluded" >';

echo '<input id="buttonDoc" type="radio" name="type" value="document" /><label id="labelDoc" title="'. _('Submit a new document in this directory.').'">'. _('Submit a new document.') .'</label>';
if (forge_check_perm('docman', $group_id, 'approve')) {
	echo '<input id="buttonDir" type="radio" name="type" value="directory" /><label id="labelDir" title="'. _('Create a directory based on this name.').'">'. _('Add a new directory.') .'</label>';
	echo '<input id="buttonZip" type="radio" name="type" value="zip" /><label id="labelZip" title="'. _('Create a full directory tree using a zipfile.').'">'. _('Inject Tree thru Zip') . '</label>';
}
echo '<div class="docman_div_include" id="addsubdocgroup" style="display:none;">';
echo '<h4 class="docman_h4">'. _('Add a new subdirectory') .'</h4>';
include ($gfcommon.'docman/views/addsubdocgroup.php');
echo '</div>';
echo '<div class="docman_div_include" id="addfile" style="display:none">';
echo '<h4 class="docman_h4">'. _('Add a new document') .'</h4>';
include ($gfcommon.'docman/views/addfile.php');
echo '</div>';
echo '<div class="docman_div_include" id="zipinject" style="display:none">';
echo '<h4 class="docman_h4">'. _('Inject a Tree thru Zipfile') .'</h4>';
echo '<form id="injectzip" name="injectzip" method="post" action="?group_id='.$group_id.'&action=injectzip&dirid='.$dirid.'">';
echo '<p>';
echo '<label>' . _('Upload Zip File:') . ' </label><input type="file" name="uploaded_data" size="30" />';
echo '<input id="submitinjectzip" type="button" value="'. _('Inject') .'" onclick="javascript:doItInject()" />';
echo '</p></div>';
echo '</form>';
echo '</div>';
?>
