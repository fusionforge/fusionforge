<?php
/**
  *
  * Site Admin: Trove Admin: edit category
  *
  * This page is linked from trove_cat_list.php, page to browse full
  * Trove tree.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/include/trove.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

// ########################################################

if ($GLOBALS["submit"]) {

	$newroot = trove_getrootcat($GLOBALS['form_parent']);

	if ($GLOBALS[form_shortname]) {
		$res = db_query("
			UPDATE trove_cat
			SET	shortname='".htmlspecialchars($form_shortname)."',
				fullname='".htmlspecialchars($form_fullname)."',
				description='".htmlspecialchars($form_description)."',
				parent='$form_parent',
				version='".date("Ymd",time())."01',
				root_parent='$newroot'
			WHERE trove_cat_id='$form_trove_cat_id'
		");

		if (!$res || db_affected_rows($res)<1) {
			exit_error(
				'Error In Trove Operation',
				db_error()
			);
		}
	}
	// update full paths now
	if($newroot!=0) {
		trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);
		trove_updaterootparent($form_trove_cat_id,$newroot);
	}
	else {
		trove_genfullpaths($form_trove_cat_id,trove_getfullname($form_trove_cat_id),$form_trove_cat_id);
		trove_updaterootparent($form_trove_cat_id,$form_trove_cat_id);
	}
	db_query("update trove_group_link set trove_cat_root=(select root_parent from trove_cat where trove_cat_id=trove_group_link.trove_cat_id)");

	session_redirect("/admin/trove/trove_cat_list.php");
}

if ($GLOBALS["delete"]) {
	if ($form_trove_cat_id==$default_trove_cat){
		exit_error( 'Error In Trove Operation','Can t delete trove category define as default in local.inc');
	}
	$res = db_query("
		SELECT trove_cat_id FROM trove_cat WHERE parent='$form_trove_cat_id'
	");

	if (!$res) {
		exit_error( 'Error In Trove Operation', db_error());
	}
	if (db_numrows($res)>0) {
		exit_error("Can't delete","That trove cat has sub categories");
	} else {
		$res=db_query(" DELETE FROM trove_cat WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res || db_affected_rows($res)<1) {
			exit_error( 'Error In Trove Operation', db_error());
		}
		$res=db_query(" DELETE FROM trove_group_link WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res) {
			exit_error( 'Error In Trove Operation', db_error());
		}
	}
	session_redirect("/admin/trove/trove_cat_list.php");
}

/*
	Main Code
*/

$res_cat = db_query("SELECT * FROM trove_cat WHERE trove_cat_id=$trove_cat_id");
if (db_numrows($res_cat)<1) {
	exit_error("No Such Category","That trove cat does not exist");
}
$row_cat = db_fetch_array($res_cat);

site_admin_header(array('title'=>'Site Admin: Trove - Edit Category'));
?>

<h3>Edit Trove Category</h3>

<form action="trove_cat_edit.php" method="post">

<p>Parent Category:
<br /><select name="form_parent">

<?php
// generate list of possible parents
$res_parent = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat");

while ($row_parent = db_fetch_array($res_parent)) {
	print ('<option value="'.$row_parent["trove_cat_id"].'"');
	if ($row_cat["parent"] == $row_parent["trove_cat_id"]) print ' selected="selected"';
	print ('>'.$row_parent["fullname"]."</option>\n");
}

?>
</select>

<input type="hidden" name="form_trove_cat_id" value="<?php
  print $GLOBALS['trove_cat_id']; ?>" /></p>

<p>New category short name (no spaces, unix-like):
<br /><input type="text" name="form_shortname" value="<?php print $row_cat["shortname"]; ?>" /></p>

<p>New category full name (VARCHAR 80):
<br /><input type="text" name="form_fullname" value="<?php print $row_cat["fullname"]; ?>" /></p>

<p>New category description (VARCHAR 255):
<br /><input type="text" name="form_description" size="80" value="<?php print $row_cat["description"]; ?>" /></p>

<br /><input type="submit" name="submit" value="Update" /><input type="submit" name="delete" value="Delete" />
</form>

<?php

site_admin_footer(array());

?>
