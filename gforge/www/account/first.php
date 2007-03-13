<?php
/**
 * Welcome page
 *
 * This is the page user is redirerected to after first site login
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
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

site_user_header(array('title'=>sprintf(_('Welcome to %1$s'), $sys_name)));

printf(_('<p>You are now a registered user on %1$s, the online development environment for Open Source projects.</p><p>As a registered user, you can participate fully in the activities on the site. You may now post messages to the project message forums, post bugs for software in %1$s, sign on as a project developer, or even start your own project.</p><p>Enjoy the site, and please provide us with feedback on ways that we can improve %1$s.</p><p>--the %1$s staff.</p>'), $sys_name);

site_user_footer(array());

?>
