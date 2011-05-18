<?php
/**
 * Canned Responses functions library.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

/**
 * add_canned_response() - Add a new canned response
 *
 * @param		string	Canned response title
 * @param		string	Canned response text
 */
function add_canned_response($title, $text)
{
		global $error_msg;
		if( !db_query_params ('INSERT INTO canned_responses (response_title, response_text) VALUES($1,$2)',
			array($title,
				$text)) ) {
			$error_msg .= db_error();
		}
}

/**
 * get_canned_responses() - Get an HTML select-box of canned responses
 */
function get_canned_responses()
{
	global $canned_response_res;
	if (!$canned_response_res) {
		$canned_response_res = db_query_params ('SELECT response_id, response_title FROM canned_responses',
			array());
	}
	return html_build_select_box($canned_response_res, 'response_id');
}

?>
