<?php
/**
 * GForge Project 
 *
 * fabio bertagnin fbertagnin@mail.transiciel.com
 *
 */

if (!defined('UNICODE.PHP'))
{
	define ('UNICODE.PHP', '1');
	function convert_unicode ($text)
	{
		$rep = $text;
		//$rep = mb_convert_encoding ($rep, "ascii", "UTF-8");
		$rep = utf8_decode($rep);
		return $rep;
	}
	
}
?>
