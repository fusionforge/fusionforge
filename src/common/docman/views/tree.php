<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
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
global $nested_docs;
global $linkmenu;
global $g; // the group object

if (!forge_check_perm('docman', $group_id, 'read')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

/**
 * needed for docman_recursive_display function call
 * see utils.php for more information
 */
$idExposeTreeIndex = 0;
$idhtml = 0;

$displayProjectName = 0;

if ($g->usesPlugin('projects_hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects_hierarchy');
	$projectIDsArray = array($g->getID());
	$projectIDsArray = array_merge($projectIDsArray, $projectsHierarchy->getFamilyID($group_id, 'child', false, 'validated'));
	if (sizeof($projectIDsArray) >= 2)
		$displayProjectName = 1;
} else {
	$projectIDsArray = array($g->getID());
}
echo '<div id="documenttree" style="height:100%">';
foreach ($projectIDsArray as $key=>$projectID) {
	$groupObject = group_get_object($projectID);
	if ($groupObject->usesDocman() && forge_check_perm('docman', $groupObject->getID(), 'read')) {
		$dm = new DocumentManager($groupObject);
		$dm->getJSTree($linkmenu, $displayProjectName);
		echo '<noscript>';
		echo '<ul>';
		$label = '/';
		if ($displayProjectName)
			$label = $groupObject->getPublicName();

		echo '<li><a href="?group_id='.$groupObject->getID().'&amp;view='.$linkmenu.'">'.$label.'</a></il>';
		$dm->getTree($linkmenu);
		echo '</ul>';
		echo '</noscript>';
	}
}
echo '</div>';
?>
