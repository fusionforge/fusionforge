<?php
/**
 * FusionForge Documentation Manager
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $group_id; // id of the group
global $dirid; //id of the doc_group


if (!forge_check_perm('docman', $group_id, 'submit')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}
?>

<script type="text/javascript">
var controllerAddItem;

jQuery(document).ready(function() {
	controllerAddItem = new DocManAddItemController({
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
echo '<input id="buttonDoc" type="radio" name="type" value="document" />';
echo '<label id="labelDoc" class="tabtitle-nw" title="'. _('Submit a new document in this folder.').'" >'. _('Submit a new document.') .'</label>';
if (forge_check_perm('docman', $group_id, 'approve')) {
	echo '<input id="buttonDir" type="radio" name="type" value="directory" />';
	echo '<label id="labelDir" class="tabtitle-nw" title="'. _('Create a folder based on this name.').'" >'. _('Add a new directory.') .'</label>';
	echo '<input id="buttonZip" type="radio" name="type" value="zip" />';
	echo '<label id="labelZip" class="tabtitle-w" title="'. _('Create a full folders tree using an compressed archive. Only zip format support.').'" >'. _('Inject Tree') . '</label>';
}
if (forge_check_perm('docman', $group_id, 'approve')) {
	echo '<div class="docman_div_include" id="addsubdocgroup" style="display:none;">';
	echo '<h4 class="docman_h4">'. _('Add a new sub folder') .'</h4>';
	include ($gfcommon.'docman/views/addsubdocgroup.php');
	echo '</div>';
}
echo '<div class="docman_div_include" id="addfile" style="display:none">';
echo '<h4 class="docman_h4">'. _('Add a new document') .'</h4>';
include ($gfcommon.'docman/views/addfile.php');
echo '</div>';
if (forge_check_perm('docman', $group_id, 'approve')) {
	echo '<div class="docman_div_include" id="zipinject" style="display:none">';
	echo '<h4 class="docman_h4">'. _('Inject a Tree') .'</h4>';
	echo '<form id="injectzip" name="injectzip" method="post" action="?group_id='.$group_id.'&action=injectzip&dirid='.$dirid.'" enctype="multipart/form-data">';
	echo '<p>';
	echo '<label>' . _('Upload archive:') . ' </label><input type="file" name="uploaded_zip" size="30" />';
	echo '<input id="submitinjectzip" type="button" value="'. _('Inject') .'" onclick="javascript:doItInject()" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
}
echo '</div>';
?>
