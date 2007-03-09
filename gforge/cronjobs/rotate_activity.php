#! /usr/bin/php4 -f
<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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

require ('squal_pre.php');
require ('common/include/cron_utils.php');

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
