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
$whereclause = " WHERE language_id='".$lang."' AND pagename!='#' AND pagename||category NOT IN (select pagename||category FROM $table WHERE language_id='Base' ) ORDER BY seq";
$columns     = "seq, tmpid, pagename, category, tstring";
$edit        = 'yes';

include_once('admintabfiles.php');

?>
