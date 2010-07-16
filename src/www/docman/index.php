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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'docman/include/doc_utils.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';

/*
	EXPERIMENTAL CODE TO USE JAVASCRIPT TREE
*/
function docman_recursive_display($docgroup) {
	global $nested_groups,$nested_docs,$group_id;
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			if (isset($nested_docs[$dg->getID()]) && is_array($nested_docs[$dg->getID()])) {
				echo "
		['".'<span class="JSCookTreeFolderClosed"><i><img alt="" src="\' + ctThemeXPBase + \'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="\' + ctThemeXPBase + \'folderopen1.gif"></i></span>'."', '".addslashes($dg->getName())."', '#', '', '',";
				docman_recursive_display($dg->getID());
				foreach ($nested_docs[$dg->getID()] as $d) {
					$docurl=util_make_url ('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.urlencode($d->getFileName()));
					$docname=addslashes($d->getName())." (".htmlspecialchars($d->getFileName(), ENT_QUOTES).")";
					$docdesc=addslashes($d->getDescription());
					echo ",['','".$docname."','".$docurl."','','".$docdesc."' ]";
				}
				echo ",
		],";
			}
	
		}
	}
}

$group_id = getIntFromRequest('group_id');
$feedback = getStringFromRequest('feedback');

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$df = new DocumentFactory($g);
if ($df->isError()) {
	exit_error(_('Error'),$df->getErrorMessage());
}

$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error(_('Error'),$dgf->getErrorMessage());
}


// check if the user is docman's admin
if (forge_check_perm ('docman', $group_id, 'approve')) {
	$is_editor = true;
} else {
	$is_editor = false;
}

docman_header(_('Document Manager: Display Document'),_('Project: %1$s'));
echo '<h1>'.sprintf(_('Documents for %1$s'), $g->getPublicName()) .'</h1>';

$d_arr =& $df->getDocuments();
if (!$d_arr || count($d_arr) <1){
	$d_arr = &$df->getDocuments();
}

if (!$d_arr || count($d_arr) < 1) {
	print '<div class="warning_msg">'._('This project has no visible documents').'</div>';
} else {
	// Get the document groups info
	$nested_groups =& $dgf->getNested();	

	$nested_docs=array();
	//put the doc objects into an array keyed off the docgroup
	foreach ($d_arr as $doc) {
		$nested_docs[$doc->getDocGroupID()][] = $doc;
	}
	?>
	<script language="JavaScript"><!--
	var myThemeXPBase = "<?php echo util_make_uri ('/jscook/ThemeXP/'); ?>";
	--></script>
	<script language="JavaScript" src="<?php echo util_make_uri ('/jscook/JSCookTree.js'); ?>"></script>
	<link rel="stylesheet" href="<?php echo util_make_uri ('/jscook/ThemeXP/theme.css'); ?>" type="text/css" />
	<script src="<?php echo util_make_uri ('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

	<br>
	<form action="">
		<input style="width: 100px" type="button" value="<?php echo _('expand all'); ?>" onclick="ctExpandTree('myMenuID',9);" />
		<input style="width: 100px" type="button" value="<?php echo _('collapse all'); ?>" onclick="ctCollapseTree('myMenuID');" />
	</form>
	<br>
	<div id="myMenuID"></div>

	<script language="JavaScript"><!--
	var myMenu =
	[
		['<span class="JSCookTreeFolderClosed"><i><img alt="" src="' + ctThemeXPBase + 'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="' + ctThemeXPBase + 'folderopen1.gif" /></i></span>', '/', '#', '', '', <?php docman_recursive_display(0); ?>
		]
	];
	ctDraw ('myMenuID', myMenu, ctThemeXP1, 'ThemeXP', 0, 1);
	--></script>

	<noscript>
		<?php docman_display_documents($nested_groups,$df,$is_editor); ?>
	</noscript>
	<?php
}
docman_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
