<?php
/**
  *
  * Site Admin: Trove Admin: add new leaf category
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

if ($GLOBALS['submit']) {
	$newroot = trove_getrootcat($GLOBALS['form_parent']);

	if ($GLOBALS[form_shortname]) {
		$res = db_query("
			INSERT INTO trove_cat 
				(shortname,fullname,description,parent,version,root_parent)
			VALUES (
				'."htmlspecialchars($form_shortname)."',
				'."htmlspecialchars($form_fullname)."',
				'."htmlspecialchars($form_description)."',
				'$form_parent',
				'".date("Ymd",time())."01',
				'$newroot'
			)
		");

		if (!$res || db_affected_rows($res)<1) {
			exit_error(
				'Error In Trove Operation',
				db_error()
			);
		}
	} 

	// update full paths now
        trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);

	session_redirect("/admin/trove/trove_cat_list.php");
} 

site_admin_header(array('title'=>'Site Admin: Trove - Add Node'));
?>

<h3>Add New Trove Category</h3>

<form action="trove_cat_add.php" method="post">
<p>Parent Category:
<br><select name="form_parent">

<?php

// generate list of possible parents
$res_cat = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat");
while ($row_cat = db_fetch_array($res_cat)) {
	print ('<OPTION value="'.$row_cat["trove_cat_id"].'">'.$row_cat["fullname"]."\n");
}

?>

</select>
<p>New category short name (no spaces, unix-like):
<br><input type="text" name="form_shortname">
<p>New category full name (VARCHAR 80):
<br><input type="text" name="form_fullname">
<p>New category description (VARCHAR 255):
<br><input type="text" size="80" name="form_description">
<br><input type="submit" name="submit" value="Add">
</form>

<?php

site_admin_footer(array());

?>
