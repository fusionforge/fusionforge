<?php
/**
 * show which user has activated monitoring on this tracker.
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

$title = _('Show which user has activated monitoring on the tracker').' '.$ath->getName();
$ath->adminHeader(array ('title'=>$title, 'modal'=>1));

switch (getStringFromRequest('func')) {
	case 'monitor' : {
		$start = getIntFromRequest('start');
		$stop = getIntFromRequest('stop');
		$user_id = getIntFromRequest('user_id');
		$groupMemberIds = array_map(function($user) {
			return $user->getID();
		}, $group->getMembers());

		if (in_array($user_id,$groupMemberIds)) {
			// Fix to prevent collision with the start variable used in browse.
			$_GET['start'] = 0;
			if ($start && $ath->isMonitoring($user_id)) {
				$feedback = _('Monitoring Started');
			} elseif ($stop && !$ath->isMonitoring($user_id)) {
				$feedback = _('Monitoring Deactivated');
			} else {
				$ath->setMonitor($user_id);
				$feedback=$ath->getErrorMessage();
				$ath->clearError();
			}
		} else {
			$feedback=_('User is not member of this project');
		}
		break;
	}
}
//Show list of monitors
echo '<h1>' ._('Users from this project with monitoring status').'</h1>';
$headers = array(
	_('User Name'),
	_('E-Mail'),
	_('Monitor'),
);
echo $GLOBALS['HTML']->listTableTop($headers);

$users = $ath->Group->getUsers();
foreach ($users as &$user) {
	$link = '/tracker/admin/?group_id='.$group_id.'&amp;atid='.$atid.'&amp;show_monitor=1&amp;func=monitor&amp;user_id='.$user->getID();
	if ($ath->isMonitoring($user->getID())) {
		$label = _('Stop Monitor');
		$link .= '&amp;stop=1';
	} else {
		$label = _('Monitor');
		$link  .= '&amp;start=1';
	}
	echo '<tr>';
	echo '<td>'.$user->data_array['realname'].'</td>';
	echo '<td>'.$user->data_array['email'].'</td>';
	echo '<td>'.util_make_link($link, $label).'</td>';
	echo '</tr>';
}
echo $GLOBALS['HTML']->listTableBottom();
$ath->footer(array());
