<?php
/**
  *
  * The Kernel Traffic page
  *
  * This page displays the retrieved Kernel Traffic page within a
  * SourceForge look-and-feel.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: kernel_traffic.php,v 1.4 2001/05/22 21:39:31 pfalcon Exp $
  * @author: Darrell Brogdon <dbrogdon@valinux.com>
  *
  */


require_once('pre.php');
require_once('www/news/news_utils.php');
require_once('features_boxes.php');
require_once('cache.php');

/**
 * getKernelTraffic() - Pull the downloaded contents of Kernel Traffic from the database
 *
 * This function mostly exists so we can cache the results.
 */
function getKernelTraffic()
{
	$sql = "SELECT kt_data FROM kernel_traffic";
	$res = db_query($sql);
	return db_result($res,0,0);
}

//set up the group_id
$group_id='18435';

//set up a foundry object for reference all over the place
$foundry=&group_get_object($group_id);

$HTML->header(array('title'=>'Kernel Traffic','group'=>$group_id));

echo'	<TABLE cellspacing="0" cellpadding="10" border="0" width="100%">
		<TR>
			<TD valign="top" align="left">
';

echo '			<table><tr><td>';		// Needed to make the formatting show properly

echo cache_display('kerneltraffic','getKernelTraffic()',(24*3600));
?>

<hr>
<b>Legal And Historical Notice</b><br>
<br>
All KT and KC issues are Copyright their respective authors and released under the GPL.<br>
<br>
Linux &reg; is a registered trademark of Linus Torvalds<br>
<br>
Kernel Traffic is copyright &copy; Zack Brown<br>
<br>
Kernel Cousins are copyright &copy; their respective authors.<br>
<br>
Kernel Traffic and the Cousins are distributed under the terms of the GNU General Public Licence, version 2, or (at your discretion) any later version.<br>
<br>
Kernel Traffic and the Cousins will always be indebted to Mark Constable, of http://www.renta.net/, who hosted all of the Kernel Traffic and Kernel Cousin pages at http://www.opensrc.org from January through September 1999. The Open Source movement never had a truer friend.<br>
<br>

<?php

echo '</TD><TD VALIGN="TOP" WIDTH="30%">';

// Display the sponsor info if any
echo $foundry->getSponsorHTML1();

// Display the Stat's features boxes on the right
echo cache_display('foundry'.$group_id.'_features_boxes','foundry_features_boxes()',(24*3600));

echo '</TD></TR></TABLE>';

$HTML->footer(array());
?>
