<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

$HTML->header(array(title=>"Project Management: Add Task"));

$timedate = time();

?> 
<p>&nbsp;</p>

<table border="0" width="800">
<tr>  
	<td width="800">
	<pre>
<?php
	$cal = `/usr/bin/cal -y`;
	print ("$cal");
?>
	</pre>
	</td>

</tr>

</table>

<?php
$HTML->footer(array());

?>
