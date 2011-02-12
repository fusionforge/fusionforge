<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $df; // document factory
global $dgf; // document group factory

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

$df->setStateID('2');

/**
 * var must be call d_arr & nested_groups
 * because used by tree.php
 */
$d_arr =& $df->getDocuments();
$nested_groups =& $dgf->getNested('2');

if ((!$d_arr || count($d_arr) < 1) && (!$nested_groups || count($nested_groups) < 1)) {
	echo '<div class="warning">'._('Trash is empty').'</div>';
} else {

	echo '<form id="emptytrash" name="emptytrash" method="post" action="?group_id='.$group_id.'&action=emptytrash" >';
	echo '<ul>';
	echo '<li><input id="submitemptytrash" type="submit" value="'. _('Delete permanently all documents with deleted status.') .'" ></li>';
	echo '</ul>';
	echo '</form>';

	echo '<div style="float:left; width:17%; padding-right:3px; margin-right:2px; border-right: dashed 1px black;">';
	include ($gfcommon.'docman/views/tree.php');
	echo '</div>';
	echo '<div style="float:left; width:82%;">';
		var_dump($d_arr);
		var_dump($nested_groups);
	echo '</div>';
}
?>
