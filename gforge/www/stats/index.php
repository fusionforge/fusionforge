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

// require you to be a member of the sfstats group
session_require( array('group'=>$sys_stats_group) );

$HTML->header(array('title'=>$GLOBALS['sys_name']." Site Statistics "));

//
// BEGIN PAGE CONTENT CODE
//

echo "\n\n";

print '<div align="center">' . "\n";
print '<h3>Sitewide Aggregate Statistics </h3><br />' . "\n";
?>

<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><strong>OVERVIEW STATS</strong></td>
<td><a href="projects.php">PROJECT STATS</a></td>
<td><a href="graphs.php">SITE GRAPHS</a></td>
</tr>
</table>

<hr />

<?php

stats_site_aggregate();
print '<br /><br />';
stats_site_projects_daily( 7 );
print '<br /><br />';
stats_site_projects_monthly( );
print '<br /><br />' . "\n";
print '</div>' . "\n";
echo '
<p>Other statistics:
<ul>
<li><a href="lastlogins.php">Most Recent Logins</a></li>
<li><a href="i18n.php">I18n Statistics</a></li>
</ul>
</p>
';
//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
