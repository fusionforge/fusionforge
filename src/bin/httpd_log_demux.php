#!/usr/bin/php
<?php
/**
 * httpd log demultiplexer
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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
/**
 * Separate logs for each project homepages.
 * Best used with mod_vhost_alias and a shared CustomLog directive.
 * Not creating log directories because of faked hostnames.
 * Caution: this will be run as root.  Keep it simple.
 */

if (count($argv) < 3) {
	echo "Usage: {$argv[0]} dir_template project_pregexp\n";
	echo "   ex: CustomLog \"||{$argv[0]} /home/logs/%/raw/access.log /([-_a-zA-Z0-9]+)\.yourforge\.tld/\" combined\n";
	exit(1);
}
$dir_template   = $argv[1];
$project_regexp = $argv[2];

$stdin = fopen('php://stdin', 'r') or die("Can't read standard input.");

while(($line = fgets($stdin)) !== false) {
	if (!preg_match($project_regexp, $line, $matches)) {
		error_log("httpd_log_demux: line doesn't match project_pregexp: $line");
	} else {
		$project = $matches[1];
		if (!preg_match('/^[a-z0-9][-a-z0-9_\.]+\z/', $project)) {  // project name, or domain name if DNS alias
			error_log("httpd_log_demux: project name is invalid: '$project'");
		} else {
			$logfile = str_replace('%', $project, $dir_template);
			$f = fopen($logfile, 'a');
			if ($f != null) {
				fwrite($f, $line);
				fclose($f);
			}
		}
	}
}

fclose($stdin);
