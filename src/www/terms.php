<?php
/**
 * FusionForge
 *
 * Copyright 1999-2001, VA Linux Systems
 * Copyright 2002-2004, GForge, LLC
 * Copyright 2009, Roland Mas
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

require_once('env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfwww.'include/features_boxes.php';

$HTML->header(array('title'=> _('Terms of use')));
?>

<table width="100%" cellpadding="5" cellspacing="0" border="0">
<tr><td width="65%" valign="top">
	<h3><?php printf (_('%1$s Terms of Use'), forge_get_config ('forge_name')); ?></h3>
<p>

	<?php printf (_('These are the terms and conditions under which you are allowed to use the %1$s service.  They are empty by default, but the administrator(s) of the service can use this page to publish their local requirements if needed.'),
		      forge_get_config ('forge_name')) ;
; ?>

</p>

</td>

<td width="35%" valign="top">
</td></tr></table>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
