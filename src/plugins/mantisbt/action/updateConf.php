<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $group_id; // the group id
global $mantisbt; // the mantisbt object
global $group; // the group object

$confArr = array();
$confArr['url'] = getStringFromRequest('url');
$confArr['sync_roles'] = 0;
$confArr['sync_users'] = 0;
$confArr['soap_user'] = getStringFromRequest('soap_user');
$confArr['soap_password'] = getStringFromRequest('soap_password');

if (!$mantisbt->updateConf($group_id, $confArr))
	session_redirect('/plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname=mantisbt&error_msg='.urlencode($group->getErrorMessage()));

$feedback = _('MantisBT configuration successfully updated.');
session_redirect('/plugins/mantisbt/?type=admin&group_id='.$group_id.'&pluginname=mantisbt&feedback='.urlencode($feedback));
?>
