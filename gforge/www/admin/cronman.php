<?php
/**
 * GForge Cron Viewing Page
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


require_once('pre.php');
require_once('www/admin/admin_utils.php');
require ('common/include/cron_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>$Language->getText('admin_index','title')));

if (!$which || $which==100) {
	$which=100;
} else {
	$sql_str = " WHERE job='$which' ";
}

?>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<?php echo html_build_select_box_from_arrays(array_keys($cron_arr), $cron_arr, 'which', $which,true,'Any'); ?>
<input type="submit" name="submit" value="Submit">
</form>
<?php

$title_arr[]=$Language->getText('cronman','date');
$title_arr[]=$Language->getText('cronman','job');
$title_arr[]=$Language->getText('cronman','message');

echo $HTML->listTableTop ($title_arr);

$sql="SELECT * FROM cron_history $sql_str ORDER BY rundate DESC";

$res=db_query($sql);
for ($i=0; $i<db_numrows($res); $i++) {

	echo '<tr '. $HTML->boxGetAltRowStyle($i+1) .'>
		<td>'. date($sys_datefmt, db_result($res,$i,'rundate')).'</td>
		<td>'. $cron_arr[db_result($res,$i,'job')].'</td>
		<td>'. db_result($res,$i,'output').'</td></tr>';

}

echo $HTML->listTableBottom();

site_admin_footer(array());

?>
