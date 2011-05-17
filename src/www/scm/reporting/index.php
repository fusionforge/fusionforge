<?php
/**
 * SCM Reporting
 *
 * Copyright 2004-2005 (c) Tim Perdue - GForge LLC
 * http://fusionforge.org
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';    

$group_id = getIntFromRequest("group_id");
scm_header(array('title'=>_('SCM Repository Reporting'),'group'=>$group_id));

?>

<h2>Commits Over Time</h2>
<p>
<img src="commitstime_graph.php?group_id=<?php echo $group_id; ?>" 
     alt="Commits Over Time" />
</p>

<h2>Commits Last 30 Days</h2>
<p>
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=30" 
     alt="Commits Last 30 Days" />
</p>

<h2>Commits Last 90 Days</h2>
<p>
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=90" 
     alt="Commits Last 90 Days" />
</p>

<h2>Commits Last 365 Days</h2>
<p>
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=365" 
     alt="Commits Last 365 Days" />
</p>

<?php

scm_footer(); 

?>
