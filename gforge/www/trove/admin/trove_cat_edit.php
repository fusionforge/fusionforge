<?php
/**
 *
 * Site Admin: Trove Admin: edit category
 *
 * This page is linked from trove_cat_list.php, page to browse full
 * Trove tree.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet
 * http://gforge.org/
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
require_once('www/admin/admin_utils.php');
require_once('../include/utils.php');

require_once('common/include/escapingUtils.php');
require_once('common/trove/TroveCategory.class.php');

$categoryId = getIntFromGet('trove_cat_id');
$category = new TroveCategory($categoryId);

if($category->isError()) {
	exit_error($Language->getText('global','error'), $category->getErrorMessage());
}

$do = getStringFromRequest('do');

switch($do) {
	case 'addTranslation' :
		$label = new TroveCategoryLabel($category);
		$label->create(getStringFromPost('label'), getIntFromPost('language_id'));
		// TODO : gestion d'erreurs, affichage d'un flag
		break;
	case 'removeTranslation' :
		$label = new TroveCategoryLabel($category, getIntFromRequest('label_id'));
		$label->remove();
		// TODO : gestion d'erreurs, affichage d'un flag
		break;
	case 'updateCategory' :
		if(!$category->update(getStringFromPost('shortName'), getStringFromPost('fullName'), getStringFromPost('description'))) {
			echo $category->getErrorMessage();
		}
		break;
	case 'moveCategory' :
		break;
	case 'removeCategory' :
		break;
	case 'createSubcategory' :
		break;
}

/*
if ($GLOBALS["submit"]) {

	$newroot = trove_getrootcat($GLOBALS['form_parent']);

	if ($GLOBALS[form_shortname]) {
		if ($form_trove_cat_id == $form_parent) {
			exit_error($Language->getText(
								'admin_trove_cat_edit','error_tove_equal_parent'),
								db_error()
			);
		} else {
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
		}

		if (!$res || db_affected_rows($res)<1) {
			exit_error(
				$Language->getText('admin_trove_cat_edit','error_in_trove_operation'),
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
		exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation_cant_delete'));
	}
	$sql = "select count(*) from trove_group_link where trove_cat_id='$form_trove_cat_id'";
	$res = db_numrows(db_query($sql));
	if ($res > 0) {
		exit_error($Language->getText('admin_trove_cat_edit','error_in_trove_operation'), $Language->getText('admin_trove_cat_edit','error_in_trove_operation_cant_delete_in_use'));
	}
	
	$res = db_query("
		SELECT trove_cat_id FROM trove_cat WHERE parent='$form_trove_cat_id'
	");

	if (!$res) {
		exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
	}
	if (db_numrows($res)>0) {
		exit_error( $Language->getText('admin_trove_cat_edit','cant_delete_has_subcategories'), db_error());
	} else {
		$res=db_query(" DELETE FROM trove_treesums WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res || db_affected_rows($res)<1) {
			 exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
		$res=db_query(" DELETE FROM trove_cat WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res || db_affected_rows($res)<1) {
			exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
		$res=db_query(" DELETE FROM trove_group_link WHERE trove_cat_id='$form_trove_cat_id'");
		if (!$res) {
			exit_error( $Language->getText('admin_trove_cat_edit','error_in_trove_operation'), db_error());
		}
	}
	session_redirect("/admin/trove/trove_cat_list.php");
}
*/
/*
	Main Code
*/

site_admin_header(array('title'=>$Language->getText('admin_trove_cat_edit','title')));
?>

<table width="100%" border="0">
	<tr>
		<td width="60%" valign="top">
<h3><?php echo $Language->getText('admin_trove_cat_edit','edit_trove_category'); ?></h3>

<form action="trove_cat_edit.php?trove_cat_id=<?php echo $category->getId(); ?>" method="post">
<input type="hidden" name="do" value="updateCategory" />

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_short_name'); ?>:
<br /><input type="text" name="shortName" value="<?php echo $category->getShortName(); ?>" /></p>

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_full_name'); ?>:
<br /><input type="text" name="fullName" value="<?php echo $category->getFullName(); ?>" /></p>

<p><?php echo $Language->getText('admin_trove_cat_edit','new_category_description'); ?>:
<br /><input type="text" name="description" size="80" value="<?php echo $category->getDescription(); ?>" /></p>

<br /><input type="submit" name="submit" value="<?php echo $Language->getText('admin_trove_cat_edit','update'); ?>" /><input type="submit" name="delete" value="<?php echo $Language->getText('admin_trove_cat_edit','delete'); ?>" />
</form>
<?php
	$tableHeaders = array(
		'Label',
		'Language',
		'Actions'
	);
	?>
	<h3>Localization</h3>
	<form method="post" action="trove_cat_edit.php?trove_cat_id=<?php  echo $category->getId(); ?>">
		<input type="hidden" name="do" value="addTranslation" />
	<?php
	echo $HTML->listTableTop($tableHeaders);

	$labels =& $category->getLabels();
	$alreadyDefined = array();
	
	$keys = array_keys($labels);
	$count =0;

	foreach($keys AS $key) {
		$currentLabel =& $labels[$key];
		$alreadyDefined[] = $currentLabel->getLanguageId();
		echo '<tr '. $HTML->boxGetAltRowStyle($count) .'>';
		echo '<td width="60%">'.$currentLabel->getLabel().'</td>';
		echo '<td width="20%">'.$currentLabel->getLanguageName().'</td>';
		echo '<td width="20%" align="center"><a href="trove_cat_edit.php?trove_cat_id='.$category->getId().'&do=removeTranslation&label_id='.$currentLabel->getId().'">Remove</td>';
		echo '</tr>';
		$count++;
	}
	?>
	<tr <?php echo $HTML->boxGetAltRowStyle($count); ?>>
		<td width="60%"><input type="text" name="label" size="40" /></td>
		<td width="20%"><?php echo getLanguageSelectionPopup ($alreadyDefined); ?></td>
		<td width="20%"><input type="submit" value="Add a translation" /></td>
	</tr>
	<?php
	echo $HTML->listTableBottom();
	?>
	</form>
<form>
<h3>Move the category</h3>
<p><?php echo $Language->getText('admin_trove_cat_edit','parent_category'); ?>
<br /><select name="form_parent">
<?php
// generate list of possible parents
$res_parent = db_query("SELECT shortname,fullname,trove_cat_id FROM trove_cat");

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
</form>
	<h3>Remove the category</h3>
	Remove category and subcategories
			</td>
			<td width="40%" valign="top">
				<?php
				$tableHeaders = array('Subcategories');
				echo $HTML->listTableTop($tableHeaders);
				$childrenCategories = $category->getChildren();
				for($i = 0, $max = count($childrenCategories); $i < $max; $i++) {
					$childCategory =& $childrenCategories[$i];
					echo '<tr '. $HTML->boxGetAltRowStyle($count) .'>';
					echo '<td><a href="trove_cat_edit.php?trove_cat_id='.$childCategory->getId().'">'.$childCategory->getFullName().'</td>';
					echo '</tr>';
				}
				if($max == 0) {
					echo '<tr '. $HTML->boxGetAltRowStyle(0) .'>';
					echo '<td>None found</td>';
					echo '</tr>';
				}
				echo $HTML->listTableBottom();
				?>
			</td>
		</tr>
	</table>
<?php

	site_admin_footer(array());

?>
