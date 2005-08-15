<?php

/**
 * GForge Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
 *
 * http://gforge.org
 *
 * @version $Id$
 */


require_once('pre.php');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once ('www/search/include/SearchManager.class');

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');
$forum_id = getIntFromRequest('forum_id');
$group_project_id = getIntFromRequest('group_project_id');

$words = getStringFromRequest('words');
$type_of_search = getStringFromRequest('type_of_search');
$exact = getStringFromRequest('exact');

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
	$HTML->header(array('title'=>$Language->getText('search', 'title')));
	
	echo '<h1>'.$Language->getText('search', 'error_invalid_search').'</h1>';
	
	$HTML->footer(array());
	exit();
}

?>
