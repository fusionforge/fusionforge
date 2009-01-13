<?php
/**
  *
  * Site Admin: Trove Admin: add new leaf category
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: trove_cat_add.php,v 1.17 2001/04/10 16:10:30 pfalcon Exp $
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
				'".htmlspecialchars($form_shortname)."',
				'".htmlspecialchars($form_fullname)."',
				'".htmlspecialchars($form_description)."',
				'$form_parent',
				'".date("Ymd",time())."01',
				'$newroot'
			)
		");

		if (!$res || db_affected_rows($res)<1) {
			exit_error(
				_('Error in Trove operation'),
				db_error()
			);
		}
	} 

	// update full paths now
        trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);

	session_redirect("/admin/trove/trove_cat_list.php");
} 

site_admin_header(array('title'=>_('Site Admin: Trove - Add Node')));
?>

<h3><?php echo _('Add New Trove Category'); ?></h3>

<form action="trove_cat_add.php" method="post">
	<p><?php echo _('Parent Category: '); ?><?php echo utils_requiredField(); ?>
<br /><select name="form_parent">

<?php

// generate list of possible parents
// <paul@zootweb.com> 4/2/2003 - If we were given a parent trove use it
// in the "Parent Category" box otherwise give them the complete list.
if (isset($parent_trove_cat_id)) {
	if ($parent_trove_cat_id == 0) {
		print ('<option value="0">root</option>\n');
	} else {
		$res_cat = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat WHERE trove_cat_id=$parent_trove_cat_id");
		while ($row_cat = db_fetch_array($res_cat)) {
			print ('<option value="'.$row_cat["trove_cat_id"].'">'.$row_cat["fullname"]."</option>\n");
		}
	}
} else {
	print ('<option value="0">root</option>\n');
	$res_cat = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat");
	while ($row_cat = db_fetch_array($res_cat)) {
		print ('<option value="'.$row_cat["trove_cat_id"].'">'.$row_cat["fullname"]."</option>\n");
	}
}

?>

</select></p>
<p><?php echo _('New category short name (no spaces, Unix-like): '); ?> <?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_shortname" /></p>
	<p><?php echo _('New category full name (80 characters max): '); ?> <?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_fullname" /></p>
<p><?php echo _('New category description (255 characters max): '); ?> <?php echo utils_requiredField(); ?>
<br /><input type="text" size="80" name="form_description" />
<br /><input type="submit" name="submit" value="<?php echo _('Add'); ?>" /></p>
</form>

<?php

site_admin_footer(array());

?>
