<?php

/*
 * MediaWiki plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2006, Daniel Perez
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

$group_id = getStringFromRequest('group_id');
$pluginname = 'mediawiki' ;

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
$params['title']       = sprintf ('Mediawiki for project %s', $group->getPublicName()) ;
$params['pagename']    = $pluginname;
$params['sectionvals'] = array ($group->getPublicName());

site_project_header($params);

echo '<iframe src="'.util_make_url('/plugins/mediawiki/wiki/'.$group->getUnixName().'/index.php').'" frameborder="no" width=100% height=700></iframe>' ;

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
