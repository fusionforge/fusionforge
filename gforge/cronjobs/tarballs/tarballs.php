#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$sys_cvs_root_path = '/cvsroot';

if(!isset($sys_cvs_root_path)) {
	$err = 'You have to define $sys_cvs_root_path variable in your config file.';
} elseif(!isset($sys_cvs_tarballs_path)) {
	$err = 'You have to define $sys_cvs_tarballs_path variable in your config file.';
} elseif(!is_dir($sys_cvs_root_path) || !is_readable($sys_cvs_root_path)) {
	$err = $sys_cvs_root_path.' is not a directory or is not readable.';
} elseif(!is_dir($sys_cvs_tarballs_path) || !is_writable($sys_cvs_tarballs_path)) {
	$err = $sys_cvs_tarballs_path.' is not a directory or is not writable.';
} else {
	exec('./tarballs.sh generate '.$sys_cvs_root_path.' '.$sys_cvs_tarballs_path.' 2>&1', $output);
	$err = implode("\n", $output);
	if(empty($err)) {
		$err = 'CVS tarballs generated';
	}
}

cron_entry(19, $err);

?>