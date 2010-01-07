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

if ($actor_id) {
	$actor = new ContribTrackerActor ($actor_id) ;
	if (!is_object ($actor) || $actor->isError()) {
		exit_error (_('Invalid actor'),
			    _('Invalid actor specified.')) ;
	}
	Header ("Content-type: image/png");
	echo $actor->getLogo();
} else {
		exit_error (_('Invalid actor'),
			    _('Invalid actor specified.')) ;
}	

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
