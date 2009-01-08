#! /usr/bin/php5 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id: rotate_activity.php 6506 2008-05-27 20:56:57Z aljeux $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require $gfwww.'include/squal_pre.php';
require $gfcommon.'include/cron_utils.php';

$err='';

$today_formatted=date('Ymd',(time()-(30*60*60*24)));
$sql='';

db_begin();

$sql = "DELETE FROM activity_log WHERE day < $today_formatted";
$err .= $sql;
$rel = db_query($sql);
$err .= db_error();

db_commit();

$err .= " Done: ".date('Ymd H:i').' - '.db_error();

cron_entry(10,$err);

?>
