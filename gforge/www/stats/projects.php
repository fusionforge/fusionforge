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

$HTML->header(array('title'=>"SourceForge Site Statistics "));

?>
<DIV ALIGN="CENTER">
<font size="+1"><b>Project Statistical Comparisons</b></font><BR>
</DIV>

<HR>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center"><a href="index.php">OVERVIEW STATS</a></td>
<td align="center"><B>PROJECT STATS</B></td>
<td align="center"><a href="graphs.php">SITE GRAPHS</a></td>
</tr>
</table>

<HR>

<?php


if ( isset( $report ) ) {

	// Print the form, passing it the params, so it can save state.
	stats_site_projects_form( $report, $orderby, $projects, $trovecatid );

	?>
	<DIV ALIGN="CENTER">
	<BR><BR>
	<?php

	stats_site_projects( $report, $orderby, $projects, $trovecatid );

	?>
	<BR><BR>
	</DIV>
	<?php

} else { 

	stats_site_projects_form( );

}

//
// END PAGE CONTENT CODE
//

$HTML->footer( array() );
?>
