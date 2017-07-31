#!/usr/bin/php
/**
 * Copyright (C) 2014 Philipp Keidel - EDAG Engineering AG
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

 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

<?php

$script_name=$argv[1];

if ( $script_name == "post-commit" ) {
	$script_path="/usr/share/gforge/plugins/scmhook/library/scmcvs/hooks/committracker/post.php";
} else {
	echo "Invalid script specified: $script_name";
	exit(1);
}

$args = '';
for ($i=2; $i<count($argv); $i++) {
	$args .= escapeshellarg($argv[$i]).' ';
}

$filepath = tempnam("/tmp", "cvswrapper_");
file_put_contents($filepath, file_get_contents("php://stdin"));

$command = "cd /usr/share/gforge/plugins/ && php $script_path \"$filepath\" $args";
$ouptut = array();

$retval = execute($command, $output);
exit($retval);


//////////////////////////////////////////////////
function execute($command, &$output) {
	$retval = 0;
	exec($command, $output, $retval);
	return $retval;
}
