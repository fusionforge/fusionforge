<?php
//
//show which user has activated monitoring on this tracker.

$title = sprintf(_('Show which user has activated monitoring on the tracker %s'),$ath->getName());
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
			if ($start && $ath->isMonitoring($user_id))
				$feedback = _('Monitoring Started');
			elseif ($stop && !$ath->isMonitoring($user_id))
				$feedback = _('Monitoring Deactivated');
			else {
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
foreach ($users as &$user)
{
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
