<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
require ('pre.php');
require($DOCUMENT_ROOT.'/news/news_utils.php');

if ($limit>20) $limit=20;

$news=news_show_latest($group_id,$limit,$show_summaries,false,$flat);
print $news;
?>
