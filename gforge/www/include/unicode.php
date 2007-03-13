<?php
/**
 * GForge Project 
 *
 * 
 * fabio bertagnin fbertagnin@mail.transiciel.com
 *
 * @version   $Id: 18_special_chars_in_graphics.dpatch,v 1.1 2006/01/13 09:49:16 fabio Exp $
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
