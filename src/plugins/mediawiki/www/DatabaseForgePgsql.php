<?php
/*
 * Copyright (C) 2010 Roland Mas, Olaf Lenz
 * Copyright (C) 2011 France Telecom
 * Copyright 2022, Franck Villaume - TrivialDev
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $wgVersion, $IP, $gfplugins;
$wgVersionArr = explode('.', $wgVersion);
if ($wgVersionArr[0] == 1 && $wgVersionArr[1] >= 28) {
	require_once $IP.'/includes/libs/rdbms/database/DatabasePostgres.php';
} else {
	require_once $IP.'/includes/db/DatabasePostgres.php';
}

if ($wgVersionArr[0] == 1 && $wgVersionArr[1] >= 32) {
	require_once $gfplugins.'mediawiki/www/DatabaseForgePgsql-0132.php';
} else {
	require_once $gfplugins.'mediawiki/www/DatabaseForgePgsql-0131.php';
}
