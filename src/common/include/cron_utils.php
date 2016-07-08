<?php
/**
 * FusionForge cron job utilities
 *
 * Copyright 2003, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

//
//	This key id# is important - do not change or renumber
//
require_once $gfcommon.'include/SysTasksQ.class.php';
$cron_arr = array(
	 0 => 'unused',
	 1 => 'db/calculate_user_metric.php',
	 2 => 'db/check_stale_tracker_items.php',
	 3 => 'db/db_project_sums.php',
	 4 => 'db/db_stats_agg.php',
	 5 => 'db/db_trove_maint.php',
	'MASSMAIL' => 'db/massmail.php',  # 6
	 7 => 'db/project_cleanup.php',
	 8 => 'db/project_weekly_metric.php',
	 9 => 'db/rating_stats.php',
	10 => 'db/rotate_activity.php',
	11 => 'db/site_stats.php',
	12 => 'db/vacuum.php',
	'LISTS' => 'lists/mailing_lists_create.php',  # 18
	20 => 'db/reporting_cron.php',
	22 => 'db/daily_task_email.php',
	'HOMEDIR' => 'shell/homedirs.php',  # 25
	'SCM_REPO' => 'scm/create_scm_repos.php',  # 27
	28 => 'scm/gather_scm_stats.php',
	'WEB_VHOSTS' => 'web-vhosts/create_vhosts.php',  # 30
	);

#	 13 => 'cvs.php',
#	 14 => 'history_parse.php',
#	 15 => 'ssh_create.php',
#	 16 => 'usergroup.php',
#	 17 => 'misc/mailaliases.php',
#	 19 => 'tarballs.php',
#	 21 => 'create_svn.php',
#	 23 => 'misc/backup_site.php',
#	 24 => 'svn-stats.php',
#	 26 => 'update_users.php',
#	 29 => 'weekly.php',
#    901 => 'create_groups.php';
#    902 => 'mailing_lists_index.php';
#    903 => 'job-server.pl';

function cron_entry($job,$output) {
	$sql='INSERT INTO cron_history (rundate,job,output)
		values ($1, $2, $3)' ;
	return db_query_params ($sql,
				array (time(), $job, $output));
}

function cron_debug($string) {
	global $verbose;
	if($verbose) {
		echo $string."\n";
	}
}

function checkChroot() {

	if(forge_get_config('chroot') != '' && is_dir(forge_get_config('chroot'))) {
		return true;
	}
	return false;
}

// Locking: for a single script
// flock() locks are automatically lost on program termination, however
// that happened (clean, segfault...)
// Global, otherwise auto-closed by PHP and we lose the lock!
$locks = array();
function cron_acquire_lock($script) {
	global $locks;
	// Script lock: http://perl.plover.com/yak/flock/samples/slide006.html
	if (!isset($locks[$script]))
		$locks[$script] = fopen($script, 'r') or die("Failed to ask lock.\n");

	if (!flock($locks[$script], LOCK_EX | LOCK_NB)) {
		die("There's a lock for '$script', exiting\n");
	}
}

function cron_release_lock($script) {
	global $locks;
	flock($locks[$script], LOCK_UN);
	unset($locks[$script]);
}

//
// Reload NSCD, in particular when replicating new groups, users or
// project memberships
//
function cron_reload_nscd() {
        system("(nscd -i passwd && nscd -i group) >/dev/null 2>&1");
}

function cron_reload_apache() {
        system("service apache2 reload || service httpd reload >/dev/null 2>&1");
}

function cron_regen_apache_auth() {
	# Reproduce nss_passwd on file, so we can work without mod-auth-*
	$passwd_fname = forge_get_config('data_path').'/scm-passwd';
	$passwd_f = fopen($passwd_fname.'.new', 'w');

	# Enable /authscm/$user ITK URLs for FusionForge users only (not system users)
	$config_fname = forge_get_config('data_path').'/scm-auth.inc';
	$config_f = fopen($config_fname.'.new', 'w');

	$res = db_query_params("SELECT login, passwd FROM nss_passwd WHERE status=$1", array('A'));
	while ($arr = db_fetch_array($res)) {
		fwrite($passwd_f, $arr['login'].':'.$arr['passwd']."\n");
		fwrite($config_f, 'Use ScmUser '.$arr['login']."\n");
	}

	fclose($passwd_f);
	chmod($passwd_fname.'.new', 0644);
	rename($passwd_fname.'.new', $passwd_fname);

	fclose($config_f);
	chmod($config_fname.'.new', 0644);
	rename($config_fname.'.new', $config_fname);

	# Regen scmsvn-auth.inc
	$hook_params = array() ;
	plugin_hook_by_reference ('scm_regen_apache_auth', $hook_params) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
