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

require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/ReportSetup.class');

session_require( array('group'=>$sys_stats_group) );

echo report_header('Main Page');

?>
<h3>Users</h3>
<p>
<a href="useradded.php?SPAN=1">Users Added Weekly (graph)</a><br />
<a href="usercum.php?SPAN=1">Cumulative Users Weekly (graph)</a><br />
<a href="useract.php">Activity (graph)</a><br />
<p>
<h3>Projects</h3>
<p>
Project-specific reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads<br />
<a href="projectact.php">Activity (graph)</a><br />
<p>
<h3>Site-Wide</h3>
<p>
Site-wide reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads<br />
<a href="toolspie.php">Pie (graph)</a><br />
<a href="siteact.php">Line (graph)</a><br />
<p>
<h3>Time-Tracking</h3>
<p>
<a href="usertime.php">Individual User Time Report (graph)</a> <a href="usertime.php?typ=r">(report)</a><br />
<a href="projecttime.php">Individual Project Time Report (graph)</a> <a href="projecttime.php?typ=r">(report)</a><br />
<a href="sitetime.php">Site-Wide Time Report (graph)</a> <a href="sitetime.php?typ=r">(report)</a><br />
<a href="sitetimebar.php">Site-Wide Total Hours Graph (graph)</a> <a href="sitetimebar.php?typ=r">(report)</a><br />
<a href="usersummary.php">Site-Wide Task &amp; Hours (report)</a><br />
<p>
<h3>Administrative</h3>
<p>
<a href="rebuild.php">Initialize / Rebuild Reporting Tables</a><br />
<a href="timecategory.php">Manage Time Tracker Categories</a><br />
<a href="http://gforge.org/tracker/?atid=116&group_id=7&func=browse">File Support Request</a><br />
<a href="http://gforge.org/tracker/?atid=118&group_id=7&func=browse">File Feature Request</a><br />
<p>
<?php

echo report_footer();

?>
