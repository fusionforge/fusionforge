#! /usr/bin/php5 -f
<?php

$verbose=0;
$scmname='scmroot';
require ('squal_pre.php');
require ('common/include/cron_utils.php');

$sys_scm_root_path = "$sys_chroot$cvsdir_prefix";

if(!isset($sys_scm_root_path)) {
	$err = 'You have to define $cvsdir_prefix and possibly $sys_chroot variable in your config file.';
} elseif(!isset($sys_scm_tarballs_path)) {
	$err = 'You have to define $sys_scm_tarballs_path variable in your config file.';
} elseif(!is_dir($sys_scm_root_path) || !is_readable($sys_scm_root_path)) {
	$err = $sys_scm_root_path.' is not a directory or is not readable.';
} elseif(!is_dir($sys_scm_tarballs_path) || !is_writable($sys_scm_tarballs_path)) {
	$err = $sys_scm_tarballs_path.' is not a directory or is not writable.';
} else {
	if ($handle = opendir($sys_scm_root_path)) {
		chdir($sys_scm_root_path);
		if ($verbose) echo "Scanning $sys_scm_root_path\n";
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir($file) && $file != "cvs-locks") {
				if ($verbose) echo "Creating $sys_scm_tarballs_path/$file-$scmname.tar.gz\n";
				exec("tar czf $sys_scm_tarballs_path/$file-$scmname.tar.gz.new $file 2>&1", $output);
				if (is_file("$sys_scm_tarballs_path/$file-$scmname.tar.gz.new")){
					rename("$sys_scm_tarballs_path/$file-$scmname.tar.gz.new","$sys_scm_tarballs_path/$file-$scmname.tar.gz");
				}
			}
		}
		closedir($handle);
	}
	$err = implode("\n", $output);
	if(empty($err)) {
		$err = 'SCM tarballs generated';
	}
}

cron_entry(19, $err);

?>
