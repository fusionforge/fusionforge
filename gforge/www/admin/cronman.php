<?php
/**
 * GForge Cron Viewing Page
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('pre.php');
require_once('www/admin/admin_utils.php');
require_once ('common/include/cron_utils.php');

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
