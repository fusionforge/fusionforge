#! /usr/bin/php5 -f
<?php

require 'www/env.inc.php';
require $gfwww.'include/squal_pre.php';
require $gfcommon.'include/cron_utils.php';

$verbose=0;
$scmname='scmroot';
$hot_backup = $sys_opt_path.'/cronjobs/hot-backup.py';

$sys_scm_root_path = "$sys_chroot$svndir_prefix";

putenv('SVN_PATH='.dirname(`which svn`));

if(!isset($sys_scm_root_path)) {
	$err = 'You have to define $svndir_prefix and possibly $sys_chroot variable in your config file.';
} elseif(!isset($sys_scm_tarballs_path)) {
	$err = 'You have to define $sys_scm_tarballs_path variable in your config file.';
} elseif(!is_dir($sys_scm_root_path) || !is_readable($sys_scm_root_path)) {
	$err = $sys_scm_root_path.' is not a directory or is not readable.';
} elseif(!is_dir($sys_scm_tarballs_path) || !is_writable($sys_scm_tarballs_path)) {
	$err = $sys_scm_tarballs_path.' is not a directory or is not writable.';
} else {
	$output='';
	if ($handle = opendir($sys_scm_root_path)) {
		if ($verbose) echo "Scanning $sys_scm_root_path\n";
		chdir($sys_scm_root_path);
		while (false !== ($file = readdir($handle))) {
			chdir($sys_scm_root_path);
			if ($file != "." && $file != ".." && is_dir($file) && $file != "cvs-locks" && $file != '.deleted') {
				if ($verbose) echo "Creating $sys_scm_tarballs_path/$file-$scmname.tar.gz\n";
				mkdir("$sys_scm_tarballs_path/$file");
				chdir($sys_scm_root_path);
				exec("$hot_backup $file $sys_scm_tarballs_path/$file 2>&1", $output);
				chdir($sys_scm_tarballs_path);
				exec("tar czf $sys_scm_tarballs_path/$file-$scmname.tar.gz.new $file 2>&1", $output);

				if (is_file("$sys_scm_tarballs_path/$file-$scmname.tar.gz.new")){
					rename("$sys_scm_tarballs_path/$file-$scmname.tar.gz.new","$sys_scm_tarballs_path/$file-$scmname.tar.gz");
					rename("$sys_scm_tarballs_path/$file","$sys_scm_tarballs_path/$file.done_by_cron");
					system("rm -rf $sys_scm_tarballs_path/$file.done_by_cron");
				}
			}
		}
		closedir($handle);
	}
	if($output) {
		$err = implode("\n", $output);
	}
	if(empty($err)) {
		$err = 'SCM tarballs generated';
	}
}

cron_entry(19, $err);

?>
