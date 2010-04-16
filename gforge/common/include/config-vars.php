<?php
/**
 * FusionForge configuration variables
 *
 * Copyright 2010, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

forge_define_config_item ('forge_name', 'core', $GLOBALS['sys_name']) ;
forge_define_config_item ('user_registration_restricted', 'core', $GLOBALS['sys_user_reg_restricted']) ;
forge_define_config_item ('web_host', 'core', $GLOBALS['sys_default_domain']) ;
forge_define_config_item ('scm_tarballs_path', 'core', $GLOBALS['sys_scm_tarballs_path']) ;
forge_define_config_item ('scm_snapshots_path', 'core', $GLOBALS['sys_scm_snapshots_path']) ;
// forge_define_config_item ('', 'core', $GLOBALS['sys_']) ;

/* Long-term:
 require_once $gfcommon.'include/FusionForge.class.php';
 $forge = new FusionForge() ;
 forge_define_config_item ('forge_name', 'core', $forge->software_name) ;
 forge_define_config_item ('user_registration_restricted', 'core', false) ;
*/

?>
