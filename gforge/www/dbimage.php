<?php
/**
  *
  * Fetch a multimedia data from database
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('squal_pre.php');

$res=db_query("SELECT * FROM db_images WHERE id='$id'");

$filename=db_result($res,0,'filename');
$type=db_result($res,0,'filetype');
$data=base64_decode(db_result($res,0,'bin_data'));

Header('Content-disposition: filename="'.str_replace('"', '', $filename).'"');
Header("Content-type: $type");
echo $data;

?>
