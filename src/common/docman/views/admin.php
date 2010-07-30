<?php

/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume
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

require_once ('docman/include/DocumentGroupHTML.class.php');
?>

<script language="javascript">
function displayAdminDiv(id) {
	if ( 'none' == document.getElementById(id).style.display ) {
		document.getElementById(id).style.display = 'inline';
	} else {
		document.getElementById(id).style.display = 'none';
	}
}
function doIt(formid) {
	document.getElementById(formid).submit();
	document.getElementById('submit'+formid).disabled = true;
}
</script>
<?php
echo '<a href="#" onclick="javascript:displayAdminDiv(\'adminpending\')" ><h4>'. _('Admin Pending Files') .'</h4></a>';
echo '<div id="adminpending" style="display:none">';
include ('docman/views/listpendingfile.php');
echo '</div>';

echo '<a href="#" onclick="javascript:displayAdminDiv(\'admintrash\')" ><h4>'. _('Admin Trash') .'</h4></a>';
echo '<div id="admintrash" style="display:none;" >';
echo '<form id="emptytrash" name="emptytrash" method="post" action="?group_id='.$group_id.'&action=emptytrash" >';
echo '<ul>';
echo '<li>'. _('Delete permanenty all documents and document groups with deleted status ') .' <input id="submitemptytrash" type="button" value="Yes" onclick="javascript:doIt(\'emptytrash\')" ></li>';
echo '</ul>';
echo '</form>';
echo '<ul>';
echo '<li><a href="#" onclick="javascript:displayAdminDiv(\'listtrash\')">'. _('Select dir or files to be resurrected from deleted status') .'</a></li>';
echo '</ul>';
echo '<div id="listtrash" style="display:none;" >';
include ('docman/views/listtrashfile.php');
echo '</div>';
echo '</div>';

echo '<a href="#" onclick="javascript:displayAdminDiv(\'adminsearchengine\')" ><h4>'. _('Admin Engine Options') .'</h4></a>';
echo '<div id="adminsearchengine" style="display:none;" >';
echo '<form id="searchengine" name="searchengine" method="post" action="?group_id='.$group_id.'&action=updateenginesearch" >';
echo '<ul>';

$searchEngineStatus = '1';
$labelSearchEngine = _('Enable Search Engine');
if ($g->useDocmanSearch()) {
	$searchEngineStatus='0';
	$labelSearchEngine = _('Disable Search Engine');
}

echo '<li>'.$labelSearchEngine.' <input name="status" type="hidden" value="'.$searchEngineStatus.'"><input id="submitsearchengine" type="button" value="Yes" onclick="javascript:doIt(\'searchengine\')"></li>';
echo '</ul>';
echo '</form>';

if ($g->useDocmanSearch()) {
	if ($d_arr || count($d_arr) > 1) {
		echo '<form id="reindexword" name="reindexword" method="post" action="?group_id='.$group_id.'&action=forcereindexenginesearch">';
		echo '<ul>';
		echo '<li>'. _('Force reindexation search engine') .' <input name="status" type="hidden" value="1"><input id="submitreindexword" type="button" value="Yes" onclick="javascript:doIt(\'reindexword\')"></li>';
		echo '</ul>';
		echo '</form>';
	}
}
echo '</div>';

?>
