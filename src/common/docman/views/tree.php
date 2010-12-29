<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
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
global $d_arr; // documents array
global $group_id; // id of the group

echo '<h3>Document Tree</h3>';
$nested_docs = array();
$idExposeTreeIndex = 0;
$idhtml = 0;
if ($d_arr != NULL ) {
	if (!$d_arr || count($d_arr) > 0) {
		// Get the document groups info
		//put the doc objects into an array keyed off the docgroup
		foreach ($d_arr as $doc) {
			$nested_docs[$doc->getDocGroupID()][] = $doc;
		}
	}
}
echo '<div id="documenttree" style="height:100%">';
?>

<script type="text/javascript">
	var myThemeXPBase = "<?php echo util_make_uri ('/jscook/ThemeXP/'); ?>";
</script>
<script type="text/javascript" src="<?php echo util_make_uri ('/jscook/JSCookTree.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo util_make_uri ('/jscook/ThemeXP/theme.css'); ?>" type="text/css" />
<script src="<?php echo util_make_uri ('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

<div id="myMenuID" style="overflow:auto;"></div>

<script type="text/javascript">
	var myMenu =
		[
			['<span class="JSCookTreeFolderClosed"><i><img alt="" src="' + myThemeXPBase + 'folder1.gif" /></i></span><span id="ctItemID0" class="JSCookTreeFolderOpen"><i><img alt="" src="' + myThemeXPBase + 'folderopen1.gif" /></i></span>', '/', '#', '', '', <?php docman_recursive_display(0); ?>
			]
		];

	var treeIndex = ctDraw ('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 1);
	ctExposeTreeIndex (treeIndex, <?php echo $idExposeTreeIndex ?>);
	var openItem = ctGetSelectedItem (treeIndex)
	ctOpenFolder (openItem)
</script>

<?php
echo '</div>';
?>
