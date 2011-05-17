<?php
/**
 * SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, Tim Perdue -GForge LLC
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'scm/include/scm_utils.php';    

$group_id = getIntFromRequest("group_id");
$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
scm_header(array('title'=>_('SCM Repository'),'group'=>$group_id));

plugin_hook ("blocks", "scm index");

$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("scm_page", $hook_params) ;

scm_footer(); 

?>
