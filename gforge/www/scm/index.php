<?php
/**
 * GForge SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-05-19
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once('pre.php');    
require_once('www/scm/include/scm_utils.php');    

$group_id = getIntFromRequest("group_id");
scm_header(array('title'=>_('SCM Repository'),'group'=>$group_id));

$hook_params = array () ;
$hook_params['group_id'] = $group_id ;
plugin_hook ("scm_page", $hook_params) ;

scm_footer(); 

?>
