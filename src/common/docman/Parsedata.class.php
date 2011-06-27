<?php
/**
 * FusionForge document manager
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
	/**
	 * Constructor.
	 *
	 * @param	string
	 * @return	boolean	true
	 */
	var $parsers;
	 var $p_path;

	function Parsedata() {
		$this->p_path = dirname(__FILE__).'/engine/';
		$this->parsers = get_parser_list ($this->p_path);
		return true;
	}

	function get_parse_data($data, $title, $description, $filetype) {
		$parser = "";
		$rep = "";
		$data1 = $data;
		if (array_key_exists($filetype, $this->parsers)) {
			// parse data if good parser exists
			$parser = $this->p_path.$this->parsers[$filetype];
			$filename = tempnam("/tmp/","tmp");
			$fp = fopen ($filename, "w");
			fwrite ($fp, $data1);
			fclose ($fp);
			$cmd = "php -f $parser $filename";
			$rep = shell_exec ($cmd);
			if ( file_exists ($filename ) ) {
				unlink($filename);
			}
		}
		// always parse titre and description
		$data2 = utf8_decode("$title $description");
		// temporary file for treatement
		$filename = tempnam("/tmp", "tmp");
		$fp = fopen ($filename, "w");
		fwrite ($fp, $data2);
		fclose ($fp);
		$cmd = $this->p_path.$this->parsers["text/plain"];
		$cmd = "php -f $cmd $filename";
		$rep1 = shell_exec ($cmd);
		// dont need to unlink the filename because parser_text already remove it
		return preg_replace("/\n/", " ", "$rep $rep1");
	}

	function print_debug($text) {
		echo "$text \n";
		ob_flush();
	}
}

function get_parser_list($parser_path) {
	$file = $parser_path."parser_list.txt";
	$rep = array();
	$fp = fopen ($file, "r");
	if ($fp) {
		$buff = fread($fp, 2048);
		$a1 = explode ("\n", $buff);
		foreach ($a1 as $a) {
			if (trim($a) != "" && substr($a, 0,1) != "#") {
				$a2 = explode ("|", $a);
				$rep[$a2[0]] = $a2[1];
			}
		}
	}
	return $rep;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
