#! /usr/bin/php -f
<?php
/**
 * Small and fast system action trigger
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

//putenv('FUSIONFORGE_NO_PLUGINS=true');
//putenv('FUSIONFORGE_NO_DB=true');

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

// (sysactionsq_lists) -> cf. mail_group_list
// - sysaction_id references sysactions

// (sysactionsq_membership)
// -> user_groups_lastmodified


// Locking: for a single script
// flock() locks are automatically lost on program termination, however
// that happened (clean, segfault...)
$lock = null;  // global, or auto-closed by PHP and we lose the lock!
function AcquireReplicationLock($script) {
  // Script lock: http://perl.plover.com/yak/flock/samples/slide006.html
  global $argv, $lock;
  $lock = fopen($script, 'r') or die("Failed to ask lock.\n");

  if (!flock($lock, LOCK_EX | LOCK_NB)) {
    die("There's a lock for '$script', exiting\n");
  }
}

// Invalidate users/groups cache e.g. when a user is added to a group
// Special-case in 'publish-subscribe' mode
function usergroups_sync() {
		global $usergroups_lastsync;
		$res = db_query_params("SELECT MAX(last_modified_date) AS lastmodified FROM nss_usergroups");
		$row = db_fetch_array($res);
		if ($row['lastmodified'] > $usergroups_lastsync) {
				cron_reload_nscd();
				$hook_params = array();
				plugin_hook("usergroups_sync", $hook_params);
				$usergroups_lastsync = time();
		}
}

function sysaction_get_script($plugin_id, $sysaction_id) {
		global $cron_arr;
		if ($plugin_id == null) {
				if (isset($cron_arr[$sysaction_id]))
						return forge_get_config('source_path').'/cronjobs/'.$cron_arr[$sysaction_id];
				else
						return null;
		} else {
				// TODO
				// $path = forge_get_config('plugins_path')."/$plugin/cronjobs/";
		}
}

usergroups_sync();
while (true) {
		// Deal with pending requests
		$res = db_query_params("SELECT * FROM sysactionsq WHERE status=$1", array('TODO'));
		while ($arr = db_fetch_array($res)) {
				usergroups_sync();
				$script = sysaction_get_script($arr['plugin_id'], $arr['sysaction_id']);
				if (!is_executable($script)) {
						db_query_params("UPDATE sysactionsq SET status=$1, error_message=$2"
										. " WHERE sysactionsq_id=$3",
										array('ERROR',
											  "Cron job {$arr['plugin_id']}/{$arr['sysaction_id']}"
											  . " '$script' not found or not executable.\n",
											  $arr['sysactionsq_id']));
						continue;
				}
				db_query_params("UPDATE sysactionsq SET status=$1 WHERE sysactionsq_id=$2",
								array('WIP', $arr['sysactionsq_id']));
				AcquireReplicationLock($script);
				$ret = null;
				system("$script\n", $ret);
				if ($ret == 0) {
						db_query_params("UPDATE sysactionsq SET status=$1 WHERE sysactionsq_id=$2",
										array('DONE', $arr['sysactionsq_id']));
				} else {
						db_query_params("UPDATE sysactionsq SET status=$1 WHERE sysactionsq_id=$2",
										array('ERROR', $arr['sysactionsq_id']));
				}
		}

		usergroups_sync();

		sleep(1);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End: