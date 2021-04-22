<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2014,2021, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'include/plugins_utils.php';

/**
 * Widget_MyBookmarks
 *
 * Personal bookmarks
 */

class Widget_MyBookmarks extends Widget {
	function __construct() {
		parent::__construct('mybookmarks');
	}

	function getTitle() {
		return _('My Bookmarks');
	}

	function getContent() {
		global $HTML;
		$html_my_bookmarks = '';
		$result = db_query_params("SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where user_id=$1 ORDER BY bookmark_title",
					array(user_getid()));
		$rows = db_numrows($result);
		if (!$result || $rows < 1) {
			$html_my_bookmarks .= $HTML->warning_msg(_('You currently do not have any bookmarks saved.'));
			$html_my_bookmarks .= db_error();
		} else {
			$html_my_bookmarks .= $HTML->listTableTop();
			for ($i = 0; $i < $rows; $i++) {
				$cells = array();
				$cells[][] = util_make_link(db_result($result,$i,'bookmark_url'), db_result($result,$i,'bookmark_title'), array(), true).
						html_e('small', array(), util_make_link('/my/bookmark_edit.php?bookmark_id='.db_result($result,$i,'bookmark_id'), '['._('Edit').']'));
				$cells[] = array(util_make_link('/my/bookmark_delete.php?bookmark_id='.db_result($result,$i,'bookmark_id'),
						$HTML->getDeletePic(_('Delete'), _('Delete'), array('onClick' => 'return confirm("'._('Delete this bookmark?').'")'))),
						'style' => 'text-align:right');
				$html_my_bookmarks .= $HTML->multiTableRow(array(), $cells);
			}
			$html_my_bookmarks .= $HTML->listTableBottom();
		}
		$html_my_bookmarks .= html_e('div', array('style' => 'text-align:center; font-size:0.8em;'), util_make_link('/my/bookmark_add.php', '['._('Add a bookmark').']'));
		return $html_my_bookmarks;
	}

	function getDescription() {
		return sprintf(_('List your favorite bookmarks (your favorite pages in %s or external).'), forge_get_config('forge_name'))
			. '<br />'
			. sprintf(_('Note that in many cases %s uses URL with enough embedded information to bookmark sophisticated items like Software Map browsing, typical search in your project Bug or Task database, etc.'), forge_get_config('forge_name'))
			. '<br />'
			. _('Bookmarked items can be edited which means that both the title of the bookmark and its destination URL can be modified.');
	}
}
