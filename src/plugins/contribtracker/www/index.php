<?php

/**
 * ContribTracker plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2010 (c) Franck Villaume
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
$plugin = plugin_get_object ('contribtracker') ;

function display_contribution ($c, $show_groups = false) {
	global $plugin ;
	print '<h2>'.$c->getName().'</h2>' ;
	if ($show_groups) {
		print '<strong>'._('Project:').'</strong> ' ;
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
			util_make_link ('/plugins/'.$plugin->name.'/?actor_id='.$p->getActor()->getId (),
					htmlspecialchars ($p->getActor()->getName())),
			htmlspecialchars ($p->getActor()->getLegalStructure()->getName())) ;
		if ($p->getActor()->getLogo() != '') {
			print ' ';
			print util_make_link ('/plugins/'.$plugin->name.'/?actor_id='.$p->getActor()->getId (),
					      '<img type="image/png" src="'.util_make_url ('/plugins/'.$plugin->name.'/actor_logo.php?actor_id='.$p->getActor()->getId ()).'" />') ;
		}
		print '</li>' ;
	}
	print '</ul>' ;
}
	
$group_id = getIntFromRequest ('group_id') ;
$actor_id = getIntFromRequest ('actor_id') ;
if ($group_id) {
	$group = group_get_object ($group_id) ;
	if(!$group || !is_object ($group)) {
		exit_no_group () ;
	}
	session_require_perm ('project_read', $group_id);

	$contrib_id = getIntFromRequest ('contrib_id') ;
	if ($contrib_id) {    // List only one particular contribution
		$contrib = new ContribTrackerContribution ($contrib_id) ;
		if (!$contrib || !is_object ($contrib)
		    || $contrib->getGroup()->getId() != $group_id) {
			exit_permission_denied ('','home') ;
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

		if (count ($contribs) == 0) {
			print '<h1>'._('No contributions').'</h1>' ;
			print _('No contributions have been recorded for this project yet.') ;
		} else {
			print '<h1>'.sprintf (_('Contributions for project %s'),
					    htmlspecialchars ($group->getPublicName())).'</h1>' ;
			
			foreach ($contribs as $c) {
				display_contribution ($c) ;
				print '<hr />' ;
			}
		}
	}
} elseif ($actor_id) {
	$actor = new ContribTrackerActor ($actor_id) ;
	if (!is_object ($actor) || $actor->isError()) {
		exit_error (_('Invalid actor'),'contribtracker');
	}

	$HTML->header(array('title'=>_('Actor details'),'pagename'=>'contribtracker'));
	
	print '<h1>'.sprintf(_('Actor details for %s'),
			     htmlspecialchars($actor->getName())).'</h1>' ;
	print '<ul>' ;
	print '<li><strong>'._('Name:').'</strong> '.htmlspecialchars($actor->getName()).'</li>' ;
	print '<li><strong>'._('URL:').'</strong> ' ;
	if ($actor->getUrl() != '') {
		print '<a href="'.htmlspecialchars($actor->getUrl()).'">'.htmlspecialchars($actor->getUrl()).'</a>';
	}
	print '</li>' ;
	print '<li><strong>'._('Email:').'</strong> '.htmlspecialchars($actor->getEmail()).'</li>' ;
	print '<li><strong>'._('Legal structure:').'</strong> '.htmlspecialchars($actor->getLegalStructure()->getName()).'</li>' ;
	print '<li><strong>'._('Description:').'</strong> '.htmlspecialchars($actor->getDescription()).'</li>' ;
	print '</ul>' ;
	if ($actor->getLogo() != '') {
		if ($actor->getUrl() != '') {
			print '<a href="'.htmlspecialchars($actor->getUrl()).'"><img type="image/png" src="'.util_make_url ('/plugins/'.$plugin->name.'/actor_logo.php?actor_id='.$actor->getId ()).'" /></a>' ;
		} else {
			print '<img type="image/png" src="'.util_make_url ('/plugins/'.$plugin->name.'/actor_logo.php?actor_id='.$actor->getId ()).'" />' ;
		}
	}
	
	$participations = $actor->getParticipations () ;
	
	if (count ($participations) == 0) {
		printf (_("%s hasn't been involved in any contributions yet"),
			htmlspecialchars($actor->getName())) ;
	} else {
		print '<h1>'.sprintf(ngettext('Contribution by %s',
					      'Contributions by %s',
					      count($participations)),
				     htmlspecialchars($actor->getName())).'</h1>' ;
		
		foreach ($participations as $p) {
			$c = $p->getContribution () ;
			print '<h2>' . util_make_link ('/plugins/'.$plugin->name.'/?group_id='.$c->getGroup()->getId().'&contrib_id='.$c->getId (),
						       htmlspecialchars ($c->getName())) . '</h2>' ;
			print '<strong>'._('Project:').'</strong> ' ;
			print util_make_link_g ($c->getGroup()->getUnixName(),
						$c->getGroup()->getId(),
						$c->getGroup()->getPublicName()) ;
			print '<br /><strong>'._('Role:').'</strong> ' ;
			print htmlspecialchars ($p->getRole()->getName()) ;
			print '<hr />' ;
		}
	}
} else {			// Latest contributions, globally
	$HTML->header(array('title'=>_('Contributions'),'pagename'=>'contribtracker'));
	
	$contribs = $plugin->getContributions () ;
	
	if (count ($contribs) == 0) {
		print '<h1>'._('No contributions').'</h1>' ;
		print _('No contributions have been recorded yet.') ;
	} else {
		print '<h1>'._('Latest contributions').'</h1>' ;

		$i = 1 ;
		foreach ($contribs as $c) {
			display_contribution ($c, true) ;
			print '<hr />' ;
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
