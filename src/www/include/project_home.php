<?php
/**
 * FusionForge Project Home
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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

require_once $gfwww.'news/news_utils.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'include/project_summary.php';
require_once $gfcommon.'include/tag_cloud.php';
require_once $gfcommon.'include/HTTPRequest.class.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

session_require_perm ('project_read', $group_id) ;

$title = _('Project Home');

use_javascript('/scripts/prototype/prototype.js');
use_javascript('/scripts/scriptaculous/scriptaculous.js');
use_javascript('/scripts/codendi/Tooltip.js');
use_javascript('/scripts/codendi/LayoutManager.js');
use_javascript('/scripts/codendi/ReorderColumns.js');

$request =& HTTPRequest::instance();
$request->set('group_id',$group_id);

$params['submenu'] = '';

if (session_loggedin()) {
	$group = group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_no_group();
	} elseif ($group->isError()) {
		exit_error($group->getErrorMessage(), 'home');
	}

	$perm =& $group->getPermission( session_get_user() );
	if ($perm && is_object($perm) && $perm->isAdmin()) {
		$sql = "SELECT l.*
				FROM layouts AS l INNER JOIN owner_layouts AS o ON(l.id = o.layout_id)
				WHERE o.owner_type = $1
				AND o.owner_id = $2
				AND o.is_default = 1
				";
		$res = db_query_params($sql,array('g', $group_id));
		if($res && db_numrows($res)<1) {
			$lm = new WidgetLayoutManager();
			$lm->createDefaultLayoutForProject($group_id,1);
			$res = db_query_params($sql,array('g', $group_id));
		}
		$id = db_result($res, 0 , 'id');
		$params['submenu'] = $HTML->subMenu(
			array(_("Add widgets"),
				_("Customize Layout")),
			array('/widgets/widgets.php?owner=g'. $group_id .'&amp;layout_id='. $id,
				'/widgets/widgets.php?owner=g'. $group_id .'&amp;layout_id='. $id.'&amp;update=layout'));
	}
}

site_project_header(array('title'=>$title, 'h1' => '', 'group'=>$group_id, 'toptab' => 'home',
	'submenu' => $params['submenu']));

$lm = new WidgetLayoutManager();
$lm->displayLayout($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP);

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
