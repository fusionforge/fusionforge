#! /usr/bin/php -f
<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Parsedata.class.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';

$engine_path = dirname(__FILE__).'/../common/docman/engine/';
$p = new Parsedata($engine_path);

$timestarttrait = microtime_float();
// documents list
$resarr = array();
$result = db_query_params('SELECT doc_data.docid, doc_data.group_id, doc_data.filename, doc_data.title, doc_data.filename, doc_data.description, doc_data.filetype from doc_data, groups where doc_data.group_id = groups.group_id and groups.force_docman_reindex = $1',
			   array('1'));
if (!$result) {
	die(db_error());
}

if ($result) {
	while ($arr = db_fetch_array($result)) 	{
		$resarr[] = $arr;
	}
}

$compt = 0;
$errorFlag = 0;
foreach ($resarr as $item) {
	$compt++;
	$timestart = microtime_float();
	$doc_dataData = db_query_params('SELECT data from doc_data where docid = $1', array($item["docid"]));
	if (!$doc_dataData) {
		die("unable to get data: ".db_error());
	}
	$data1 = base64_decode($doc_dataData["data"]);
	$lenin = strlen($data1);
	$res = $p->get_parse_data($data1, $item["title"], $item["description"], $item["filetype"]);
	$len = strlen($res);
	$resUp = db_query_params('UPDATE doc_data SET data_words=$1 WHERE docid=$2',
			 array ($res, $item["docid"]));
	if (!$resUp) {
		die("unable to update data: ".db_error());
	}
	$timeend = microtime_float();
	$timetrait = $timeend - $timestart;
	print_debug("analyze $item[filename]  type=$item[filetype]  octets in=$lenin  octets out=$len   time=$timetrait sec");
}
$timeendtrait = microtime_float();
$timetot = $timeendtrait - $timestarttrait;
db_query_params('UPDATE groups set force_docman_reindex = $1', array('0'));
//print_debug ("End analyze : $compt files, $timetot secs.");


function print_debug ($text) {
	echo "$text\n";
}

function microtime_float() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
