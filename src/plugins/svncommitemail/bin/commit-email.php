#! /usr/bin/php
<?php

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';

# Begin configuration
$svnlook = '/usr/bin/svnlook';
#$commit_email_pl = '/usr/share/subversion/hook-scripts/commit-email.pl';
$commit_email_pl = dirname(__FILE__).'/commit-email.pl';
# End configuration

# Set sendmail path to next script from our configuration.
putenv("SENDMAIL=".forge_get_config('sendmail_path'));

if(!file_exists($svnlook) || !file_exists($commit_email_pl)) { die("Missing required executables."); }

# Find who made the changes
if($argc < 4) { die("Invalid arguments."); }

$author = exec("$svnlook author \"$argv[1]\" -r $argv[2]");
$res_db= db_query_params ('SELECT email FROM users where user_name = lower($1) LIMIT 1',
			array($author));
if($res_db)
{
	$e = db_fetch_array($res_db);
	if($e) {
		passthru("$commit_email_pl --from $e[email] \"$argv[1]\" $argv[2] $argv[3]");
		exit;
	}
}
passthru("$commit_email_pl \"$argv[1]\" $argv[2] $argv[3]");
?>
