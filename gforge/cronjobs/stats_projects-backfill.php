#!/usr/local/bin/php
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('squal_pre.php');
include_once('cronjobs/stats_projects.inc');

$i=0;

while($i < 515) {

	$i++;

	$how_far_back=(86400 * $i);

	$time=time()-$how_far_back;

	$year=date('Y',$time);
	$month=date('m',$time);
	$day=date('d',$time);

	$datetime="$year$month$day";

	if ($datetime < 19991117) {
		$i=1000;
		echo 'done';
		break;
	}

	project_stats_day($year,$month,$day);
}

?>
