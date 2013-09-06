<?php
/**
 * Reporting System
 *
 * Copyright 2003, Tim Perdue - gforge.org
 * Copyright 2004 (c) GForge LLC
 * Copyright 2013, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'reporting/report_utils.php';
require_once $gfcommon.'reporting/Report.class.php';
require_once $gfcommon.'reporting/ReportSetup.class.php';

session_require_global_perm ('forge_stats', 'read') ;

report_header(_('Main Page'));
?>

<div class="info-box">
<h2><?php echo _('Users'); ?></h2>
<ul>
<li><a href="useradded.php?SPAN=1"><?php echo _('Users Added'); ?></a></li>
<li><a href="usercum.php?SPAN=1"><?php echo _('Cumulative Users'); ?></a></li>
<li><a href="useract.php"><?php echo _('User Activity'); ?></a></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Projects'); ?></h2>
<ul>
<li><a href="groupadded.php?SPAN=1"><?php echo _('Projects Added'); ?></a></li>
<li><a href="groupcum.php?SPAN=1"><?php echo _('Cumulative Projects'); ?></a></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Project-specific reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads'); ?></h2>
<ul>
<li><a href="projectact.php"><?php echo _('Project Activity'); ?></a></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Site-wide reports: Tracker, Task Mgr, Forums, Doc Mgr, Downloads'); ?></h2>
<ul>
<li><a href="toolspie.php"><?php echo _('Tool Pie Graphs'); ?></a></li>
<li><a href="siteact.php"><?php echo _('Site-Wide Activity'); ?></a></li>
</ul>
</div>

<div class="info-box">
<h2><?php echo _('Time tracking'); ?></h2>
<ul>
<li><a href="usertime.php"><?php echo _('Individual User Time Report (graph)'); ?></a> <a href="usertime.php?typ=r">(<?php echo _('report'); ?>)</a></li>
<li><a href="projecttime.php"><?php echo _('Individual Project Time Report (graph)'); ?></a> <a href="projecttime.php?typ=r">(<?php echo _('report'); ?>)</a></li>
<li><a href="sitetime.php"><?php echo _('Site-Wide Time Report (graph)'); ?></a> <a href="sitetime.php?typ=r">(<?php echo _('report'); ?>)</a></li>
<li><a href="sitetimebar.php"><?php echo _('Site-Wide Total Hours Graph (graph)'); ?></a> <a href="sitetimebar.php?typ=r">(<?php echo _('report'); ?>)</a></li>
<li><a href="usersummary.php"><?php echo _('User Summary Report'); ?></a></li>
</ul>
</div>

<?php if (forge_check_global_perm ('forge_stats', 'admin')) { ?>

<div class="info-box">
<h2><?php echo _('Administrative'); ?></h2>
<ul>
<li><a href="rebuild.php"><?php echo _('Initialize / Rebuild Reporting Tables'); ?></a></li>
<li><a href="timecategory.php"><?php echo _('Manage Time Tracker Categories'); ?></a></li>
</ul>
</div>
<?php
}

plugin_hook ("reporting_reference", array());
report_footer();
