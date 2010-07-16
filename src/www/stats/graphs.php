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
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'stats/site_stats_utils.php';

session_require_global_perm ('forge_stats', 'read') ;

$HTML->header(array('title'=>sprintf(_('%1$s Site Statistics'), forge_get_config ('forge_name'))));

echo "\n\n";

print '<h1>'._('Sitewide Statistics Graphs').'</h1>' . "\n";
print '<div align="center">' . "\n";
?>
<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><a href="index.php"><?php echo _('OVERVIEW STATS'); ?></a></td>
<td><a href="projects.php"><?php echo _('PROJECT STATS'); ?></a></td>
<td><strong><?php echo _('SITE GRAPHS'); ?></strong></td>
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
