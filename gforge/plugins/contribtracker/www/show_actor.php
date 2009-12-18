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

$actor_id = getIntFromRequest ('actor_id') ;
$actor = new ContribTrackerActor ($actor_id) ;
if (!$actor || !is_object ($actor) || $actor->isError()) {
	exit_error (_('No actor'),
		    _('No actor or invalid actor specified')) ;
}

$HTML->header(array('title'=>_('Actor details'),'pagename'=>'contribtracker'));

print '<h1>'.sprintf(_('Actor details for %s'),
		     htmlspecialchars($actor->getName())).'</h1>' ;
print '<ul>' ;
print '<li><strong>'._('Name:').'</strong> '.htmlspecialchars($actor->getName()).'</li>' ;
print '<li><strong>'._('Address:').'</strong> '.htmlspecialchars($actor->getAddress()).'</li>' ;
print '<li><strong>'._('Email:').'</strong> '.htmlspecialchars($actor->getEmail()).'</li>' ;
print '<li><strong>'._('Legal structure:').'</strong> '.htmlspecialchars($actor->getLegalStructure()->getName()).'</li>' ;
print '<li><strong>'._('Description:').'</strong> '.htmlspecialchars($actor->getDescription()).'</li>' ;
print '</ul>' ;

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
		print '<h3>' . util_make_link ('/plugins/contribtracker/index.php?group_id='.$c->getGroup()->getId().'&contrib_id='.$c->getId (),
					       htmlspecialchars ($c->getName())) . '</h3>' ;
		print '<strong>'._('Group:').'</strong> ' ;
		print util_make_link_g ($c->getGroup()->getUnixName(),
					$c->getGroup()->getId(),
					$c->getGroup()->getPublicName()) ;
		print '<br /><strong>'._('Role:').'</strong> ' ;
		print htmlspecialchars ($p->getRole()->getName()) ;
	}
}
site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
