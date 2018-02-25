<?php
/**
 * FusionForge Front Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2008-2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfwww.'include/features_boxes.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

if (!forge_get_config('forge_homepage_widget')) {
	$HTML->header(array('title'=>_('Welcome'), 'h1' => ''));

	// Main page content is now themeable (see www/include/Layout.class.php);
	// Default is index_std.php;
	include ($HTML->getRootIndex());
} else {

	$params = array('submenu' => null);
	if (session_loggedin() && forge_check_global_perm('forge_admin')) {

		// Display with the preferred layout/theme of the user (if logged-in)
		$sql = "SELECT l.*
				FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
				WHERE o.owner_type = $1
				AND o.owner_id = $2
				AND o.is_default = 1
				";
		$res = db_query_params($sql,array('h', 0));
		if($res && db_numrows($res)<1) {
			$lm = new WidgetLayoutManager();
			$lm->createDefaultLayoutForForge(0);
			$res = db_query_params($sql,array('h', 0));
		}
		$id = db_result($res, 0 , 'id');
		$params['submenu'] = $HTML->subMenu(
					array(_("Add widgets"),
						_("Customize Layout")),
					array('/widgets/widgets.php?owner=h0&layout_id='.$id,
						'/widgets/widgets.php?owner=h0&layout_id='.$id.'&update=layout'),
					array(array('title' => _('Select new widgets to display on the forge home page.')),
					array('title' => _('Modify the layout: one column, multiple columns or build your own layout.'))));
	}

	html_use_jqueryui();
	site_header(array('title'=> _('Welcome'), 'h1' => '', 'toptab' => 'home',
		'submenu' => $params['submenu']));

	$lm = new WidgetLayoutManager();
	$lm->displayLayout(0, WidgetLayoutManager::OWNER_TYPE_HOME);
}

$HTML->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
