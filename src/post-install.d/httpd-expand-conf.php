#!/usr/bin/php -f
<?php
// Substitute {section/var} variables in Apache templates

// Don't try to connect to the DB, just reading config files
putenv('FUSIONFORGE_NO_DB=true');

require (dirname(__FILE__).'/../common/include/env.inc.php');
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
