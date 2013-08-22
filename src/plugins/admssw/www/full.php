<?php
/**
 * admssw plugin script which produces a full list of public projects with all their details
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

// Dumps an HTML preview or a full RDF document for public projects, with full details

// The script will paginate the contents (with the same page size than in the softwaremap/trove)
// redirecting if necessary to ?page=1
// This can be overriden with ?allatonce

// This script is the counterpart, with full details, of other lists of projects 
// which contain only an index of projects, like /projects/ (for RDF harvested by machines)
// or its HTML Turtle preview in /plugins/admssw/projectsturtle.php

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

$pluginname = 'admssw';

$script = 'admssw_full';
$default_content_type = 'text/html';

// check for alternate representations (RDF content-types)
$content_type = util_negociate_alternate_content_types($script, $default_content_type);

$plugin = plugin_get_object($pluginname);

$documenturi = util_make_url('/plugins/'.$pluginname.'/full.php');

// page length
$pl = $plugin->getPagingLimit();

$projectsnum = $plugin->getProjectListSize();

$p = $plugin->process_paging_params_or_redirect($projectsnum, $pl);

if ($content_type != 'text/html') {
	header('Content-type: '. $content_type);
}
else {
	$HTML->header(array('title'=>_('Full ADMS.SW export'),'pagename'=>'admssw_full'));
	$HTML->printSoftwareMapLinks();
}

// We want full details of the projects
$detailed = true;
print $plugin->getProjectsListDisplay($documenturi, $content_type, $p, $pl, $detailed);

if ($content_type == 'text/html') {
	$HTML->footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>