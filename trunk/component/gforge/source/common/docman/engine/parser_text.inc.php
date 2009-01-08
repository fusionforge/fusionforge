<?php
/**
 * GForge Doc Search Utilities
 *
 * 
 * Fabio Bertagnin November 2005
 *
 */

function parser_text($fichin)
{
	$tstart = microtime_float();
	if (!is_file($fichin)) return "";
	$fp = fopen ($fichin, "r");
	$buff = fread ($fp, filesize($fichin));
	// tout en minuscules
	$buff = mb_strtolower($buff);
	// élimination d'éventuels caractères unicode encore présents
	$buff = mb_convert_encoding ($buff, "ascii");
	// élimination caractères avec accents 
	// et caractères spéciaux
	$buff = suppression_diacritics($buff);
	// tous les mots dans un tableau
	$a = explode(" ", $buff);
	//sort($a);
	// élimination des doublons
	$a = array_unique($a);
	// envoi du résultat sur stdout
	$rep = print_list($a);
	return $rep;
}

function print_list ($list)
{
	$rep = "";
	foreach ($list as $el)
	{
		if (strlen($el) > 1) $rep .= "$el ";
	}
	return $rep;
}

function suppression_diacritics($text)
{
	$b = $text;
	$b = strtr($b, "éêèëàâäîïùûüôöç", "eeeeaaaiiuuuooc");
	$b = strtr($b, "\t\r\n?.*'\":;,#![]()", "                 ");
	return $b;
}

function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}


function print_debug ($text)
{
	echo "$text <br />\n";
	ob_flush();
}

?>
