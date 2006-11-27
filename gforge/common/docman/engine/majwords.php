#! /usr/bin/php4 -f
<?php
/**
 * GForge Doc Search engine
 *
 * 
 * Fabio Bertagnin November 2005
 *
 * @version   $Id: 04_IMPROVDOC_75_document_specific_search_engine.dpatch,v 1.1 2006/01/11 17:02:45 fabio Exp $
 */

require_once('pre.php');
require_once('www/docman/include/doc_utils.php');
require_once('common/docman/Parsedata.class');
require_once('common/docman/Document.class');
require_once('common/docman/DocumentFactory.class');
require_once('common/docman/DocumentGroupFactory.class');

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
?>
