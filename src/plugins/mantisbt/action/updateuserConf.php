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

global $mantisbt; // the mantisbt object

$confArr = array();
$confArr['mantisbt_user'] = getStringFromRequest('mantisbt_user');
$confArr['mantisbt_password'] = getStringFromRequest('mantisbt_password');

if (!$mantisbt->updateUserConf($confArr))
	session_redirect('/plugins/mantisbt/?type=user&pluginname='.$mantisbt->name.'&error_msg='.urlencode($group->getErrorMessage()));

$feedback = _('MantisBT User configuration successfully updated.');
session_redirect('/plugins/mantisbt/?type=user&pluginname='.$mantisbt->name.'&view=adminuser&feedback='.urlencode($feedback));
?>
