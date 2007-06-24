<?php
/** External authentication via LDAP for Gforge
 * Copyright 2003 Roland Mas <lolando@debian.org>
 * Copyright 2004 Roland Mas <roland@gnurandal.com> 
 *                The Gforge Group, LLC <http://gforgegroup.com/>
 *
 * This file is not part of Gforge
 *
 * This plugin, like Gforge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once ($GLOBALS['sys_plugins_path'].'/ldapextauth/include/LdapExtAuthPlugin.class.php') ;

$LdapExtAuthPluginObject = new LdapExtAuthPlugin ;

register_plugin ($LdapExtAuthPluginObject) ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
