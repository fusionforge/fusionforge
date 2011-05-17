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

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

$today_formatted=date('Ymd',(time()-(30*60*60*24)));
$sql='';

db_begin();

$rel = db_query_params ('DELETE FROM activity_log WHERE day < $1',
			array ($today_formatted));
$err .= db_error();

db_commit();

$err .= " Done: ".date('Ymd H:i').' - '.db_error();

cron_entry(10,$err);

?>
