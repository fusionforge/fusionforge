<?php
/**
 * User's bookmark Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2014, Stéphane-Eymeric Bredthauer
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/bookmarks.php';

site_user_header(array("title"=>_('Add a new Bookmark')));

$bookmark_url = trim(getStringFromRequest('bookmark_url'));
$bookmark_title = trim(getStringFromRequest('bookmark_title'));

if (getStringFromRequest('submit') && $bookmark_url && $bookmark_title) {
	echo html_e('p',array(),sprintf(_('Added bookmark for <strong>%1$s</strong> with title <strong>%2$s</strong>'),	htmlspecialchars($bookmark_url), htmlspecialchars($bookmark_title)));
	bookmark_add ($bookmark_url, $bookmark_title);
	echo html_ao('p');
	echo util_make_link($bookmark_url, _('Visit the bookmarked page'),false,true);
	echo html_ac(html_ap()-1);
	echo html_ao('p');
	echo util_make_link('/my/',_('Back to your homepage'));
	echo html_ac(html_ap()-1);
} else {
	echo html_ao('form', array('action' => util_make_uri('/my/bookmark_add.php'), 'method' => 'post'));
	echo html_ao('p');
	echo html_e('label', array('for' => 'bookmark_url'), _('Bookmark URL')._(':').html_e('br'));
	echo html_e('input', array('id' => 'bookmark_url', 'required' => 'required', 'type' => 'url', 'name' => 'bookmark_url', 'value' => 'http://'));
	echo html_ac(html_ap()-1);
	echo html_ao('p');
	echo html_e('label', array('for' => 'bookmark_title'), _('Bookmark Title')._(':').html_e('br'));
	echo html_e('input', array('id' => 'bookmark_title', 'required' => 'required', 'type' => 'text', 'name' => 'bookmark_title', 'value' => ''));
	echo html_ac(html_ap()-1);
	echo html_ao('p');
	echo html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Submit')));
	echo html_ac(html_ap()-2);
}

site_user_footer();
