<?php 
/**
 * FusionForge Project Home
 *
 * Copyright 1999-2001 (c) VA Linux Systems 
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once $gfwww.'news/news_utils.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'include/project_summary.php';
require_once $gfcommon.'include/tag_cloud.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

session_require_perm ('project_read', $group_id) ;

$title = _('Project Home');

use_javascript('/scripts/prototype/prototype.js');
use_javascript('/scripts/scriptaculous/scriptaculous.js');
use_javascript('/scripts/codendi/Tooltip.js');
use_javascript('/scripts/codendi/LayoutManager.js');
use_javascript('/scripts/codendi/ReorderColumns.js');

site_project_header(array('title'=>$title,'h1' => '', 'group'=>$group_id,'toptab'=>'home'));

$request =& HTTPRequest::instance();
$request->set('group_id',$group_id);

$lm = new WidgetLayoutManager();
$lm->displayLayout($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP);

site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
