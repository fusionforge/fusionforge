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
$whereclause = " lang1 ,tmp_lang lang2 WHERE lang1.language_id='Base' AND lang2.language_id='" . $lang . "' AND lang1.pagename=lang2.pagename AND lang1.category=lang2.category AND lang1.pagename!='#' AND lang2.pagename!='#' AND lang2.tmpid!='-1' ORDER BY lang1.seq";
$columns     = "lang1.seq, lang1.pagename, lang1.category, lang1.tstring, lang2.tstring";

include_once('admintabfiles.php');

?>
