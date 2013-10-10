<?php
/**
 * admssw plugin script which displays an HTML preview of the projects list as Turtle
*
* This file is (c) Copyright 2012 by Olivier BERGER, Institut Mines-Telecom
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

// Dumps an HTML preview of the Turtle RDF document for an index of public projects

// The script will paginate the contents (with the same page size than in the softwaremap/trove)
// redirecting if necessary to ?page=1
// This can be overriden with ?allatonce

// The RDF version is available at /projects/.

// This script is the counterpart, with only un index of projects, of an other lists of projects
// with full details which can be accessed in /plugins/admssw/full.php



$pluginname = 'admssw';

$HTML->header(array('title'=>_('ADMS.SW meta-data index of public projects'),'pagename'=>'admssw'));
$HTML->printSoftwareMapLinks();

$plugin = plugin_get_object ($pluginname);

$documenturi = util_make_url('/projects/');
$scripturl = util_make_url('/plugins/'. $plugin->name .'/projectsturtle.php');

// page length
$pl = $plugin->getPagingLimit();

$projectsnum = $plugin->getProjectListSize();

$p = $plugin->process_paging_params_or_redirect($projectsnum, $pl);

// We don't want full details about the projects, just an index
$detailed = false;
print $plugin->getProjectsListDisplay($documenturi, 'text/html', $p, $pl, $detailed, $scripturl);

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
