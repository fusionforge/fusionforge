<?php
/**
  *
  * SourceForge Exports: Export project news as HTML
  *
  * Parameters:
  *	group_id	-	gorup_id
  *	limit		-	number of items to export
  *	show_summaries	-	0 to show only headlines, 1 to also show
  *				summaries
  *	flat		-	1 to use minimal HTML formatting
  *	
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/news/news_utils.php');

if ($limit>20) $limit=20;

$news=news_show_latest($group_id,$limit,$show_summaries,false,$flat);
print $news;
?>
