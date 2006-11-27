#! /usr/bin/php4 -f
<?php
/**
 * GForge Doc Search Utilities
 *
 * 
 * Fabio Bertagnin November 2005
 *
 */
 
require_once("parser_text.inc.php");

if ($argc != 2)
{
	echo "Usage : parser_oo.php <filename>\n";
	exit (1);
}
$fichin = $argv[1];
if (!is_file($fichin)) exit (1);

$rep = parser_text($fichin);
// envoi du résultat sur stdout
echo "$rep";
// efface le fichier source
unlink ($fichin);

?>