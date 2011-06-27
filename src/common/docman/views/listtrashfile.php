<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $df; // document factory
global $nested_groups; // flat groups array

$df->setStateID('2');
$d_trash_arr =& $df->getDocuments();

if (!$d_trash_arr || count($d_trash_arr) < 1) {
	echo '<p class="information">'._('Trash is empty').'</p>';
} else {
?>
<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
function displayTrashDiv() {
	if ( 'none' == document.getElementById('listtrash').style.display ) {
		document.getElementById('listtrash').style.display = 'block';
	} else {
		document.getElementById('listtrash').style.display = 'none';
	}
}
/* ]]> */</script>
<?php
	echo '<form id="emptytrash" name="emptytrash" method="post" action="?group_id='.$group_id.'&amp;action=emptytrash" >';
	echo '<ul>';
	echo '<li><input id="submitemptytrash" type="button" value="'. _('Delete permanently all documents with deleted status.') .'" onclick="javascript:doIt(\'emptytrash\')" /></li>';
	echo '</ul>'."\n";
	echo '</form>';
	echo '<ul>';
	echo '<li><a href="#" onclick="javascript:displayTrashDiv()">'. _('Select documents to be resurrected from deleted status.') .'</a></li>';
	echo '</ul>'."\n";
	echo '<div id="listtrash" style="display:none;" >';
	docman_display_documents($nested_groups, $df, true, 2, 0);
	echo '</div>';
	echo "\n";
}
?>
