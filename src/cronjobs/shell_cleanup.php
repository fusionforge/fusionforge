#!/usr/bin/php
<?php
/*-
 * Cleanup cronjob for the FusionForge shell module
 *
 * Copyright © 2014
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
 *-
 * Short description of the module or comments or whatever
 */

require dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

$res = db_query_params('SELECT * FROM users
	WHERE expire_date != 0
	    AND expire_date < $1',
    array(time()));

while ($arr = db_fetch_array($res)) {
	$u = new GFUser($arr['user_id'], $arr);
	if (!$u || !is_object($u)) {
		echo "E: could not get user " . $arr['user_id'] . "\n";
		continue;
	} else
	if ($u->isError())
		echo "W: user " . $arr['user_id'] . " has error\n";
	if (!$u->setStatus('S'))
		echo "E: could not suspend user " . $arr['user_id'] .
		    " (" . $u->getUnixName() . "): " .
		    $u->getErrorMessage() . "\n";
	if (!$u->setUnixStatus('S'))
		echo "E: could not suspend account " . $arr['user_id'] .
		    " (" . $u->getUnixName() . "): " .
		    $u->getErrorMessage() . "\n";
}

/* sync nss-pgsql */
cron_reload_nscd();
