#! /usr/bin/php4 -f
<?php
/**
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require ('squal_pre.php');

$today_formatted=date('Ymd',(time()-(30*60*60*24)));

db_begin();

$sql = "DELETE FROM activity_log WHERE day < '$today_formatted'";
echo $sql;
$rel = db_query($sql);
echo db_error();

db_commit();

echo "Done: ".date('Ymd H:i').' - '.db_error();

?>
