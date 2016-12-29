<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2016, Franck Villaume - TrivialDev
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
global $linkmenu;
global $g; // the group object
global $dirid; // the selected directory
global $dm; // the document manager
global $warning_msg;
global $childgroup_id;

if (!forge_check_perm('docman', $group_id, 'read')) {
	$warning_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

echo html_ao('div', array('id' => 'documenttree'));
echo html_ao('ul', array('id' => $g->getUnixName().'-tree'));
$dm->getTree($dirid, $linkmenu);
echo html_ac(html_ap() - 1);
echo html_ao('script', array('type' => 'text/javascript'));
echo '//<![CDATA[
	jQuery(document).ready(function() {
		if (typeof(jQuery(\'#'.$g->getUnixName().'-tree\').simpleTreeMenu) != "undefined") {
			jQuery(\'#'.$g->getUnixName().'-tree\').simpleTreeMenu();
		}
	});
//]]>'."\n";
echo html_ac(html_ap() - 1);
if ($g->usesPlugin('projects-hierarchy')) {
	$projectsHierarchy = plugin_get_object('projects-hierarchy');
	$projectIDsArray = $projectsHierarchy->getFamily($group_id, 'child', true, 'validated');
}
if (isset($projectIDsArray) && is_array($projectIDsArray)) {
	foreach ($projectIDsArray as $key=>$projectID) {
		$groupObject = group_get_object($projectID);
		if ($groupObject->usesDocman() && $projectsHierarchy->getDocmanStatus($groupObject->getID())
			&& forge_check_perm('docman', $groupObject->getID(), 'read')) {
			echo html_e('hr');
			echo html_e('h5', array(), _('Child project')._(': ').util_make_link('/docman/?group_id='.$groupObject->getID(),$groupObject->getPublicName(), array('title'=>_('Browse document manager for this project.'))), false);
			$dmc = new DocumentManager($groupObject);
			echo html_ao('ul', array('id' => $groupObject->getUnixName().'-tree'));
			$dmc->getTree($dirid, $linkmenu);
			echo html_ac(html_ap() - 1);
			echo html_ao('script', array('type' => 'text/javascript'));
			echo '//<![CDATA[
				jQuery(document).ready(function() {
					if (typeof(jQuery(\'#'.$groupObject->getUnixName().'-tree\').simpleTreeMenu) != "undefined") {
						jQuery(\'#'.$groupObject->getUnixName().'-tree\').simpleTreeMenu();
					}
				});
			//]]>'."\n";
			echo html_ac(html_ap() - 1);
		}
		unset($groupObject);
	}
}
if ($childgroup_id) {
	$groupObject = group_get_object($childgroup_id);
	echo html_ao('script', array('type' => 'text/javascript'));
	echo '//<![CDATA[
			jQuery(document).ready(function() {
				if (typeof(jQuery(\'#'.$groupObject->getUnixName().'-tree\').simpleTreeMenu) != "undefined") {
					jQuery(\'#'.$groupObject->getUnixName().'-tree\').simpleTreeMenu(\'expandToNode\', jQuery(\'#leaf-'.$dirid.'\'));
				}
			});
		//]]>'."\n";
	echo html_ac(html_ap() - 1);
} else {
	echo html_ao('script', array('type' => 'text/javascript'));
	echo '//<![CDATA[
			jQuery(document).ready(function() {
				if (typeof(jQuery(\'#'.$g->getUnixName().'-tree\').simpleTreeMenu) != "undefined") {
					jQuery(\'#'.$g->getUnixName().'-tree\').simpleTreeMenu(\'expandToNode\', jQuery(\'#leaf-'.$dirid.'\'));
				}
			});
		//]]>'."\n";
	echo html_ac(html_ap() - 1);
}
echo html_ac(html_ap() - 1);
