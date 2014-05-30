<?php

/**
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

global $gfplugins;
$required = $gfplugins.'importexport/include/importexportPlugin.class.php';

// TODO: Fix the require issue.
// Although file exists, it isn't readable(!?).
// Tried copying the file, linking it, even adding the plugin in the DB(!?).
// relative - absolute paths..same behaviour I am missing smthng here...
if (file_exists($required) && is_readable($required)) {
	require_once $required;
}
else {
	return;
}

$importexportPluginObject = new importexportPlugin ;

register_plugin ($importexportPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
