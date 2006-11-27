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
$fichout = "/tmp/gfo".rand(10000, 99999).".tmp";
$cmd = "/usr/bin/perl /usr/bin/ooo2txt.pl $fichin > $fichout";
$res = shell_exec($cmd);


$rep = parser_text($fichout);
// envoi du résultat sur stdout
echo "$rep";
// efface les fichiers témporaires
unlink ($fichout);

?>