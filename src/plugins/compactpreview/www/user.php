<?php
/**
 * Copyright 2010, Sabri LABBENE, Institut TELECOM
 *
 * http://fusionforge.org/
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

require_once '../../env.inc.php';
require_once $gfwww.'include/pre.php';

global $pluginCompatPreview;
$pluginCompactPreview = plugin_get_object('compactpreview');

$username = getStringFromRequest('user');

$title = _('OSLC Compact preview of user');
echo $pluginCompatPreview->display_user_html_compact_preview($username, $title);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
