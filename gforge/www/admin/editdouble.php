<?php
/**
  *
  * Site Admin page to edit File Release System processor types
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


$unit        = 'item';
$table       = 'tmp_lang';
$primary_key = 'seq';
$whereclause = " WHERE language_id||pagename||category IN (SELECT language_id||pagename||category FROM (SELECT count(*) AS cnt,language_id,pagename,category  FROM tmp_lang WHERE pagename!='#' GROUP BY language_id,pagename,category) AS cntdouble where cnt>1 AND language_id='".$lang."') AND pagename!='' ORDER BY language_id,pagename,category,seq ";
$columns     = "seq, tmpid, pagename, category, tstring";
$edit        = 'yes';

include_once('admintabfiles.php');

?>
