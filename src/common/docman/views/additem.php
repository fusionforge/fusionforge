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
?>

<script type="text/javascript">//<![CDATA[
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
//]]></script>

<div id="tabs">
<ul>
<li><a id="tab-new-document" href="#tabs-new-document" class="tabtitle" title="<?php echo _('Submit a new document in this folder.') ?>">
    <?php echo _('New Document') ?></a></li>
<?php if (forge_check_perm('docman', $group_id, 'approve')) { ?>
<li><a id="tab-new-folder" href="#tabs-new-folder" class="tabtitle" title="<?php echo _('Create a folder based on this name.') ?>">
    <?php echo _('New Folder') ?></a></li>
<li><a id="tab-inject-tree" href="#tabs-inject-tree" class="tabtitle" title="<?php echo _('Create a full folders tree using an compressed archive. Only ZIP format support.') ?>">
    <?php echo _('Inject Tree') ?></a></li>
<?php } ?>
</ul>

<?php
echo '<div id="tabs-new-document">';
echo '<div class="docman_div_include" id="addfile">';
include ($gfcommon.'docman/views/addfile.php');
echo '</div>';
echo '</div>';

if (forge_check_perm('docman', $group_id, 'approve')) {
	echo '<div id="tabs-new-folder">';
	echo '<div class="docman_div_include" id="addsubdocgroup">';
	include ($gfcommon.'docman/views/addsubdocgroup.php');
	echo '</div>';
	echo '</div>';
	echo '<div id="tabs-inject-tree">';
	echo '<div class="docman_div_include" id="zipinject">';
	echo '<form id="injectzip" name="injectzip" method="post" action="?group_id='.$group_id.'&amp;action=injectzip&amp;dirid='.$dirid.'" enctype="multipart/form-data">';
	echo '<p>';
	echo '<label>' . _('Upload archive:') . ' </label><input type="file" name="uploaded_zip" required="required" />'.sprintf(_('(max upload size: %s)'),human_readable_bytes(util_get_maxuploadfilesize()));
	echo '<input id="submitinjectzip" type="button" value="'. _('Inject Tree') .'" />';
	echo '</p>';
	echo '</form>';
	echo '</div>';
	echo '</div>';
}

echo '</div>';
