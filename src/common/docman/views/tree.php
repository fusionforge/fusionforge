<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
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
global $nested_docs;
global $linkmenu;

if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

/**
 * needed for docman_recursive_display function call
 * see utils.php for more information
 */
$idExposeTreeIndex = 0;
$idhtml = 0;

echo '<div id="documenttree" style="height:100%">';
?>

<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
	var myThemeXPBase = "<?php echo util_make_uri('/jscook/ThemeXP/'); ?>";
/* ]]> */</script>
<script type="text/javascript" src="<?php echo util_make_uri('/jscook/JSCookTree.js'); ?>"></script>
<script src="<?php echo util_make_uri('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

<div id="myMenuID" style="overflow:auto;"></div>

<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
	var myMenu =
		[
			['<span class="JSCookTreeFolderClosed"><i><img alt="" src="' + myThemeXPBase + 'folder1.gif" /></i></span><span id="ctItemID0" class="JSCookTreeFolderOpen"><i><img alt="" src="' + myThemeXPBase + 'folderopen1.gif" /></i></span>', '/', '<?php echo '?group_id='.$group_id.'&view='.$linkmenu ?>', '', '', <?php docman_recursive_display(0); ?>
			]
		];

	var treeIndex = ctDraw('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 1);
	ctExposeTreeIndex(treeIndex, <?php echo $idExposeTreeIndex ?>);
	var openItem = ctGetSelectedItem(treeIndex);
	ctOpenFolder(openItem);
/* ]]> */</script>

<?php
echo '</div>';
?>
