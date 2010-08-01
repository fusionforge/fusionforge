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
global $d_arr; // documents array

if (!$d_arr || count($d_arr) < 1) {
	print '<div class="warning_msg">'._('This project has no visible documents').'</div>';
} else {
	// Get the document groups info
	$nested_docs=array();
	$idExposeTreeIndex = 0;
	$idhtml = 0;
	//put the doc objects into an array keyed off the docgroup
	foreach ($d_arr as $doc) {
		$nested_docs[$doc->getDocGroupID()][] = $doc;
	}
	echo '<div id="documenttree" style="height:100%">';
	echo '<h3>Document Tree</h3>';
?>
<script language="JavaScript"><!--
	var myThemeXPBase = "<?php echo util_make_uri ('/jscook/ThemeXP/'); ?>";
--></script>
<script language="JavaScript" src="<?php echo util_make_uri ('/jscook/JSCookTree.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo util_make_uri ('/jscook/ThemeXP/theme.css'); ?>" type="text/css" />
<script src="<?php echo util_make_uri ('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

<div id="myMenuID" style="overflow:auto; height:300px"></div>
<!-- if someone wants to make it dynamic.... please do it -->
	<!--<script language="Javascript">
		var mymenuidDiv = document.getElementById("myMenuID");
		var documenttreeDiv = document.getElementById("documenttree");
		mymenuidDiv.style.height = documenttreeDiv.offsetHeight+"px";
	</script>-->

<script language="JavaScript"><!--
	var myMenu =
		[
			['<span class="JSCookTreeFolderClosed"><i><img alt="" src="' + ctThemeXPBase + 'folder1.gif" /></i></span><span id="ctItemID0" class="JSCookTreeFolderOpen"><i><img alt="" src="' + ctThemeXPBase + 'folderopen1.gif" /></i></span>', '/', '#', '', '', <?php docman_recursive_display(0); ?>
			]
		];

	var treeIndex = ctDraw ('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 1);
	ctExposeTreeIndex (treeIndex, <?php echo $idExposeTreeIndex ?>);
	var openItem = ctGetSelectedItem (treeIndex)
	ctOpenFolder (openItem)
--></script>
<?php
	echo '</div>';
}
?>
