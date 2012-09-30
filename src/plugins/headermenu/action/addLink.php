<?php
/**
 * headermenu plugin
 *
 * Copyright 2012, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

global $headermenu;

session_require_global_perm('forge_admin');
$link = getStringFromRequest('link');
$description = strip_tags(getStringFromRequest('description'));
$name = strip_tags(getStringFromRequest('name'));
$linkmenu = getStringFromRequest('linkmenu');
$htmlcode = getStringFromRequest('htmlcode');

if (!empty($name) && !empty($linkmenu)) {
	switch ($linkmenu) {
		case "headermenu": {
			if (!empty($link)) {
				if (util_check_url($link)) {
					if ($headermenu->addLink($link, $name, $description, $linkmenu)) {
						$feedback = _('Task succeeded.');
						session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&feedback='.urlencode($feedback));
					}
					$error_msg = _('Task failed');
					session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
				} else {
					$error_msg = _('Provided Link is not a valid URL.');
					session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
				}
			}
			$warning_msg = _('Missing Link URL.');
			session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&warning_msg='.urlencode($warning_msg));
			break;
		}
		case "outermenu": {
			if (!empty($link)) {
				if (util_check_url($link)) {
					if ($headermenu->addLink($link, $name, $description, $linkmenu)) {
						$feedback = _('Task succeeded.');
						session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&feedback='.urlencode($feedback));
					}
					$error_msg = _('Task failed');
					session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
				} else {
					$error_msg = _('Provided Link is not a valid URL.');
					session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
				}
			}
			if (!empty($htmlcode)) {
				if ($headermenu->addLink('', $name, $description, $linkmenu, 'htmlcode', $htmlcode)) {
					$feedback = _('Task succeeded.');
					session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&feedback='.urlencode($feedback));
				}
				$error_msg = _('Task failed');
				session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&error_msg='.urlencode($error_msg));
			}
			$warning_msg = _('Missing Link URL or Html Code.');
			session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&warning_msg='.urlencode($warning_msg));
		}
	}
}
$warning_msg = _('No link to create or name missing.');
session_redirect('plugins/'.$headermenu->name.'/?type=globaladmin&warning_msg='.urlencode($warning_msg));
