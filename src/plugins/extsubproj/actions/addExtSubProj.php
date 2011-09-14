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

global $pluginExtSubProj;
global $group_id;

$confArr = array();
$confArr['newsubprojecturl'] = getStringFromRequest('newsubprojecturl');

/*
if (!$pluginExtSubProj->updateConf($group_id, $confArr)) {
	$error_msg = _('Failed to update configuration.');
	session_redirect('/plugins/'.$pluginExtSubProj->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$pluginExtSubProj->name.'&error_msg='.urlencode($error_msg));
}
*/
$feedback = _('Sub project succesfully added.').$confArr['newsubprojecturl'];
session_redirect('/plugins/'.$pluginExtSubProj->name.'/?type=admin&group_id='.$group_id.'&pluginname='.$pluginExtSubProj->name.'&feedback='.urlencode($feedback));
?>
