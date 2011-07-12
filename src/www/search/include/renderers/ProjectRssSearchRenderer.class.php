<?php
/**
 * Search Engine
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2004 (c) Guillaume Smet / Open Wide
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

require_once $gfwww.'search/include/renderers/RssSearchRenderer.class.php';
require_once $gfcommon.'search/ExportProjectSearchQuery.class.php';

/**
 * callback function used during the RSS export
 *
 * @param array $dataRow array containing data for the current row
 * @return string additionnal information added in the RSS document
 */
function rssProjectCallback($dataRow) {
	$result = db_query_params ('SELECT trove_cat.fullpath FROM trove_group_link, trove_cat
WHERE trove_group_link.trove_cat_root=$1
AND trove_group_link.trove_cat_id=trove_cat.trove_cat_id
AND group_id=$2',
				   array (forge_get_config('default_trove_cat'),
					  $dataRow['group_id'])) ;
	$return = '';
	$return .= ' | date registered: '.date('M jS Y', $dataRow['register_time']);
	$return .= ' | category: '.str_replace(' ', '', implode(',', util_result_column_to_array($result)));
	$return .= ' | license: '.$dataRow['license'];

	return $return;
}

class ProjectRssSearchRenderer extends RssSearchRenderer {

	/**
	 * Constructor
	 *
	 * @param string $words words we are searching for
	 * @param int $offset offset
	 * @param boolean $isExact if we want to search for all the words or if only one matching the query is sufficient
	 */
	function ProjectRssSearchRenderer($words, $offset, $isExact) {

		$this->callbackFunction = 'rssProjectCallback';

		$searchQuery = new ExportProjectSearchQuery($words, $offset, $isExact);

		$this->RssSearchRenderer(SEARCH__TYPE_IS_SOFTWARE, $words, $isExact, $searchQuery);
	}
}

?>
