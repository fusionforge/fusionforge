<?php
/**
  *
  * About Foundries Page
  *
  * This pages gives an intro to foundries and shows list of them all.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: about_foundries.php,v 1.19 2001/05/22 21:39:30 pfalcon Exp $
  *
  */

require_once('pre.php');
$HTML->header(array(title=>"About Foundries"));
?>

<P>
<h2>About <?php echo $GLOBALS['sys_name']; ?> Foundries</h2>

<? echo $Language->getText('about_foundries', 'about_blurb'); ?> 


<h2>Foundries</h2>

<p><? echo $Language->getText('about_foundries', 'foundries_list'); ?> 

<?php
	$query = "SELECT group_name,unix_group_name ".
		 "FROM groups WHERE status='A' AND is_public='1' ".
		 " AND type='2' ORDER BY group_name ";
	$result = db_query($query);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo "<H2>No matches found</H2>";
		echo db_error();
	} else {
		echo "<UL>";
		for ($i=0; $i<$rows; $i++) {
			echo "\n<li><A HREF=\"/foundry/".db_result($result, $i, 'unix_group_name')."/\">".
				db_result($result, $i, 'group_name')."</A></li>";
		}
		echo "\n</UL>";
	}
?>

<?php
$HTML->footer(array());

?>
