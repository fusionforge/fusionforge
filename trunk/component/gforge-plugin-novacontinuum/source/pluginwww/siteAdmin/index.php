<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
require_once ("www/env.inc.php");

require_once ("include/pre.php");
require_once ("www/admin/admin_utils.php");
require_once ("common/include/session.php");

require_once('plugins/novacontinuum/include/services/ServicesManager.php');
$serviceManager =& ServicesManager::getInstance();

session_require (array ("group" => "1", "admin_flags" => "A"));

site_admin_header (array ("title" => dgettext ("gforge-plugin-novacontinuum", "title_site_admin")));


require_once 'plugins/novacontinuum/include/siteAdmin/controller.php';

?>

<?php

site_admin_footer (array ());
?>
