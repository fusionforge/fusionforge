#!/usr/bin/php -f
<?php
/**
 * Copyright 2012, Thorsten Glaser
 * Copyright 2012, Roland Mas
 * Copyright 2021, Franck Villaume - TrivialDev
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

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon."include/pre.php";
$admins = RBACEngine::getInstance()->getUsersByAllowedAction("forge_admin", -1);
$anames = array();
foreach ($admins as $admin) {
	$anames[] = $admin->getUnixName();
}
sort($anames); echo join(" ", $anames) . "\n";
