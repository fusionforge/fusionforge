#! /usr/bin/php5 -f
<?php
/**
 * FusionForge document search engine
 *
 * Copyright 2005, Fabio Bertagnin
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

require_once $gfwww.'include/pre.php';
require_once $gfwww.'docman/include/doc_utils.php';
require_once $gfcommon.'docman/Parsedata.class.php';
require_once $gfcommon.'docman/Document.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';
require_once $gfcommon.'docman/DocumentGroupFactory.class.php';

$p = new Parsedata ("$sys_engine_path");
// print_debug(print_r($p->get_parser_list (),true));

$timestarttrait = microtime_float();
// documents list
$sql = "SELECT docid, group_id, filename, title, createdate, filename, description, filetype, data FROM doc_data \n";
$resarr = array();
$result=db_query($sql);
if ($result)
{
	while ($arr = db_fetch_array($result))
	{
		$resarr[] = $arr;
	}
}

$compt = 0;
$rapp = "";
foreach ($resarr as $item)
{
	$compt++;
	$timestart = microtime_float();
	$data1 = base64_decode($item["data"]);
	$lenin = strlen($data1);
	$res = $p->get_parse_data ($data1, $item["title"], $item["description"], $item["filetype"]);
	$len = strlen($res);
	$sql = "UPDATE doc_data SET data_words = '$res' WHERE docid = $item[docid] ";
	db_query($sql);
	$timeend = microtime_float();
	$timetrait = $timeend - $timestart;
	print_debug ("analyze $item[filename]  type=$item[filetype]  octets in=$lenin  octets out=$len   time=$timetrait sec");
}
$timeendtrait = microtime_float();
$timetot = $timeendtrait - $timestarttrait;
print_debug ("End analyze : $compt files, $timetot secs.");


function print_debug ($text)
{
	echo "$text\n";
}

function microtime_float()
{
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
