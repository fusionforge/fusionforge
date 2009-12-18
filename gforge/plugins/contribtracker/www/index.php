<?php

/*
 * ContribTracker plugin
 *
 * Copyright 2009, Roland Mas
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

$HTML->header(array('title'=>_('Contributions'),'pagename'=>'contribtracker'));

$plugin = plugin_get_object ('contribtracker') ;

$contribs = $plugin->getContributions () ;

usort ($contribs, "ContribTrackerPlugin::ContribComparator") ;

if (count ($contribs) == 0) {
	print '<h1>'._('No contributions').'</h1>' ;
	print _('No contributions have been recorded yet.') ;
} else {
	print '<h1>'._('Latest contributions').'</h1>' ;


	foreach ($contribs as $c) {
		print '<h3>'.$c->getName().'</h3>' ;
		print '<h4>'.$c->getGroup()->getPublicName().'</h4>' ;

		$parts = $c->getParticipations () ;
		foreach ($parts as $p) {
			print $p->getActor()->getName() ;
			print " (" ;
			print $p->getActor()->getLegalStructure()->getName() ;
			print ") as " ;
			print $p->getRole()->getName() ;
			print '<br />' ;
		}
	}
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
