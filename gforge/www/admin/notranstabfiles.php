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
$primary_key = 'tmpid';
$whereclause = " WHERE language_id='Base' AND pagename!='#' AND pagename||category NOT IN (select pagename||category FROM $table WHERE language_id='" . $lang . "' ) ORDER BY seq";
$columns     = "tmpid, pagename, category, tstring";
include_once('admintabfiles.php');

?>
