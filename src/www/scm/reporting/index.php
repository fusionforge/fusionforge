<?php
/**
 * SCM Reporting
 *
 * Copyright 2004-2005 (c) Tim Perdue - GForge LLC
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginPie();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();

$group_id = getIntFromRequest("group_id");
scm_header(array('title'=>_('SCM Repository Reporting'), 'group'=>$group_id));

echo '<h2>'._('Commits Over Time')."</h2>\n";
commitstime_graph($group_id, 1);


echo '<h2>'._('Commits Last 30 Days')."</h2>\n";
commits_graph($group_id, 30, 2);

echo '<h2>'._('Commits Last 90 Days')."</h2>\n";
commits_graph($group_id, 90, 3);

echo '<h2>'._('Commits Last 365 Days')."</h2>\n";
commits_graph($group_id, 365, 4);

scm_footer();
