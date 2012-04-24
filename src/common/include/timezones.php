<?php
/*-
 * Import system timezones into PHP, for FusionForge
 *
 * Copyright © 2012
 *	Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * All rights reserved.
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

unset($TZs);
if (file_exists("/usr/share/zoneinfo/UTC")) {
	/* avoid making $TZs a reference, which ls() returns */
	foreach (ls("/usr/share/zoneinfo") as $j) {
		if (!in_array($j, array(
			'Factory',
			'iso3166.tab',
			'localtime',
			'posix',
			'posixrules',
			'right',
			'zone.tab',
		    ))) {
			/* not masked or non-timezone */
			$TZs[] = $j;
		}
	}
	$j = true;
	while ($j) {
		sort($TZs);
		$i = count($TZs);
		$j = false;
		while ($i-- > 0) {
			if (is_dir("/usr/share/zoneinfo/" . $TZs[$i])) {
				foreach (ls("/usr/share/zoneinfo/" . $TZs[$i])
				    as $j) {
					$TZs[] = $TZs[$i] . "/" . $j;
				}
				unset($TZs[$i]);
				$j = true;
				break;
			}
		}
	}
	sort($TZs);
}

if (!isset($TZs) || !$TZs) {
	/* fall back to SourceForge compiled-in list */
	include dirname(__FILE__).'/timezones-sf.php';
}
