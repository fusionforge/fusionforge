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

// Support for short aliases

if (!$words) {
	$words = $q;
}

if (!$type_of_search) {
	$type_of_search = $type;
}
if (!$type_of_search) {
	$type_of_search = $t;
}
if (!$type_of_search) {
	$type_of_search = SEARCH__TYPE_IS_SOFTWARE;
}

require_once('pre.php');
require_once('common/include/escapingUtils.php');
require_once('www/tracker/include/ArtifactTypeHtml.class');

$offset = getIntFromGet('offset');

if($type_of_search == SEARCH__TYPE_IS_SOFTWARE) {
	if($rss) {
		require('include/ProjectRssSearchRenderer.class');
		$searchQuery = new ProjectRssSearchRenderer($words, $offset, $exact);
	} else {
		require('include/ProjectHtmlSearchRenderer.class');
		$searchQuery = new ProjectHtmlSearchRenderer($words, $offset, $exact);
	}
} elseif ($type_of_search == SEARCH__TYPE_IS_PEOPLE) {
	
	require('include/PeopleHtmlSearchRenderer.class');
	$searchQuery = new PeopleHtmlSearchRenderer($words, $offset, $exact);
	
} elseif ($type_of_search == SEARCH__TYPE_IS_FORUM && $forum_id && $group_id) {
	
	require('include/ForumHtmlSearchRenderer.class');
	$searchQuery = new ForumHtmlSearchRenderer($words, $offset, $exact, $group_id, $forum_id);

} elseif ($type_of_search == SEARCH__TYPE_IS_ARTIFACT && $atid && $group_id) {
	
	require('include/ArtifactHtmlSearchRenderer.class');
	$searchQuery = new ArtifactHtmlSearchRenderer($words, $offset, $exact, $group_id, $atid);
	
} elseif ($type_of_search == SEARCH__TYPE_IS_SKILL) {
	
	require('include/SkillHtmlSearchRenderer.class');
	$searchQuery = new SkillHtmlSearchRenderer($words, $offset, $exact);
	
}

if(isset($searchQuery)) {
	$searchQuery->flush();
} else {
	$HTML->header(array('title'=>$Language->getText('search', 'title'), 'pagename'=>'search'));
	
	echo '<h1>'.$Language->getText('search', 'error_invalid_search').'</h1>';
	
	$HTML->footer(array());
	exit();
}

?>