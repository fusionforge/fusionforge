<?php
/**
  *
  * SourceForge Exports: Export project summary page as HTML
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
require_once('project_summary.php');

echo project_summary($group_id,$mode,$no_table);

?>
