#! /usr/bin/php4 -f
<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');
require ('common/reporting/ReportSetup.class.php');

$err='';

$report = new ReportSetup();

if ($report->isError()) {
	$err .= $report->getErrorMessage();
}

db_begin();

if (!$report->dailyData()) {
	$err .= $report->getErrorMessage();
}

db_commit();

$err .= "Done: ".date('Ymd H:i').' - '.db_error();
cron_entry(20,$err);

?>
