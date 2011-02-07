<?php
/**
 * FusionForge Cron Viewing Page
 *
 * Copyright 2002 GForge, LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfcommon.'include/cron_utils.php';

site_admin_header(array('title'=>_('Cron Manager')));

$which = getIntFromRequest('which', 100);

?>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="get">
<?php echo html_build_select_box_from_arrays(array_keys($cron_arr), array_values($cron_arr), 'which', $which,true,'Any'); ?>
<input type="submit" name="submit" value="<?php echo _('Submit');?>" />
</form>
<?php

$title_arr = array(
	_('Date'),
	_('Job'),
	_('Message')
);

echo $HTML->listTableTop ($title_arr);

if ($which==100) {
	$res = db_query_params ('SELECT COUNT(*) AS count FROM cron_history',
				array ());
} else {
	$res = db_query_params ('SELECT COUNT(*) AS count FROM cron_history WHERE job=$1',
				array ($which));
}
$totalCount = db_result($res, 0, 'count');

$offset = getIntFromRequest('offset');
if($offset > $totalCount) {
	$offset = 0;
}

if ($which==100) {
	$res = db_query_params ('SELECT * FROM cron_history ORDER BY rundate DESC',
				array (),
				ADMIN_CRONMAN_ROWS,
				$offset);
} else {
	$res = db_query_params ('SELECT * FROM cron_history WHERE job=$1 ORDER BY rundate DESC',
				array ($which),
				ADMIN_CRONMAN_ROWS,
				$offset);
}

for ($i=0; $i<db_numrows($res); $i++) {

	echo '<tr '. $HTML->boxGetAltRowStyle($i+1) .'>
		<td>'. date(_('Y-m-d H:i'), db_result($res,$i,'rundate')).'</td>
		<td>'. $cron_arr[db_result($res,$i,'job')].'</td>
		<td>'. nl2br(htmlentities(db_result($res,$i,'output'))).'</td></tr>';

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
				. html_image('t2.png', '15', '15')
				. ' '._('Previous').'</a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td><td align="right">';
		if ($totalCount > $offset + ADMIN_CRONMAN_ROWS) {
			$nextUrl = 'cronman.php?which='.$which.'&amp;offset='.($offset + ADMIN_CRONMAN_ROWS);
			echo '<a href="'.$nextUrl.'" class="next">'
				._('Next').' '
				. html_image('t.png', '15', '15') . '</a>';
		} else {
			echo '&nbsp;';
		}
		?></td>
	</tr>
</table>
<?php
}

site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
