#!/usr/bin/php
<?php
/**
 * Synchronously wait until all tasks are done - useful for testsuite
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

putenv('FUSIONFORGE_NO_PLUGINS=true');

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

$first = true;
do {
	if (!$first)
		sleep(1);

	$res = db_query_params("SELECT * FROM systasks WHERE status=$1"
	                       . " ORDER BY systask_id", array('TODO'));
	$nb = db_numrows($res);
	if ($nb > 0) {
		echo "systasks_wait_until_empty.php: pending:\n";
		while ($arr = db_fetch_array($res)) {
			echo "- {$arr['systask_id']} {$arr['plugin_id']} {$arr['systask_type']}"
				. " {$arr['group_id']} {$arr['user_id']}\n";
		}
	}

	$first = false;
} while ($nb > 0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
