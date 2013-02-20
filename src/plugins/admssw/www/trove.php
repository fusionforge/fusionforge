<?php
/**
 * admssw plugin script which displays an HTML preview of the SKOS export of the trove categories, as Turtle
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

/* FIXME
* Parameters:
*   $gwords     = target words to search
*   $gexact     = 1 for search ing all words (AND), 0 - for any word (OR)
*   $otherfreeknowledge = 1 for search in Free/Libre Knowledge Gforge Initiatives
*   $order = "project_title" or "title"    -  criteria for ordering results: if empty or not allowed results are ordered by rank
*
*/

$pluginname = 'admssw';

$HTML->header(array('title'=>_('SKOS meta-data for trove categories'),'pagename'=>'trove'));
$HTML->printSoftwareMapLinks();

$plugin = plugin_get_object ($pluginname);

echo '<p>'. sprintf( _('The following is a preview of the (machine-readable) RDF meta-data which can be obtained at <tt>%1$s</tt> as Turtle'), util_make_url('/softwaremap/trove_list.php')) .'</p>';

echo $plugin->htmlPreviewTroveCatsAsTurtle();

echo _('To access this RDF document, you may use, for instance :<br />');
echo '<tt>$ curl -H "Accept: text/turtle" '. util_make_url('/softwaremap/trove_list.php') .'</tt><br />';

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>