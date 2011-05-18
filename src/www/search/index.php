<?php
/**
 * Search Engine
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2004 (c) Guillaume Smet / Open Wide
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


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'search/include/SearchManager.class.php';

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');
$forum_id = getIntFromRequest('forum_id');
$group_project_id = getIntFromRequest('group_project_id');

$words = getStringFromRequest('words');
$type_of_search = getStringFromRequest('type_of_search');
$exact = getStringFromRequest('exact', 1);

// Support for short aliases

if (!$words) {
	$words = getStringFromRequest('q');
}

if (!$type_of_search) {
	$type_of_search = getStringFromRequest('type');
}
if (!$type_of_search) {
	$type_of_search = getStringFromRequest('t');
}
if (!$type_of_search) {
	$type_of_search = SEARCH__TYPE_IS_SOFTWARE;
}
$words=htmlspecialchars($words);

$offset = getIntFromGet('offset');

$searchManager =& getSearchManager();

$parameters = array(
	SEARCH__PARAMETER_GROUP_ID => $group_id,
	SEARCH__PARAMETER_ARTIFACT_ID => $atid,
	SEARCH__PARAMETER_FORUM_ID => $forum_id,
	SEARCH__PARAMETER_GROUP_PROJECT_ID => $group_project_id
);

$searchManager->setParametersValues($parameters);

if (getStringFromRequest('rss')) {
	$outputFormat = SEARCH__OUTPUT_RSS;
} else {
	$outputFormat = SEARCH__OUTPUT_HTML;
}

$renderer = $searchManager->getSearchRenderer($type_of_search, $words, $offset, $exact, $outputFormat);

if ($renderer) {
	$renderer->flush();
} else {
	exit_error(_('Error - Invalid search'));
}

?>
