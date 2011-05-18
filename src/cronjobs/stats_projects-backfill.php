#! /usr/bin/php
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once $gfcommon.'include/pre.php';
include_once('cronjobs/stats_projects.inc');

$i=0;

while($i < 515) {

	$i++;

	$how_far_back=(86400 * $i);

	$time=time()-$how_far_back;

	$year=date('Y',$time);
	$month=date('m',$time);
	$day=date('d',$time);

	$datetime="$year$month$day";

	if ($datetime < 19991117) {
		$i=1000;
		echo 'done';
		break;
	}

	project_stats_day($year,$month,$day);
}

?>
