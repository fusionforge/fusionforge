<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once $gfcommon.'include/config.php';

function parser_text($fichin) {
	$tstart = microtime_float();
	if (!is_file($fichin))
		return "";

	$fp = fopen($fichin, "r");
	$buff = fread($fp, filesize($fichin));
	// tout en minuscules
	$buff = mb_strtolower($buff);
	// élimination d'éventuels caractères unicode encore présents
	$buff = mb_convert_encoding($buff, "ascii");
	// élimination caractères avec accents 
	// et caractères spéciaux
	$buff = suppression_diacritics($buff);
	// tous les mots dans un tableau
	$a = explode(" ", $buff);
	// élimination des doublons
	$a = array_unique($a);
	// envoi du résultat sur stdout
	$rep = print_list($a);
	return $rep;
}

function print_list($list) {
	$rep = "";
	foreach ($list as $el) {
		if (strlen($el) > 1) $rep .= "$el ";
	}
	return $rep;
}

function suppression_diacritics($text) {
	$b = $text;
	$b = iconv('UTF-8', 'US-ASCII//TRANSLIT', $b) ;
	$b = strtr($b, "\t\r\n?.*'\":;,#![]()", "                 ");
	return $b;
}

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function print_debug ($text) {
	echo "$text <br />\n";
	ob_flush();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
