<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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
global $dirid; // id of the doc_group

if (!forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

// plugin projects-hierarchy
$actionurl = '?group_id='.$group_id.'&amp;action=addsubdocgroup&amp;dirid='.$dirid;
if (isset($childgroup_id) && $childgroup_id) {
	$g = group_get_object($childgroup_id);
	$actionurl .= '&amp;childgroup_id='.$childgroup_id;
}

?>
<script type="text/javascript">//<![CDATA[
function doItAddSubGroup() {
	document.getElementById('addsubgroup').submit();
	document.getElementById('submitaddsubgroup').disabled = true;
}
//]]></script>
<?php
echo '<div class="docmanDivIncluded">';
echo '<form id="addsubgroup" name="addsubgroup" method="post" action="'.$actionurl.'">';
if ($dirid) {
	echo _('Name of the document subfolder to create')._(': ');
} else {
	echo _('Name of the document folder to create')._(': ');
}
echo '<input required="required" type="text" name="groupname" size="40" maxlength="255" />';
echo '<input id="submitaddsubgroup" type="button" value="'. _('Create') .'" onclick="javascript:doItAddSubGroup()" />';
echo '</form>';
echo '</div>';
