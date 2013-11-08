<?php
/**
 * FusionForge document manager
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * Copyright 2011-2013, Franck Villaume - TrivialDev
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

	/**
	 * Constructor.
	 *
	 * @return	\Parsedata
	 */
	function __construct() {
		$this->p_path = dirname(__FILE__).'/engine/';
		$this->parsers = $this->get_parser_list($this->p_path);
	}

	/**
	 * get_parse_data - analyse content and metadata
	 *
	 * @param	string	$data		the path of the file to be analysed
	 * @param	string	$title		the file title
	 * @param	string	$description	the file description
	 * @param	string	$filetype	the file type
	 * @param	string	$filename	the filename
	 * @return	string	the analysed content
	 */
	function get_parse_data($data, $title, $description, $filetype, $filename) {
		$parser = "";
		$rep = "";
		if (array_key_exists($filetype, $this->parsers)) {
			// parse data if good parser exists
			$parser = $this->p_path.$this->parsers[$filetype];
			$cmd = "php -f $parser $data";
			$rep = shell_exec($cmd);
		}
		// always parse title, description, filename and filetype
		$data1 = utf8_decode("$title $description $filename $filetype");
		// temporary file for treatement
		$filename = tempnam(forge_get_config('data_path'), 'tmp');
		$handle = fopen($filename, 'w');
		fwrite($handle, $data1);
		fclose($handle);
		$cmd = $this->p_path.$this->parsers['text/plain'];
		$cmd = "php -f $cmd $filename";
		$rep1 = shell_exec($cmd);
		if ( file_exists ($filename ) ) {
			unlink($filename);
		}
		// dont need to unlink the filename because parser_text already remove it
		return preg_replace("/\n/", " ", "$rep $rep1");
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
		$handle = fopen($file, 'r');
		if ($handle) {
			$buff = fread($handle, 2048);
			$lines = explode("\n", $buff);
			foreach ($lines as $a) {
				if (trim($a) != "" && substr($a, 0,1) != "#") {
					$a2 = explode ("|", $a);
					$rep[$a2[0]] = $a2[1];
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
