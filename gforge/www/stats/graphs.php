<?php
/**
  *
  * SourceForge Sitewide Statistics
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('site_stats_utils.php');

// require you to be a member of the sfstats group (group_id = 11084)
session_require( array('group'=>$sys_stats_group) );

$HTML->header(array('title'=>"SourceForge Site Statistics"));

//
// BEGIN PAGE CONTENT CODE
//

echo "\n\n";

print '<DIV ALIGN="CENTER">' . "\n";
print '<font size="+1"><b>Sitewide Statistics Graphs</b></font><BR>' . "\n";
?>

<HR>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center"><a href="index.php">OVERVIEW STATS</a></td>
<td align="center"><a href="projects.php">PROJECT STATS</a></td>
<td align="center"><B>SITE GRAPHS</B></td>
</tr>
</table>

<HR>

<BR><BR>
<IMG SRC="views_graph.png">
<BR><BR>
<IMG SRC="views_graph.png?monthly=1">
<BR><BR>
<IMG SRC="users_graph.png">
<BR><BR>
</DIV>

<?php
$HTML->footer( array() );
?>
