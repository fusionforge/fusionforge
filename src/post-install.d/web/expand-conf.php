#!/usr/bin/php -f
<?php
/**
 * Substitute {section/var} variables in Apache templates
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

// Don't try to connect to the DB, just reading config files
putenv('FUSIONFORGE_NO_DB=true');

require (dirname(__FILE__).'/../../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';

if (count($argv) != 3) {
  echo "Usage: $argv[0] template.inc destination.inc\n";
  echo "(Note: you can use php://stdin and php://stdout)\n";
  exit(1);
}

// forge_get_config('source_path').'/etc/httpd.conf.d/'.
$lines = file($argv[1]);
if ($lines === FALSE) {
  echo "$argv[0]: cannot open $argv[1]\n";
  exit(1);
}

$out = fopen($argv[2], 'w');
if ($out === FALSE) {
  echo "$argv[0]: cannot write to $argv[2]\n";
  exit(1);
}


// Replace the variable with the configuration value
foreach($lines as $line) {
  $line = preg_replace_callback(
    ',{([a-z_]*)/([a-z_]*)},',
    function ($matches) {
      return forge_get_config($matches[2], $matches[1]);
    },
    $line);
  fwrite($out, $line);
}
fclose($out);
