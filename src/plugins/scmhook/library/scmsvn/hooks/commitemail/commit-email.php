#! /usr/bin/php
<?php
/**
 * Copyright 2010, Fusionforge Team
 * Copyright 2011, Franck Villaume - Capgemini
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

require_once dirname(__FILE__).'/../../../../../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

# Begin configuration
$svnlook = '/usr/bin/svnlook';
$commit_email_pl = dirname(__FILE__).'/commit-email.pl';
# End configuration

# Set sendmail path to next script from our configuration.
putenv("SENDMAIL=".forge_get_config('sendmail_path'));

# Make svnlook support accents
putenv("LANG=en_US.UTF-8");

if(!file_exists($svnlook) || !file_exists($commit_email_pl)) { die("Missing required executables."); }

# Find who made the changes
if($argc < 4) { die("Invalid arguments."); }

$users_host = forge_get_config('users_host');
if (!empty($users_host))
  $hostname = "-h $users_host";
passthru("$commit_email_pl \"$argv[1]\" $argv[2] $hostname $argv[3]", $ret);
exit($ret);  // warn SVN if something went wrong
