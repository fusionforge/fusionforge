<?php
/**
 * Site Admin: Trove Admin: add new leaf category
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'admin/admin_utils.php';

session_require_global_perm ('forge_admin');

// ########################################################

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest("form_key"))) {
		exit_form_double_submit();
	}
	
	$form_parent = getIntFromRequest('form_parent');
	$form_shortname = getStringFromRequest('form_shortname');
	$form_fullname = getStringFromRequest('form_fullname');
	$form_description = getStringFromRequest('form_description');

	$newroot = trove_getrootcat($form_parent);

	if ($form_shortname && $form_fullname) {
		$res = db_query_params ('
			INSERT INTO trove_cat 
				(shortname,fullname,description,parent,version,root_parent)
			VALUES (
				$1,
				$2,
				$3,
				$4,
                                $5,
				$6
			)
		',
			array(htmlspecialchars($form_shortname),
			      htmlspecialchars($form_fullname),
			      htmlspecialchars($form_description),
			      $form_parent,
			      date("Ymd",time()).'01',
			      $newroot));

		if (!$res || db_affected_rows($res)<1) {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('Error In Trove Operation: ').db_error(),'trove');
		}

		// update full paths now
		trove_genfullpaths($newroot,trove_getfullname($newroot),$newroot);

		session_redirect("/admin/trove/trove_cat_list.php");
	} else {
		$error_msg = 'Missing category short name or full name';
	}
} 

site_admin_header(array('title'=>_('Add New Trove Category')));
?>

<form action="trove_cat_add.php" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<p><?php echo _('Parent Category'); ?>:<?php echo utils_requiredField(); ?>
<br /><select name="form_parent">

<?php

// generate list of possible parents
// <paul@zootweb.com> 4/2/2003 - If we were given a parent trove use it
// in the "Parent Category" box otherwise give them the complete list.
$parent_trove_cat_id = getIntFromRequest("parent_trove_cat_id", -1);
if ($parent_trove_cat_id != -1) {
	if ($parent_trove_cat_id == 0) {
		print ('<option value="0">root</option>');
	} else {
		$res_cat = db_query_params ('SELECT shortname,fullname,trove_cat_id FROM trove_cat WHERE trove_cat_id=$1',
			array($parent_trove_cat_id)) ;

		while ($row_cat = db_fetch_array($res_cat)) {
			print ('<option value="'.$row_cat["trove_cat_id"].'">'.$row_cat["fullname"]."</option>");
		}
	}
} else {
	print ('<option value="0">root</option>');
	$res_cat = db_query_params ('SELECT shortname,fullname,trove_cat_id FROM trove_cat',
			array()) ;

	while ($row_cat = db_fetch_array($res_cat)) {
		print ('<option value="'.$row_cat["trove_cat_id"].'">'.$row_cat["fullname"]."</option>");
	}
}

?>

</select></p>
<p><?php echo _('New category short name (no spaces, unix-like)'); ?>:<?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_shortname" /></p>
<p><?php echo _('New category full name (Maximum length is 80 chars)'); ?>:<?php echo utils_requiredField(); ?>
<br /><input type="text" name="form_fullname" /></p>
<p><?php echo _('New category description (Maximum length is 255 chars)'); ?>:
<br /><input type="text" size="80" name="form_description" /></p>
<p><input type="submit" name="submit" value="<?php echo _('Add'); ?>" /></p>
</form>

<?php

site_admin_footer(array());

?>
