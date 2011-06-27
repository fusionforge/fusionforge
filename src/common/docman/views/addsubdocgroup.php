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
global $group_id; // id of the group
global $dirid; //id of the doc_group

?>
<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
function doItAddSubGroup() {
	document.getElementById('addsubgroup').submit();
	document.getElementById('submitaddsubgroup').disabled = true;
}
/* ]]> */</script>
<?php
echo '<div class="docmanDivIncluded" >';
echo '<form id="addsubgroup" name="addsubgroup" method="post" action="?group_id='.$group_id.'&amp;action=addsubdocgroup&amp;dirid='.$dirid.'">';
if ($dirid) {
	echo _('Name of the document subfolder to create:'). ' ';
} else {
	echo _('Name of the document folder to create:'). ' ';
}
echo '<input type="text" name="groupname" size="40" maxlength="255" />';
echo '<input id="submitaddsubgroup" type="button" value="'. _('Create') .'" onclick="javascript:doItAddSubGroup()" />';
echo '</form>';
echo '</div>';
?>
