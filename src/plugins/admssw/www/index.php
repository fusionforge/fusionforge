<?php
/**
 * admssw plugin main script
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

$HTML->header(array('title'=>'ADMS.SW','pagename'=>'admssw'));

echo '<p>'._('Preview of RDF meta-data about the forge, conforming to the ADMS.SW specifications').'</p>';

echo '<ul>';
echo '<li>'._('Public projects :');

	echo '<ul>';
	echo '<li>'.util_make_link('/plugins/'.$pluginname.'/projectsturtle.php', _('short index')).', '. 
	_('or '). util_make_link('/plugins/'.$pluginname.'/full.php', _('full dump')) . _(' (as Turtle)').'</li>';
	//echo '<li>'.util_make_link('/plugins/'.$pluginname.'/projectsrdfxml.php', _('as RDF+XML')).'</li>';
	echo '</ul>';

echo '</li>';

echo '<li>'._('Trove categories :');

echo '<ul>';
echo '<li>'.util_make_link('/plugins/'.$pluginname.'/trove.php', _('as Turtle')).'</li>';
echo '</ul>';

echo '</li>';

echo '</ul>';

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>