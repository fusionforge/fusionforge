<?php
/**
 * Set default language for not-logged-on sessions (via cookie)
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

require_once('pre.php');

setcookie('cookie_language_id',$language_id,(time()+2592000),'/','',0);
$cookie_language_id = $language_id;

echo $HTML->header(array('title'=>"Change Language"));

?>

<h2>Language Updated</h2>
<p>
Your language preference has been saved in a cookie and will be
remembered next time you visit the site.
</p>

<?php

echo $HTML->footer(array());

?>
