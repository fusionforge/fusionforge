<?php
/**
 * MantisBT plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2014, Franck Villaume - TrivialDev
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

global $mantisbt; // the mantisbt object
global $user;

$confArr = array();
$confArr['mantisbt_user'] = getStringFromRequest('mantisbt_user');
$confArr['mantisbt_password'] = getStringFromRequest('mantisbt_password');

if (!$mantisbt->updateUserConf($confArr)) {
	$error_msg = $mantisbt->getErrorMessage();
	session_redirect('/plugins/'.$mantisbt->name.'/?type=user');
}

$feedback = _('MantisBT User configuration successfully updated.');
session_redirect('/plugins/'.$mantisbt->name.'/?type=user&view=adminuser');
