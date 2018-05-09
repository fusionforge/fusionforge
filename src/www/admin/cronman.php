<?php
/**
 * FusionForge Cron Viewing Page
 *
 * Copyright 2002 GForge, LLC
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';
require_once $gfcommon.'include/cron_utils.php';

global $HTML;

site_admin_header(array('title'=>_('Cron Manager')));

$which = getStringFromRequest('which', 100);

echo $HTML->openForm(array('action' => '/admin/cronman.php', 'method' => 'get'));
echo html_build_select_box_from_arrays(array_keys($cron_arr), array_values($cron_arr), 'which', $which, true, _('Any'));
echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit')));
echo $HTML->closeForm();

if ($which == 100) {
	$res = db_query_params ('SELECT COUNT(*) AS count FROM cron_history',
				array ());
} else {
	$res = db_query_params ('SELECT COUNT(*) AS count FROM cron_history WHERE job=$1',
				array ($which));
}
$totalCount = (int)db_result($res, 0, 'count');

$offset = getIntFromRequest('offset');
if($offset > $totalCount) {
	$offset = 0;
}

if ($totalCount) {
	if ($which == 100) {
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

	$title_arr = array(
		_('Date'),
		_('Job'),
		_('Message')
	);

	echo $HTML->listTableTop($title_arr);

	for ($i=0; $i<db_numrows($res); $i++) {
		$cells = array();
		$cells[][] = date(_('Y-m-d H:i'), db_result($res,$i,'rundate'));
		$cells[][] = $cron_arr[db_result($res,$i,'job')];
		$cells[][] = nl2br(htmlentities(db_result($res,$i,'output')));
		echo $HTML->multiTableRow(array(), $cells);
	}

	echo $HTML->listTableBottom();

	if($totalCount > ADMIN_CRONMAN_ROWS) {
	?>
	<br />
	<table class="tablegetmore fullwidth" cellpadding="5">
		<tr>
			<td><?php
			if ($offset != 0) {
				echo util_make_link('/admin/cronman.php?which='.$which.'&offset='.($offset - ADMIN_CRONMAN_ROWS),
							$HTML->getPrevPic().' '._('Previous'),
							array('class' => 'prev'));
			} else {
				echo '&nbsp;';
			}
			echo '</td><td class="align-right">';
			if ($totalCount > $offset + ADMIN_CRONMAN_ROWS) {
				echo util_make_link('/admin/cronman.php?which='.$which.'&offset='.($offset + ADMIN_CRONMAN_ROWS),
							_('Next').' '.$HTML->getNextPic(),
							array('class' => 'next'));
			} else {
				echo '&nbsp;';
			}
			?></td>
		</tr>
	</table>
	<?php
	}
} else {
	echo $HTML->information(_('No message entries found'));
}

site_admin_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
