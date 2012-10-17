#! /usr/bin/php -f
<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'docman/Parsedata.class.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';

$p = new Parsedata();

$timestarttrait = microtime_float();
// documents list
$resarr = array();
$result = db_query_params('SELECT doc_data.docid, doc_data.group_id, doc_data.filename, doc_data.title, doc_data.description, doc_data.filetype from doc_data, groups where doc_data.group_id = groups.group_id and groups.force_docman_reindex = $1',
			   array('1'));
if (!$result) {
	die(db_error());
}

if ($result) {
	while ($arr = db_fetch_array($result)) {
		$resarr[] = $arr;
	}
}

$compt = 0;
$errorFlag = 0;
foreach ($resarr as $item) {
	$compt++;
	$timestart = microtime_float();
	$group = group_get_object($item['group_id']);
	$d = new Document($group, $item['docid']);
	$datafile = tempnam(forge_get_config('data_path'), 'tmp');
	$fh = fopen($datafile, 'w');
	fwrite($fh, $d->getFileData(false));
	fclose($fh);
	$lenin = $d->getFileSize();
	$res = $p->get_parse_data($datafile, htmlspecialchars($item['title']), htmlspecialchars($item['description']), $item['filetype'], $item['filename']);
	$len = strlen($res);
	if (file_exists($datafile)) {
		unlink($datafile);
	}
	$resUp = db_query_params('UPDATE doc_data SET data_words=$1 WHERE docid=$2',
			 array ($res, $item['docid']));
	if (!$resUp) {
		die('unable to update data: '.db_error());
	}
	$timeend = microtime_float();
	$timetrait = $timeend - $timestart;
	echo "Analyzed $item[filename] : type=$item[filetype]  octets in=$lenin  octets out=$len  time=$timetrait sec\n";
}
$timeendtrait = microtime_float();
$timetot = $timeendtrait - $timestarttrait;
db_query_params('UPDATE groups set force_docman_reindex = $1', array('0'));
//echo "End analyze : $compt files, $timetot secs.";

function microtime_float() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
