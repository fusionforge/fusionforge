<?php
/**
 * Site Admin: Trove Admin: add new leaf category
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
				$Language->getText('admin_trove_cat_add','error_in_trove_openration'),
				db_error()
			);
		}
	} 

	// update full paths now
        trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);

	session_redirect("/admin/trove/trove_cat_list.php");
} 

site_admin_header(array('title'=>$Language->getText('admin_trove_cat_add','title')));
?>

<h3><?php echo $Language->getText('admin_trove_cat_add','add_new_trove_category'); ?></h3>

<form action="trove_cat_add.php" method="post">
<p><?php echo $Language->getText('admin_trove_cat_add','parent_category'); ?>:<?php echo utils_requiredField(); ?>
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
<p><?php echo $Language->getText('admin_trove_cat_add','new_category_short_name'); ?>:<?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_shortname" /></p>
<p><?php echo $Language->getText('admin_trove_cat_add','new_category_full_name'); ?>:<?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_fullname" /></p>
<p><?php echo $Language->getText('admin_trove_cat_add','new_category_description'); ?>:<?php echo utils_requiredField(); ?>
<br /><input type="text" size="80" name="form_description" />
<br /><input type="submit" name="submit" value="<?php echo $Language->getText('admin_trove_cat_add','add'); ?>" /></p>
</form>

<?php

site_admin_footer(array());

?>
