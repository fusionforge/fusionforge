#! /usr/bin/php
<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

require dirname(__FILE__).'/../../include/env.inc.php';

require_once $gfcommon.'docman/engine/parser_text.inc.php';

if ($argc != 2) {
	echo "Usage : parser_oo.php <filename>\n";
	exit(1);
}

$fichin = $argv[1];
if (!is_file($fichin))
	exit(1);

$zip = new ZipArchive;
if ($zip->open($fichin) === TRUE) {
	$output_dir = tempnam(forge_get_config('data_path'), "tmp");
	mkdir($output_dir);
	$zip->extractTo($output_dir, array('content.xml'));
	$zip->close();
} else {
	exit(2);
}

// transformer le context.xml en fichier txt
$regexp_oo = "sed -e 's/<[^>]*>//g;s/&lt;/</g;s/&gt;/>/g;s/&apos;/'\"'\"'/g;s/&quot;/\"/g;s/&amp;/\&/g'";

$cmd = $regexp_oo." ".$output_dir."/content.xml > ".$output_dir."/content.xml.txt";

$res = shell_exec($cmd);
echo parser_text($output_dir.'/content.xml.txt');

unlink($output_dir.'/content.xml');
unlink($output_dir.'/content.xml.txt');
rmdir($output_dir);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
