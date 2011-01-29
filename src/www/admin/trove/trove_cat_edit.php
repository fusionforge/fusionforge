<?php
/**
 * Site Admin: Trove Admin: edit category
 *
 * This page is linked from trove_cat_list.php, page to browse full
 * Trove tree.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
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


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'admin/admin_utils.php';

session_require_global_perm ('forge_admin');

// ########################################################

if (getStringFromRequest('submit')) {
	$form_parent = getStringFromRequest('form_parent');
	$form_shortname = getStringFromRequest('form_shortname');
	$form_trove_cat_id = getIntFromRequest('form_trove_cat_id');
	$form_shortname = getStringFromRequest('form_shortname');
	$form_fullname = getStringFromRequest('form_fullname');
	$form_description = getStringFromRequest('form_description');
	$newroot = trove_getrootcat($form_parent);

	if ($form_shortname) {
		if ($form_trove_cat_id == $form_parent) {
			exit_error(_("Error: a category can't be the same as its own parent: ").db_error(),'trove');
		} else {
			$res = db_query_params ('
				UPDATE trove_cat
				SET	shortname=$1,
					fullname=$2,
					description=$3,
					parent=$4,
					version=$5,
					root_parent=$6
				WHERE trove_cat_id=$7
			',
			array(htmlspecialchars($form_shortname),
			      htmlspecialchars($form_fullname),
			      htmlspecialchars($form_description),
			      $form_parent,
			      date("Ymd",time()).'01',
			      $newroot,
			      $form_trove_cat_id));
		}

		if (!$res || db_affected_rows($res)<1) {
			exit_error(_('Error In Trove Operation :').db_error(),'trove');
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
	db_query_params ('update trove_group_link set trove_cat_root=(select root_parent from trove_cat where trove_cat_id=trove_group_link.trove_cat_id)',
			array()) ;


	session_redirect("/admin/trove/trove_cat_list.php");
}

if (getStringFromRequest("delete")) {
	$form_trove_cat_id = getIntFromRequest('form_trove_cat_id');
	if ($form_trove_cat_id==forge_get_config('default_trove_cat')){
		exit_error( _('Error in trove operation, can\'t delete trove category defined as default in configuration.'),'trove');
	}
	trove_del_cat_id($form_trove_cat_id);
	session_redirect("/admin/trove/trove_cat_list.php");
}

/*
	Main Code
*/

$trove_cat_id = getIntFromRequest("trove_cat_id");

$res_cat = db_query_params ('SELECT * FROM trove_cat WHERE trove_cat_id=$1',
			array($trove_cat_id)) ;

if (db_numrows($res_cat)<1) {
	exit_error( _('No Such Category, That trove cat does not exist'),'trove');
}
$row_cat = db_fetch_array($res_cat);

site_admin_header(array('title'=>_('Edit Trove Category')));
?>

<form action="trove_cat_edit.php" method="post">

<p><?php echo _('Parent Category:'); ?>
<br /><select name="form_parent">

<?php
// generate list of possible parents (a category can't be a parent of itself)
$res_parent = db_query_params ('SELECT shortname,fullname,trove_cat_id FROM trove_cat WHERE trove_cat_id <> $1',
			array($trove_cat_id));

// Place the root node at the start of the list
print('<option value="0"');
if ($row_cat["parent"] == 0) {
	print(' selected="selected"');
}
print('>root</option>');
while ($row_parent = db_fetch_array($res_parent)) {
	print ('<option value="'.$row_parent["trove_cat_id"].'"');
	if ($row_cat["parent"] == $row_parent["trove_cat_id"]) print ' selected="selected"';
	print ('>'.$row_parent["fullname"]."</option>\n");
}

?>
</select>

<input type="hidden" name="form_trove_cat_id" value="<?php
  print $GLOBALS['trove_cat_id']; ?>" /></p>

<p><?php echo _('Category short name (no spaces, unix-like)'); ?>:
<br /><input type="text" name="form_shortname" value="<?php print $row_cat["shortname"]; ?>" /></p>

<p><?php echo _('Category full name (Maximum length is 80 chars)'); ?>:
<br /><input type="text" name="form_fullname" value="<?php print $row_cat["fullname"]; ?>" /></p>

<p><?php echo _('Category description (Maximum length is 255 chars)'); ?>:
<br /><input type="text" name="form_description" size="80" value="<?php print $row_cat["description"]; ?>" /></p>

<br /><input type="submit" name="submit" value="<?php echo _('Update'); ?>" /><input type="submit" name="delete" value="<?php echo _('Delete'); ?>" />
</form>

<?php

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
