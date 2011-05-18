<?php
/** FusionForge ClearCase plugin
 *
 * Copyright 2003-2009, Roland Mas
 * Copyright 2004, GForge, LLC
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

global $gfplugins;
require_once $gfplugins.'scmccase/common/CCasePlugin.class.php' ;

$CCasePluginObject = new CCasePlugin ;

register_plugin ($CCasePluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
