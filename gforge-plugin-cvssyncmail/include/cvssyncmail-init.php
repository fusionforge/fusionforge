<?php

/**
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $sys_plugins_path;
require_once ($sys_plugins_path.'/cvssyncmail/common/CVSSyncMailPlugin.class') ;

$CVSSyncMailPlugin = new CVSSyncMailPlugin() ;

register_plugin ($CVSSyncMailPlugin) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
