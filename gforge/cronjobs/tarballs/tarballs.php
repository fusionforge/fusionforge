#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$sys_scm_root_path = '/cvsroot';

if(!isset($sys_scm_root_path)) {
	$err = 'You have to define $sys_scm_root_path variable in your config file.';
} elseif(!isset($sys_scm_tarballs_path)) {
	$err = 'You have to define $sys_scm_tarballs_path variable in your config file.';
} elseif(!is_dir($sys_scm_root_path) || !is_readable($sys_scm_root_path)) {
	$err = $sys_scm_root_path.' is not a directory or is not readable.';
} elseif(!is_dir($sys_scm_tarballs_path) || !is_writable($sys_scm_tarballs_path)) {
	$err = $sys_scm_tarballs_path.' is not a directory or is not writable.';
} else {
	exec('./tarballs.sh generate '.$sys_scm_root_path.' '.$sys_scm_tarballs_path.' 2>&1', $output);
	$err = implode("\n", $output);
	if(empty($err)) {
		$err = 'SCM tarballs generated';
	}
}

cron_entry(19, $err);

?>
