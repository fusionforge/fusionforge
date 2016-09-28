<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * Copyright 2011-2013,2015 Franck Villaume - TrivialDev
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
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

class Parsedata {

	var $parsers;

	var $p_path;

	function __construct() {
		$this->p_path = dirname(__FILE__).'/engine/';
		$this->parsers = $this->get_parser_list($this->p_path);
	}

	/**
	 * get_parse_data - analyse content and metadata
	 *
	 * @param	string	$data		the path of the file to analyse
	 * @param	string	$filetype	type of the file to analyse
	 * @return	string	the analysed content
	 */
	function get_parse_data($data, $filetype) {
		$parser = '';
		$rep = '';
		if (array_key_exists($filetype, $this->parsers)) {
			// parse data if good parser exists
			$parser = $this->p_path.$this->parsers[$filetype];
			$cmd = "php -f $parser $data";
			$rep = shell_exec($cmd);
		}
		// dont need to unlink the filename because parser_text already remove it
		return preg_replace("/\n/", " ", "$rep");
	}

	/**
	 * get_parser_list - get the list of available parsers
	 *
	 * @param	string	$parser_path	the path where are located the parsers
	 * @return	array	available parsers
	 */
	function get_parser_list($parser_path) {
		$file = $parser_path.'parser_list.txt';
		$rep = array();
		$arrayLines = file($file, FILE_SKIP_EMPTY_LINES);
		if (is_array($arrayLines) && count($arrayLines)) {
			foreach ($arrayLines as $a) {
				if (trim($a) != '' && substr($a, 0,1) != '#') {
					$a2 = explode ('|', $a);
					$rep[$a2[0]] = trim($a2[1]);
				}
			}
		}
		return $rep;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
