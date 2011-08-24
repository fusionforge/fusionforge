<?php
/**
 * Projects Hierarchy plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
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

global $projectsHierarchy; // the projects-hierarchy plugin object

$confArr = array();
$confArr['tree'] = getIntFromRequest('tree');
$confArr['docman'] = getIntFromRequest('docman');
$confArr['delegate'] = 0;

if (!$projectsHierarchy->updateGlobalConf($confArr)) {
	$error_msg = _('Failed to update global configuration.');
	session_redirect('/plugins/'.$projectsHierarchy->name.'/?type=globaladmin&pluginname='.$projectsHierarchy->name.'&error_msg='.urlencode($error_msg));
}

$feedback = _('Projects Hierarchy global configuration successfully updated.');
session_redirect('/plugins/'.$projectsHierarchy->name.'/?type=globaladmin&pluginname='.$projectsHierarchy->name.'&feedback='.urlencode($feedback));
?>
