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


require_once('../env.inc.php');
require_once('pre.php');
require_once('site_stats_utils.php');

// require you to be a member of the sfstats group
session_require( array('group'=>$sys_stats_group) );

$HTML->header(array('title'=>sprintf(_('%1$s Site Statistics'), $GLOBALS['sys_name'])));

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
<td><strong><?php echo _('OVERVIEW STATS'); ?></strong></td>
<td><a href="projects.php"><?php echo _('PROJECT STATS'); ?></a></td>
<td><a href="graphs.php"><?php echo _('SITE GRAPHS'); ?></a></td>
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
echo '<p/>'._('Other statistics:<ul><li><a href="i18n.php">I18n Statistics</a></li></ul>');

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
