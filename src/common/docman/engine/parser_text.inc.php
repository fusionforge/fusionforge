<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
	if (!is_file($fichin))
		return "";

	if (filesize($fichin) == 0)
		return "";

	$handle = fopen($fichin, "r");
	$buff = fread($handle, filesize($fichin));

	// tout en minuscules
	if (function_exists('mb_strtolower')) {
		$buff = mb_strtolower($buff);
	} else {
		$buff = strtolower($buff);
	}

	// élimination d'éventuels caractères unicode encore présents
	if (function_exists('mb_convert_encoding')) {
		$buff = mb_convert_encoding($buff, "ascii");
	}

	// élimination caractères avec accents
	// et caractères spéciaux
	$buff = suppression_diacritics($buff);
	// tous les mots dans un tableau
	$words = explode(" ", $buff);
	// élimination des doublons
	$words = array_unique($words);
	// envoi du résultat sur stdout
	$rep = print_list($words);
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
	$text = iconv('UTF-8', 'US-ASCII//TRANSLIT', $text) ;
	$text = strtr($text, "\t\r\n?.*'\":;,#![]()", "                 ");
	return $text;
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
