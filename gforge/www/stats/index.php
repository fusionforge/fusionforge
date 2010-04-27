<?php
/**
  *
  * SourceForge Sitewide Statistics
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'stats/site_stats_utils.php';

// require you to be a member of the sfstats group
session_require( array('group'=>forge_get_config('stats_group')) );

$HTML->header(array('title'=>sprintf(_('%1$s Site Statistics'), forge_get_config ('forge_name'))));

//
// BEGIN PAGE CONTENT CODE
//

echo "\n\n";

print '<h1>'._('Sitewide aggregate statistics').'</h1>' . "\n";
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

stats_site_projects_daily( 7 );

stats_site_projects_monthly( );

echo '<h2>'._('Other statistics').'</h2>';
echo '<ul><li><a href="i18n.php">'.("I18n Statistics").'</a></li></ul>';

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
