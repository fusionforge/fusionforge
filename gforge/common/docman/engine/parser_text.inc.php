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
	// �limination d'�ventuels caract�res unicode encore pr�sents
	$buff = mb_convert_encoding ($buff, "ascii");
	// �limination caract�res avec accents 
	// et caract�res sp�ciaux
	$buff = suppression_diacritics($buff);
	// tous les mots dans un tableau
	$a = explode(" ", $buff);
	//sort($a);
	// �limination des doublons
	$a = array_unique($a);
	// envoi du r�sultat sur stdout
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
	$b = strtr($b, "���������������", "eeeeaaaiiuuuooc");
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
