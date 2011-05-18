<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportSetup.class.php';

session_require_global_perm ('forge_stats', 'read') ;

report_header('Main Page');

?>
<h2><?php echo _('Users'); ?></h2>
<p>
<a href="useradded.php?SPAN=1"><?php echo _('Users Added Weekly (graph)'); ?></a><br />
<a href="usercum.php?SPAN=1"><?php echo _('Cumulative Users Weekly (graph)'); ?></a><br />
<a href="useract.php"><?php echo _('Activity (graph)'); ?></a><br />
</p>
<h2><?php echo _('Projects'); ?></h2>
<p>
<a href="groupadded.php?SPAN=1"><?php echo _('Projects Added Weekly (graph)'); ?></a><br />
<a href="groupcum.php?SPAN=1"><?php echo _('Cumulative Projects Weekly (graph)'); ?></a><br />
<?php echo _('Project-specific reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads'); ?><br />
<a href="projectact.php"><?php echo _('Activity (graph)'); ?></a><br />
</p>
<h2><?php echo _('Site-Wide'); ?></h2>
<p>
<?php echo _('Site-wide reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads'); ?><br />
<a href="toolspie.php"><?php echo _('Pie (graph)'); ?></a><br />
<a href="siteact.php"><?php echo _('Line (graph)'); ?></a><br />
</p>
<h2><?php echo _('Time-Tracking'); ?></h2>
<p>
<a href="usertime.php"><?php echo _('Individual User Time Report (graph)'); ?></a> <a href="usertime.php?typ=r">(<?php echo _('report'); ?>)</a><br />
<a href="projecttime.php"><?php echo _('Individual Project Time Report (graph)'); ?></a> <a href="projecttime.php?typ=r">(<?php echo _('report'); ?>)</a><br />
<a href="sitetime.php"><?php echo _('Site-Wide Time Report (graph)'); ?></a> <a href="sitetime.php?typ=r">(<?php echo _('report'); ?>)</a><br />
<a href="sitetimebar.php"><?php echo _('Site-Wide Total Hours Graph (graph)'); ?></a> <a href="sitetimebar.php?typ=r">(<?php echo _('report'); ?>)</a><br />
<a href="usersummary.php"><?php echo _('Site-Wide Task &amp; Hours (report)'); ?></a><br />
</p>

<?php if (forge_check_global_perm ('forge_stats', 'admin')) { ?>
<h2><?php echo _('Administrative'); ?></h2>
<p>
<a href="rebuild.php"><?php echo _('Initialize / Rebuild Reporting Tables'); ?></a><br />
<a href="timecategory.php"><?php echo _('Manage Time Tracker Categories'); ?></a><br />
</p>
<?php
}

plugin_hook ("reporting_reference", array());
report_footer();

?>
