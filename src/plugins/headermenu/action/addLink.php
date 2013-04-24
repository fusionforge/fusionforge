<?php
/**
 * headermenu plugin : addLink action
 *
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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
global $group_id;

$link = getStringFromRequest('link');
$description = strip_tags(getStringFromRequest('description'));
$name = strip_tags(getStringFromRequest('name'));
$linkmenu = getStringFromRequest('linkmenu');
$htmlcode = TextSanitizer::purify(getStringFromRequest('htmlcode'));
$type = getStringFromRequest('type');
$iframed = getIntFromRequest('iframeview');

$redirect_url = '/plugins/'.$headermenu->name.'/?type='.$type;
if (isset($group_id) && $group_id) {
	$redirect_url .= '&group_id='.$group_id;
} else {
	$group_id = 0;
}

if (!empty($name) && !empty($linkmenu)) {
	switch ($linkmenu) {
		case 'headermenu': {
			if (!empty($link)) {
				if (util_check_url($link)) {
					if ($headermenu->addLink($link, $name, $description, $linkmenu)) {
						$feedback = _('Task succeeded.');
						session_redirect($redirect_url.'&feedback='.urlencode($feedback));
					}
					$error_msg = _('Task failed');
					session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
				} else {
					$error_msg = _('Provided Link is not a valid URL.');
					session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
				}
			}
			$warning_msg = _('Missing Link URL.');
			session_redirect($redirect_url.'&warning_msg='.urlencode($warning_msg));
			break;
		}
		case 'outermenu':
		case 'groupmenu': {
			if (!empty($link)) {
				if (util_check_url($link)) {
					$linktype = 'url';
					if ($iframed) {
						$linktype = 'iframe';
					}
					if ($headermenu->addLink($link, $name, $description, $linkmenu, $linktype, $group_id)) {
						$feedback = _('Task succeeded.');
						session_redirect($redirect_url.'&feedback='.urlencode($feedback));
					}
					$error_msg = _('Task failed');
					session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
				} else {
					$error_msg = _('Provided Link is not a valid URL.');
					session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
				}
			}
			if (!empty($htmlcode)) {
				if ($headermenu->addLink('', $name, $description, $linkmenu, 'htmlcode', $group_id, $htmlcode)) {
					$feedback = _('Task succeeded.');
					session_redirect($redirect_url.'&feedback='.urlencode($feedback));
				}
				$error_msg = _('Task failed');
				session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
			}
			$warning_msg = _('Missing Link URL or HTML Page.');
			session_redirect($redirect_url.'&warning_msg='.urlencode($warning_msg));
		}
	}
}
$warning_msg = _('No link to create or name missing.');
session_redirect($redirect_url.'&warning_msg='.urlencode($warning_msg));
