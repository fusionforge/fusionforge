<?php
/**
 * GForge SCM Reporting
 *
 * Copyright 2005 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-05-19
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

require_once('../../env.inc.php');
require_once('pre.php');    
require_once('www/scm/include/scm_utils.php');    

$group_id = getIntFromRequest("group_id");
scm_header(array('title'=>_('SCM Repository'),'group'=>$group_id));

?>

<h2>Reporting</h2>
<p>
<h3>Commits Over Time</h3><br />
<img src="commitstime_graph.php?group_id=<?php echo $group_id; ?>" />
</p>
<p>
<h3>Commits Last 30 Days</h3><br />
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=30" />
</p>
<p>
<h3>Commits Last 90 Days</h3><br />
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=90" />
</p>
<p>
<h3>Commits Last 365 Days</h3><br />
<img src="commits_graph.php?group_id=<?php echo $group_id; ?>&amp;days=365" />
</p>

<?php

scm_footer(); 

?>
