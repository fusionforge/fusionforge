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

$HTML->header(array('title'=>$Language->getText('stats','title',array($GLOBALS['sys_name']))));

echo "\n\n";

print '<div align="center">' . "\n";
print '<h3>'.$Language->getText('stats_graph','sitewide_statistics_graphs').'</h3><br />' . "\n";
?>
<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><a href="index.php"><?php echo $Language->getText('stats_graph','overview_stats'); ?></a></td>
<td><a href="projects.php"><?php echo $Language->getText('stats_graph','project_stats'); ?></a></td>
<td><strong><?php echo $Language->getText('stats_graph','site_graphs'); ?></strong></td>
</tr>
</table>

<hr />

<br /><br />
<img src="views_graph.php?monthly=1" alt="" />
<br /><br />
<img src="users_graph.php" alt="" />
<br /><br />
</div>

<?php
$HTML->footer( array() );
?>
