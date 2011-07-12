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

$actor_id = getIntFromRequest ('actor_id') ;

if ($actor_id) {
	$actor = new ContribTrackerActor ($actor_id) ;
	if (!is_object ($actor) || $actor->isError()) {
		exit_error (_('Invalid actor'),'contribtracker');
	}
	Header ("Content-type: image/png");
	echo $actor->getLogo();
} else {
		exit_error (_('Invalid actor'),'contribtracker');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
