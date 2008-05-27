<?php
/**
 * GForge HTTP 404 (Document Not Found) Page
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

require_once('env.inc.php');
require_once $gfwww.'include/pre.php';    // Initial db and session library, opens session

$HTML->header(array('title'=>_('Requested Page not Found (Error 404)')));

echo "<div align=\"center\">";

echo "<h1>".util_make_link('/',_('PAGE NOT FOUND'))."</h1>";

echo "<p />";

echo $HTML->searchBox();

echo "<p /></div>";

$HTML->footer(array());

?>
