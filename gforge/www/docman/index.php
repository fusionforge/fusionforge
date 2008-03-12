<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once('../env.inc.php');
require_once('pre.php');
require_once('include/doc_utils.php');
require_once('common/docman/DocumentFactory.class.php');
require_once('common/docman/DocumentGroupFactory.class.php');

/*
	EXPERIMENTAL CODE TO USE JAVASCRIPT TREE
*/
function docman_recursive_display($docgroup) {
	global $nested_groups,$nested_docs,$group_id;
	if (is_array(@$nested_groups[$docgroup])) {
		foreach ($nested_groups[$docgroup] as $dg) {
			echo "
		['".'<span class="JSCookTreeFolderClosed"><i><img alt="" src="\' + ctThemeXPBase + \'folder1.gif" /></i></span><span class="JSCookTreeFolderOpen"><i><img alt="" src="\' + ctThemeXPBase + \'folderopen1.gif"></i></span>'."', '".$dg->getName()."', '#', '', '',";
			docman_recursive_display($dg->getID());
			if (is_array($nested_docs[$dg->getID()])) {
				foreach ($nested_docs[$dg->getID()] as $d) {
					$docurl=util_make_url ('/docman/view.php/'.$group_id.'/'.$d->getID().'/'.$d->getFileName());
					$docname=addslashes($d->getName())." (".$d->getFileName().")";
					$docdesc=addslashes($d->getDescription());
					echo ",['','".$docname."','".$docurl."','','".$docdesc."' ]";
				}
			}
			echo ",
		],";
	
		}
	}
}

$group_id = getIntFromRequest('group_id');
$language_id = getStringFromRequest('language_id');

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


// Save chosen language in docman and print document details in documents list
if ($language_id)
{
       setcookie("my_language_id", "$language_id", time()+3600*24*999);
}
if (!$language_id && isset($_COOKIE["my_language_id"]))
{
       $language_id = $_COOKIE["my_language_id"];
}

// the "selected language" variable will be used in the links to navigate the
// document groups tree

if (!$language_id) {
	if (session_loggedin()) {
		$language_id = $LUSER->getLanguage();
	} else {
		$language_id = 1;
	}
	
	$selected_language = $language_id;
} else if ($language_id == "*") {
	$language_id = 0 ;
	$selected_language = "*";
} else {
	$selected_language = $language_id;
}

// check if the user is docman's admin
$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isDocEditor()) {
	$is_editor = false;
} else {
	$is_editor = true;
}

$df->setLanguageID($language_id);

docman_header(_('Document Manager: Display Document'),_('Project: %1$s'));

$d_arr =& $df->getDocuments();
if (!$d_arr || count($d_arr) <1){
	$df->setLanguageId(0);
	$d_arr = &$df->getDocuments();
}

if (!$d_arr || count($d_arr) < 1) {
	print "<strong>"._('This project has no visible documents')."</strong>";
} else {
	doc_droplist_count($group_id, $language_id, $g);

	// Get the document groups info
	$nested_groups =& $dgf->getNested();	

	$nested_docs=array();
	//put the doc objects into an array keyed off the docgroup
	foreach ($d_arr as $doc) {
		$nested_docs[$doc->getDocGroupID()][] = $doc;
	}
	?>
	<script language="JavaScript"><!--
	var myThemeXPBase = "<?php echo util_make_url ('/jscook/ThemeXP/'); ?>";
	--></script>
	<script language="JavaScript" src="<?php echo util_make_url ('/jscook/JSCookTree.js'); ?>"></script>
	<link rel="stylesheet" href="<?php echo util_make_url ('/jscook/ThemeXP/theme.css'); ?>" type="text/css" />
	<script src="<?php echo util_make_url ('/jscook/ThemeXP/theme.js'); ?>" type="text/javascript"></script>

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
