<?php

/*
 * MoinMoinWiki plugin
 *
 * Copyright 2009-2011, Roland Mas
 * Copyright 2006, Daniel Perez
 *
 */

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

$group_id = getIntFromRequest('group_id');
$pluginname = 'moinmoin' ;

$group = group_get_object($group_id);
if (!$group) {
	exit_error ("Invalid Project", "Invalid Project");
}

if (!$group->usesPlugin ($pluginname)) {
	exit_error ("Error", "First activate the $pluginname plugin through the Project's Admin Interface");
}

$params = array () ;
$params['toptab']      = $pluginname;
$params['group']       = $group_id;
$params['title']       = 'MoinMoinWiki' ;
$params['pagename']    = $pluginname;
$params['sectionvals'] = array ($group->getPublicName());

site_project_header($params);

if (file_exists ('/var/lib/gforge/plugins/moinmoin/wikidata/'.$group->getUnixName().'.py')) {
	echo '<iframe src="'.util_make_url('/plugins/moinmoin/'.$group->getUnixName().'/FrontPage').'" frameborder="0" width=100% height=700></iframe>' ;
} else {
	print '<h2>'._('Wiki not created yet, please wait for a few minutes.').'</h2>';
}

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
