<?php

/*
 * ContribTracker plugin
 *
 * Copyright 2009, Roland Mas
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
$plugin = plugin_get_object ('contribtracker') ;

$group_id = getIntFromRequest ('group_id') ;
session_require(array('group'=>$group_id,'admin_flags'=>'A'));
$group = group_get_object ($group_id) ;

$params = array () ;
$params['toptab'] = 'contribtracker' ;
$params['group'] = $group_id ;
$params['title'] = sprintf (_('Contribution tracker for project %s'),
			    htmlspecialchars ($group->getPublicName()));
$params['pagename'] = 'contribtracker' ;
$params['sectionvals'] = array($group->getPublicName());

$date_format = _('%Y-%m-%d') ;

site_project_header ($params) ;

$action = getStringFromRequest ('action') ;
util_ensure_value_in_set ($action, array ('display',
					  'post_add_contrib',
					  'edit_contrib',
					  'del_contrib',
					  'post_edit_contrib',
					  'del_part',
					  'add_part')) ;

function check_contrib_id ($c_id, $g_id) {
	$contrib = new ContribTrackerContribution ($c_id) ;
	if (!$contrib || !is_object ($contrib)
	    || $contrib->getGroup()->getId() != $g_id) {
		exit_permission_denied () ;
	}
}
function check_part_id ($p_id, $c_id) {
	$part = new ContribTrackerParticipation ($p_id) ;
	if (!$part || !is_object ($part)
	    || $part->getContrib()->getId() != $c_id) {
		exit_permission_denied () ;
	}
}
function check_date () {
	global $date_format ;
	$r_date = getStringFromRequest ('date') ;
	$tmp = strptime ($r_date, $date_format) ;
	if (!$tmp) {
		$date = time () ;
	} else {
		$date = mktime (0,0,0,$tmp['tm_mon']+1,$tmp['tm_mday'],$tmp['tm_year']);
	}
	return $date ;
}
	

// Get and validate parameters, error if tampered with
switch ($action) {
case 'display':
	break;
case 'post_add_contrib':
	$date = check_date () ;
	$name = getStringFromRequest ('contrib_name') ;
	$desc = getStringFromRequest ('contrib_desc') ;
	break ;
case 'edit_contrib':
case 'del_contrib':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	break ;	
case 'post_edit_contrib':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$date = check_date () ;
	$name = getStringFromRequest ('contrib_name') ;
	$desc = getStringFromRequest ('contrib_desc') ;
	break ;
case 'del_part':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$part_id = getIntFromRequest ('part_id') ;
	check_part_id ($part_id, $contrib_id) ;
	break ;
case 'add_part':
	$contrib_id = getIntFromRequest ('contrib_id') ;
	check_contrib_id ($contrib_id, $group_id) ;
	$actor_id = getIntFromRequest ('actor_id') ;
	$actor = new ContribTrackerActor ($actor_id) ;
	if (!$actor || !is_object ($actor)) {
		exit_permission_denied () ;
	}
	$role_id = getIntFromRequest ('role_id') ;
	$role = new ContribTrackerRole ($role_id) ;
	if (!$role || !is_object ($role)) {
		exit_permission_denied () ;
	}
	break ;
}
	
// Do the required action

switch ($action) {
case 'post_add_contrib':
	$contrib = new ContribTrackerContribution () ;
	if (!$contrib->create ($name, $date, $desc, $group)) {
		exit_error ($contrib->getErrorMessage()) ;
	}
	$contrib_id = $contrib->getId() ;
	$action = 'edit_contrib' ;
	break ;
case 'del_contrib':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$contrib->delete () ;
	$action = 'display' ;
	break ;
case 'post_edit_contrib':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$contrib->update ($name, $date, $desc, $group) ;
	$action = 'edit_contrib' ;
	break ;
case 'del_part':
	$part = new ContribTrackerParticipation ($part_id) ;
	$part->delete () ;
	$action = 'edit_contrib' ;
	break ;
case 'add_part':
	$contrib = new ContribTrackerContribution ($contrib_id) ;
	$actor = new ContribTrackerActor ($actor_id) ;
	$role = new ContribTrackerRole ($role_id) ;
	$part = new ContribTrackerParticipation () ;
	$part->create ($contrib, $actor, $role) ;
	$action = 'edit_contrib' ;
	break ;
}

print '<h1>'._('Register a new contribution').'</h1>' ;
?>
<form action="<?php echo util_make_url ('/plugins/'.$plugin->name.'/project_admin.php') ?>" method="post">
<input type="hidden" name="action" value="post_add_contrib" />
<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<?php echo _('Contribution name:') ?> <input type="text" name="contrib_name" size="20" /><br />
<?php echo _('Contribution date:') ?> <input type="text" name="date" value="<?php echo strftime($date_format,time()) ?>"  /><br />
<?php echo _('Contribution description:') ?> <input type="textarea" name="contrib_desc" rows="20" cols="80" /><br />
<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
</form>

<?php
$contribs = $plugin->getContributionsByGroup ($group) ;
usort ($contribs, array ($plugin, "ContribComparator")) ;
if (count ($contribs) != 0) {
	print '<h1>'._('Existing contributions').'</h1>' ;
	
	foreach ($contribs as $c) {
		print '<h3>'.$c->getName().'</h3>' ;
		print '<strong>'._('Date:').'</strong> ' ;
		print strftime (_('%Y-%m-%d'), $c->getDate ()) ;
		print '<br />' ;
		
		print '<strong>'._('Description:').'</strong> ' ;
		print htmlspecialchars ($c->getDescription ()) ;
		print '<br />' ;
		
		$parts = $c->getParticipations () ;
		print '<strong>'.ngettext('Participant:',
					  'Participants:',
					  count ($parts)).'</strong> ' ;
		print '<br />' ;
		print '<ul>' ;
		foreach ($parts as $p) {
			print '<li>' ;
			printf (_('%s: %s (%s)'),
				htmlspecialchars ($p->getRole()->getName()),
				util_make_link ('/plugins/'.$plugin->name.'/?actor_id='.$p->getActor()->getId (),
						htmlspecialchars ($p->getActor()->getName())),
				htmlspecialchars ($p->getActor()->getLegalStructure()->getName())) ;
			print '</li>' ;
		}
		print '</ul>' ;
	}
	














}





util_make_link ('/plugins/'.$plugin->name.'/project_admin.php?group_id='.$group->getId ().'&action=foo',
		_('Blah')) ;
	

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
