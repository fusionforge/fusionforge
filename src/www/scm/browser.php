<?php
/**
 * SCM Frontend
 *
 * Copyright 2004, Tim Perdue -GForge LLC
 * Copyright 2004-2009, Roland Mas
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';

$group_id = getIntFromRequest("group_id");
scm_header(array('title'=>_('SCM Repository'),'group'=>$group_id));

$hook_params = array();
$hook_params['group_id'] = $group_id;
$hook_params['repo_name'] = getStringFromRequest('repo_name', 'none');
$hook_params['user_id'] = getIntFromRequest('user_id');
$hook_params['extra'] = getStringFromRequest('extra');
$hook_params['commit'] = getStringFromRequest('commit');
$hook_params['scm_plugin'] = getStringFromRequest('scm_plugin');
plugin_hook ('scm_browser_page', $hook_params);

scm_footer();
