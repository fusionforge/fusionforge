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

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/reporting/report_utils.php');
require_once('common/reporting/Report.class');
require_once('common/reporting/ReportSetup.class');

session_require( array('group'=>$sys_stats_group) );

echo report_header('Main Page');

?>
<h3><?php echo $Language->getText('reporting','users'); ?></h3>
<p>
<a href="useradded.php?SPAN=1"><?php echo $Language->getText('reporting','users_added_weekly'); ?></a><br />
<a href="usercum.php?SPAN=1"><?php echo $Language->getText('reporting','cumulative_users'); ?></a><br />
<a href="useract.php"><?php echo $Language->getText('reporting','user_activity'); ?></a><br />
<p>
<h3><?php echo $Language->getText('reporting','projects'); ?></h3>
<p>
<a href="groupadded.php?SPAN=1"><?php echo $Language->getText('reporting','groups_added_weekly'); ?></a><br />
<a href="groupcum.php?SPAN=1"><?php echo $Language->getText('reporting','cumulative_groups'); ?></a><br />
<?php echo $Language->getText('reporting','project_specific'); ?><br />
<a href="projectact.php"><?php echo $Language->getText('reporting','project_activity'); ?></a><br />
<p>
<h3><?php echo $Language->getText('reporting','site_wide'); ?></h3>
<p>
<?php echo $Language->getText('reporting','site_wide_reports'); ?><br />
<a href="toolspie.php"><?php echo $Language->getText('reporting','site_wide_pie'); ?></a><br />
<a href="siteact.php"><?php echo $Language->getText('reporting','site_wide_line'); ?></a><br />
<p>
<h3><?php echo $Language->getText('reporting','time_tracking'); ?></h3>
<p>
<a href="usertime.php"><?php echo $Language->getText('reporting','time_tracking_usertime'); ?></a> <a href="usertime.php?typ=r">(<?php echo $Language->getText('reporting','report'); ?>)</a><br />
<a href="projecttime.php"><?php echo $Language->getText('reporting','time_tracking_projecttime'); ?></a> <a href="projecttime.php?typ=r">(<?php echo $Language->getText('reporting','report'); ?>)</a><br />
<a href="sitetime.php"><?php echo $Language->getText('reporting','time_tracking_sitetime'); ?></a> <a href="sitetime.php?typ=r">(<?php echo $Language->getText('reporting','report'); ?>)</a><br />
<a href="sitetimebar.php"><?php echo $Language->getText('reporting','time_tracking_sitetimebar'); ?></a> <a href="sitetimebar.php?typ=r">(<?php echo $Language->getText('reporting','report'); ?>)</a><br />
<a href="usersummary.php"><?php echo $Language->getText('reporting','time_tracking_usersummary'); ?></a><br />
<p>
<h3><?php echo $Language->getText('reporting','administrative'); ?></h3>
<p>
<a href="rebuild.php"><?php echo $Language->getText('reporting','initialize'); ?></a><br />
<a href="timecategory.php"><?php echo $Language->getText('reporting','manage'); ?></a><br />

<p>
<?php

echo report_footer();

?>
