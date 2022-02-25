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

require_once $gfwww.'search/include/renderers/SearchRenderer.class.php';

class RssSearchRenderer extends SearchRenderer {

	/**
	 * callback function name used during the RSS export
	 *
	 * @var string $callbackFunction
	 */
	var $callbackFunction = '';

	/**
	 * flush - flush the RSS output
	 */
	function flush() {
		$searchQuery =& $this->searchQuery;

		header('Content-Type: text/plain');

		if($searchQuery->isError() || $this->isError()) {
			echo '<channel></channel>';
		} else {
			include_once $GLOBALS['gfwww'].'export/rss_utils.inc';

			rss_dump_project_result_array(
				$searchQuery->getData(),
				'FusionForge Search Results',
				'FusionForge Search Results for "'.$this->query['words'].'"',
				$this->callbackFunction
			);
		}
		exit();
	}

}
