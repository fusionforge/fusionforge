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

print '<DIV ALIGN="CENTER">' . "\n";
print '<font size="+1"><b>Sitewide Agregate Statistics </b></font><BR>' . "\n";
?>

<HR>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center"><B>OVERVIEW STATS</B></td>
<td align="center"><a href="projects.php">PROJECT STATS</a></td>
<td align="center"><a href="graphs.php">SITE GRAPHS</a></td>
</tr>
</table>

<HR>

<?php

stats_site_agregate();
print '<BR><BR>';
stats_site_projects_daily( 7 );
print '<BR><BR>';
stats_site_projects_monthly( );
print '<BR><BR>' . "\n";
print '</DIV>' . "\n";
echo '
<p>Other statistics:
<ul>
<li><a href="lastlogins.php">Most Recent Logins</A>
<li><a href="i18n.php">I18n Statistics</a>
</ul>
</p>
';
//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
