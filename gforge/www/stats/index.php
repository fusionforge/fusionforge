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

$HTML->header(array('title'=>$Language->getText('stats','title',array($GLOBALS['sys_name']))));

//
// BEGIN PAGE CONTENT CODE
//

echo "\n\n";

print '<div align="center">' . "\n";
print '<h3>'.$Language->getText('stats_index','sitewide_aggregate_statistics
').'</h3><br />' . "\n";
?>

<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><strong><?php echo $Language->getText('stats_index','overview_stats'); ?></strong></td>
<td><a href="projects.php"><?php echo $Language->getText('stats_index','project_stats'); ?></a></td>
<td><a href="graphs.php"><?php echo $Language->getText('stats_index','site_graphs'); ?></a></td>
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
echo '<p>'.$Language->getText('stats_index','other_statistics').'</p>';

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
