<?php
/**
 * Search Engine
 *
 * Copyright 2004 (c) Dominik Haas, GForge Team
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
require_once $gfcommon.'include/escapingUtils.php';
require_once $gfwww.'search/include/renderers/AdvancedSearchHtmlSearchRenderer.class.php';

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
