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

$HTML->header(array('title'=>$GLOBALS['sys_name']." Site Statistics "));

?>
<div align="center">
<h3><?php echo $Language->getText('stats_projects','project_comparision'); ?></h3><br />
</div>

<hr />

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr align="center">
<td><a href="index.php"><?php echo $Language->getText('stats_graph','overview_stats'); ?></a></td>
<td><strong><?php echo $Language->getText('stats_graph','project_stats'); ?></strong></td>
<td><a href="graphs.php"><?php echo $Language->getText('stats_graph','site_graphs'); ?></a></td>
</tr>
</table>

<hr />

<?php


if ( isset( $report ) ) {

	// Print the form, passing it the params, so it can save state.
	stats_site_projects_form( $report, $orderby, $projects, $trovecatid );

	?>
	<div align="center">
	<br /><br />
	<?php

	stats_site_projects( $report, $orderby, $projects, $trovecatid );

	?>
	<br /><br />
	</div>
	<?php

} else { 

	stats_site_projects_form( );

}

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
