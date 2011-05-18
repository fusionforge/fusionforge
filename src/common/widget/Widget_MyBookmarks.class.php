<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');
require_once $gfcommon.'include/plugins_utils.php';

/**
* Widget_MyBookmarks
* 
* Personal bookmarks
*/
class Widget_MyBookmarks extends Widget {
	function Widget_MyBookmarks() {
		$this->Widget('mybookmarks');
	}

	function getTitle() {
		return _("My Bookmarks");
	}

	function getContent() {
		$html_my_bookmarks = '';
		$result = db_query_params("SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where ".
		    "user_id=$1 ORDER BY bookmark_title",array( user_getid() ));
		$rows = db_numrows($result);
		if (!$result || $rows < 1) {
			$html_my_bookmarks .= '<div class="warning">'. _("You currently do not have any bookmarks saved") .'</div>';
			$html_my_bookmarks .= db_error();
		} else {
			$html_my_bookmarks .= '<table style="width:100%">';
			for ($i=0; $i<$rows; $i++) {
				$html_my_bookmarks .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>';
				$html_my_bookmarks .= '<a href="'. db_result($result,$i,'bookmark_url') .'">'. db_result($result,$i,'bookmark_title') .'</a> ';
				$html_my_bookmarks .= '<small><a href="/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">['._("Edit").']</a></small></td>';
				$html_my_bookmarks .= '<td style="text-align:right"><a href="/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id');
				$html_my_bookmarks .= '" onClick="return confirm(\''._("Delete this bookmark?").'\')">';
				$html_my_bookmarks .= '<img src="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" height="16" width="16" border="0" alt="DELETE" /></a></td></tr>';
			}
			$html_my_bookmarks .= '</table>';
		}
		$html_my_bookmarks .= '<div style="text-align:center; font-size:0.8em;"><a href="/my/bookmark_add.php">['. _("Add a bookmark") .']</a></div>';
		return $html_my_bookmarks;
	}

	function getDescription() {
		return sprintf(_('List your favorite bookmarks (your favorite pages in %1$s or external).<br />Note that in many cases %1$s uses URL with enough embedded information to bookmark sophisticated items like Software Map browsing, typical search in your project Bug or Task database, etc. <br />Bookmarked items can be edited which means that both the title of the bookmark and its destination URL can be modified.'), forge_get_config('forge_name'));
	}
}
?>
