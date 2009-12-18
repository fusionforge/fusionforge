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

function display_contribution ($c, $show_groups = false) {
	print '<h3>'.$c->getName().'</h3>' ;
	if ($show_groups) {
		print '<strong>'._('Group:').'</strong> ' ;
		print util_make_link_g ($c->getGroup()->getUnixName(),
					$c->getGroup()->getId(),
					htmlspecialchars ($c->getGroup()->getPublicName())) ;
		print '<br />' ;
	}

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
			util_make_link ('/plugins/contribtracker/show_actor.php?actor_id='.$p->getActor()->getId (),
					htmlspecialchars ($p->getActor()->getName())),
			htmlspecialchars ($p->getActor()->getLegalStructure()->getName())) ;
		print '</li>' ;
	}
	print '</ul>' ;
}
	
$group_id = getIntFromRequest ('group_id') ;
if ($group_id) {
	$group = group_get_object ($group_id) ;
	if(!$group || !is_object ($group)) {
		exit_no_group () ;
	}
	if (!$group->isPublic()) {
		$perm =& $group->getPermission(session_get_user());
		
		if (!$perm || !is_object($perm) || !$perm->isMember()) {
			exit_no_group () ;
		}
	}

	$contrib_id = getIntFromRequest ('contrib_id') ;
	if ($contrib_id) {    // List only one particular contribution
		$contrib = new ContribTrackerContribution ($contrib_id) ;
		if (!$contrib || !is_object ($contrib)
		    || $contrib->getGroup()->getId() != $group_id) {
			exit_permission_denied () ;
		}

		$params = array () ;
		$params['toptab'] = 'contribtracker' ;
		$params['group'] = $group_id ;
		$params['title'] = _('Contribution details') ;
		$params['pagename'] = 'contribtracker' ;
		$params['sectionvals'] = array($group->getPublicName());    

		site_project_header ($params) ;

		display_contribution ($contrib) ;
	} else {	// List all contributions relevant to a group
		$params = array () ;
		$params['toptab'] = 'contribtracker' ;
		$params['group'] = $group_id ;
		$params['title'] = sprintf (_('Contributions for project %s'),
					    htmlspecialchars ($group->getPublicName()));
		$params['pagename'] = 'contribtracker' ;
		$params['sectionvals'] = array($group->getPublicName());

		site_project_header ($params) ;

		$contribs = $plugin->getContributionsByGroup ($group) ;
		usort ($contribs, array ($plugin, "ContribComparator")) ;

		if (count ($contribs) == 0) {
			print '<h1>'._('No contributions').'</h1>' ;
			print _('No contributions have been recorded for this project yet.') ;
		} else {
			print '<h1>'._('Latest contributions').'</h1>' ;
			
			foreach ($contribs as $c) {
				display_contribution ($c) ;
			}
		}
	}
} else {			// Latest contributions, globally
	$HTML->header(array('title'=>_('Contributions'),'pagename'=>'contribtracker'));
	
	$contribs = $plugin->getContributions () ;
	
	usort ($contribs, array ($plugin, "ContribComparator")) ;
	
	if (count ($contribs) == 0) {
		print '<h1>'._('No contributions').'</h1>' ;
		print _('No contributions have been recorded yet.') ;
	} else {
		print '<h1>'._('Latest contributions').'</h1>' ;

		$i = 1 ;
		foreach ($contribs as $c) {
			display_contribution ($c, true) ;
			$i++ ;
			if ($i > 20) {
				break ;
			}
		}
	}
}	


site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
