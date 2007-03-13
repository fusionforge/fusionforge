<?php

/**
 * GForge Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
 *
 * http://gforge.org
 *
 * @version $Id$
 */

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/include/escapingUtils.php');
require_once('include/renderers/AdvancedSearchHtmlSearchRenderer.class');

$group_id = getIntFromRequest('group_id');
$offset = getIntFromRequest('offset');
$words = getStringFromRequest('words');
$mode = getStringFromRequest('mode', SEARCH__MODE_AND);

if ($mode == SEARCH__MODE_AND) {
	$exact = true;
} else {
	$exact = false;
}

if (!getStringFromRequest('search')) { 
	$searchQuery = new AdvancedSearchHtmlSearchRenderer($words, $offset, true, $group_id);
	//just display the header and footer if search is not set
	$searchQuery->writeHeader();
	$searchQuery->writeFooter();
} else {
	if ($mode == SEARCH__MODE_AND) {
		$exact = true;
	} else {
		$exact = false;
	}
	$searchQuery = new AdvancedSearchHtmlSearchRenderer($words, $offset, $exact, $group_id);
	$searchQuery->flush();
}

?>
