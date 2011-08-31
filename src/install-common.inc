#!/usr/bin/php
<?php
/**
 * FusionForge Installation
 *
 * Copyright 2010, Roland Mas
 * http://fusionforge.org/
 */

$fusionforge_etc_dir = getenv('FUSIONFORGE_ETC_DIR');
if (empty($fusionforge_etc_dir))
{
	$fusionforge_etc_dir = '/etc/gforge';
}
$fusionforge_src_dir = getenv('FUSIONFORGE_SRC_DIR');
if (empty($fusionforge_src_dir))
{
	$fusionforge_src_dir = '/opt/gforge';
}
$fusionforge_data_dir = getenv('FUSIONFORGE_DATA_DIR');
if (empty($fusionforge_data_dir))
{
	$fusionforge_data_dir = '/var/lib/gforge';
}

$STDOUT = fopen('php://stdout','w');
$STDIN = fopen('php://stdin','r');

function show($text, $newLine = true) {
	global $STDOUT;

	$hd = fopen ("/tmp/gforge-import.log", "a+");
	fwrite($hd, "*** $text\n");
	fclose($hd);

	if ($newLine) {
		$text = GREEN.$text .NORMAL."\n";
	}
	fwrite($STDOUT, $text);
}

function run($command, $ignore = false) {

	$hd = fopen ("/tmp/gforge-import.log", "a+");
	fwrite($hd, "CMD ".$command."\n");
	fclose($hd);

	system($command, $ret);
	if ($ignore) {
		if ($ret != 0) {
			return false;
		} else {
			return true;
		}
	} else {
		if ($ret != 0) {
			echo RED.'An error ocurred running the last command... aborting.'.NORMAL."\n";
			die();
		}
	}
}

function validatePassword($password) {
	if (strlen($password)<6) {
		return 'Password is too short. Please try again.';
	}
	if (!preg_match('/[[:alnum:]]*/', $password)) {
		return 'Password contains invalid characters. Please try again.';
	}
	return '';
}

function readMasked($prompt) {
	global $STDIN;
	if (strtolower(php_uname('s')) == 'sunos') {
	    show($prompt);
	    $text_entered = fgets($STDIN);
	} else {
	    $options="-er -s -p";
	    $returned=popen("read $options \"".GREEN.$prompt.NORMAL."\n\"; echo \$REPLY", 'r');
	    $text_entered=fgets($returned, 100);
	    pclose($returned);
	    $text_entered=substr($text_entered, 0, strlen($text_entered));
	    @ob_flush();
	    flush();
	}
	return trim($text_entered);
}

