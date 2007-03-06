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


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/admin/admin_utils.php');
require_once ('common/include/cron_utils.php');

site_admin_header(array('title'=>$Language->getText('admin_index','title')));

$which = getIntFromRequest('which');
if (!$which || $which==100) {
	$which=100;
} else {
	$sql_str = " WHERE job='$which' ";
}

?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<?php echo html_build_select_box_from_arrays(array_keys($cron_arr), $cron_arr, 'which', $which,true,'Any'); ?>
<input type="submit" name="submit" value="<?php echo $Language->getText('general', 'submit');?>">
</form>
<?php

$title_arr = array(
	$Language->getText('cronman','date'),
	$Language->getText('cronman','job'),
	$Language->getText('cronman','message')
);

echo $HTML->listTableTop ($title_arr);

$sql = 'SELECT COUNT(*) AS count FROM cron_history '.$sql_str;
$res = db_query($sql);
$totalCount = db_result($res, 0, 'count');

$offset = getIntFromRequest('offset');
if($offset > $totalCount) {
	$offset = 0;
}

$sql = 'SELECT * FROM cron_history '.$sql_str.' ORDER BY rundate DESC LIMIT '.ADMIN_CRONMAN_ROWS.' OFFSET '.$offset;
$res = db_query($sql);

for ($i=0; $i<db_numrows($res); $i++) {

	echo '<tr '. $HTML->boxGetAltRowStyle($i+1) .'>
		<td>'. date($sys_datefmt, db_result($res,$i,'rundate')).'</td>
		<td>'. $cron_arr[db_result($res,$i,'job')].'</td>
		<td>'. nl2br(db_result($res,$i,'output')).'</td></tr>';

}

echo $HTML->listTableBottom();

if($totalCount > ADMIN_CRONMAN_ROWS) {
?>
<br />
<table class="tablegetmore" width="100%" cellpadding="5" cellspacing="0">
	<tr>
		<td><?php
		if ($offset != 0) {
			$previousUrl = 'cronman.php?which='.$which.'&amp;offset='.($offset - ADMIN_CRONMAN_ROWS);
			echo '<a href="'.$previousUrl.'" class="prev">'
				. html_image('t2.png', '15', '15', array('border'=>'0','align'=>'middle'))
				. ' '.$Language->getText('cronman', 'previous').'</a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td><td align="right">';
		if ($totalCount > $offset + ADMIN_CRONMAN_ROWS) {
			$nextUrl = 'cronman.php?which='.$which.'&amp;offset='.($offset + ADMIN_CRONMAN_ROWS);
			echo '<a href="'.$nextUrl.'" class="next">'
				.$Language->getText('cronman', 'next').' '
				. html_image('t.png', '15', '15', array('border'=>'0','align'=>'middle')) . '</a>';
		} else {
			echo '&nbsp;';
		}
		?></td>
	</tr>
</table>
<?php
}

site_admin_footer(array());

?>