#! /usr/bin/php5
<?php
  /* 
   * Copyright (C) 2010  Olaf Lenz
   *
   * This file is part of FusionForge.
   *
   * FusionForge is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * FusionForge is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * You should have received a copy of the GNU General Public License
   * along with FusionForge; if not, write to the Free Software
   * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   */

  /** This script will set up the structure required to use the
   mediawiki plugin. 
   
   It is usually started from the plugin manager, but can also be
   started manually.
   */

if ( isset( $_SERVER ) && 
     array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	$command_line = false;
} else {
	require('/etc/gforge/local.inc');
	require_once (dirname(__FILE__) . '/../../env.inc.php');
	$command_line = true;
}

if (!isset($mediawiki_var_path))
	$mediawiki_var_path = "$sys_var_path/plugins/mediawiki";
if (!isset($mediawiki_projects_path))
	$mediawiki_projects_path = "$mediawiki_var_path/projects";
if (!isset($mediawiki_master_path))
	$mediawiki_master_path = "$mediawiki_var_path/master";

# create directories
if (!is_dir($mediawiki_projects_path))
  system("mkdir -p $mediawiki_projects_path");
if (!is_dir($mediawiki_master_path))
  system("mkdir -p $mediawiki_master_path");

function mysymlink($from, $to) {
	global $mw_feedback;
	if (!@symlink($from, $to))
		$mw_feedback[] = sprintf(_('Could not create symbolic link from %1$s to %1$s'), $from, $to);
}

$mw_feedback = array();
# install links in master
# link files from $mediawiki_src_path to $mediawiki_master_path
if (!($dh = opendir($mediawiki_src_path))) {
	$mw_feedback[] = sprintf(_('Could not open mediawiki source directory %1$s!'), $mediawiki_src_path);
} else {
	$ignore_file = array( 
		'.' => true, 
		'..' => true,
		'config' => true,
		'skins' => true,
		'images' => true,
		'tests' => true,
		't' => true,
		);
	while ($file = readdir($dh)) {
		if (!$ignore_file[$file]) {
			$from = "$mediawiki_src_path/$file";
			$to = "$mediawiki_master_path/$file";
			mysymlink($from, $to);
		}
	}
	closedir ($dh);
}

# link LocalSettings.php from /etc/gforge/plugins/mediawiki/LocalSettings.php or from $sys_share_path/plugins/mediawiki/etc/plugins/mediawiki/LocalSettings.php
$from = "$sys_etc_path/plugins/mediawiki/LocalSettings.php";
if (!file_exists($from)) {
	$from = "$sys_share_path/plugins/mediawiki/etc/plugins/mediawiki/LocalSettings.php";
}
$to = "$mediawiki_master_path/LocalSettings.php";
mysymlink($from, $to);

# create skin directory
$todir = "$mediawiki_master_path/skins";
if (!is_dir($todir))
	mkdir($todir);

# link FusionForge skin file
$fromdir = "$sys_share_path/plugins/mediawiki/mediawiki-skin";
$from = "$fromdir/FusionForge.php";
$to = "$todir/FusionForge.php";
mysymlink($from, $to);

# create skin subdir
$todir = "$todir/fusionforge";
if (!is_dir($todir))
	mkdir($todir);

# link fusionforge main.css files
$fromdir = "$fromdir/fusionforge";
$from = "$fromdir/main.css";
$to = "$todir/main.css";
mysymlink($from, $to);

# link the rest of the files from monobook skin
$fromdir = "$mediawiki_src_path/skins/monobook";

$dh = opendir($fromdir);
$ignore_file = array( 
	'.' => true, 
	'..' => true,
	'main.css' => true,
	);
while ($file = readdir($dh)) {
	if (!$ignore_file[$file]) {
		$from = "$fromdir/$file";
		$to = "$todir/$file";
		mysymlink($from, $to);
	}
}
closedir($dh);

if ($command_line) {
	foreach ($mw_feedback as $line) {
		echo "$line\n";
	}
} else {
	foreach ($mw_feedback as $line) {
		$feedback .= "<br />$line";
	}
	$feedback .= "<br />";
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
