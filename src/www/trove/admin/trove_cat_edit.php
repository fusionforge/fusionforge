<?php
/**
 *
 * Admin: Trove Admin: edit category
 *
 * This page is linked from trove_cat_list.php, page to browse full
 * Trove tree.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet
 * http://fusionforge.org/
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


require_once('pre.php');
require_once('www/admin/admin_utils.php');
require_once('../include/utils.php');

require_once('common/include/escapingUtils.php');
require_once('common/trove/TroveCategory.class.php');

$categoryId = getIntFromGet('trove_cat_id');
$category = new TroveCategory($categoryId);

if($category->isError()) {
	exit_error($category->getErrorMessage());
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
	Main Code
*/

site_admin_header(array('title'=>_('Site Admin: Trove - Category List'))) ;
?>

<table width="100%" border="0">
	<tr>
		<td width="60%" valign="top">
		  <h3><?php echo _('Site Admin: Trove - Edit Category'); ?></h3>

<form action="trove_cat_edit.php?trove_cat_id=<?php echo $category->getId(); ?>" method="post">
<input type="hidden" name="do" value="updateCategory" />

		  <p><?php echo _('New category short name (no spaces, Unix-like): '); ?>
<br /><input type="text" name="shortName" value="<?php echo $category->getShortName(); ?>" /></p>

		  <p><?php echo _('New category full name (80 characters max): '); ?>
<br /><input type="text" name="fullName" value="<?php echo $category->getFullName(); ?>" /></p>

		  <p><?php echo _('New category description (255 characters max): '); ?>
<br /><input type="text" name="description" size="80" value="<?php echo $category->getDescription(); ?>" /></p>

<br /><input type="submit" name="submit" value="<?php echo _('update'); ?>" /><input type="submit" name="delete" value="<?php echo _('delete'); ?>" />
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
		  <p><?php echo _('Parent Category: '); ?>
<br /><select name="form_parent">
<?php
// generate list of possible parents
$res_parent = db_query_params ('SELECT shortname,fullname,trove_cat_id FROM trove_cat',
			array());

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
