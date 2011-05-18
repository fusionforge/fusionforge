<?php
/**
 * FusionForge globalsearch plugin
 *
 * Copyright 2003-2004, GForge, LLC
 * Copyright 2007-2009, Roland Mas
 * Copyright 2011, Franck Villaume - Capgemini
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class globalSearchPlugin extends Plugin {
	function globalSearchPlugin() {
		$this->Plugin();
		$this->name = "globalsearch";
		$this->_addHook('site_admin_option_hook');
		$this->_addHook('features_boxes_top');
	}

	function CallHook($hookname, &$params) {
		global $HTML;

		if ($hookname == "site_admin_option_hook") {
			print '<li><a href="/plugins/globalsearch/edit_assoc_sites.php">'._("Admin Associated Forges"). ' [' . _('Global Search plugin') . ']</a></li>';
		} elseif ($hookname == "features_boxes_top") {
			(isset($params['returned_text'])) ? $params['returned_text'] .= $HTML->boxTop(_('Associated Forges'), 'Associated_Forges') : $params['returned_text'] = $HTML->boxTop(_('Associated Forges'), 'Associated_Forges');
			$params['returned_text'] .= $this->show_top_n_assocsites(5);
			$params['returned_text'] .= $this->search_box();
		}
	}

	/**
	 * @return	string	html code to display
	 */
	function show_globalsearch_stats_boxes() {
		GLOBAL $HTML;

		$return = '';
		$return .= $HTML->boxTop(_("Global Search"));
		$return .= globalsearch_box();
		$return .= $HTML->boxMiddle(_("Top associated forges"));
		$return .= show_top_n_assocsites(5);
		$return .= "<div align=\"center\">".sprintf(_("Total projects in associated forges: <b>%1$d</b>"),stats_get_total_projects_assoc_sites()). "</div>";
		$return .= $HTML->boxBottom();
		return $return;
	}

	/**
	 * @return	string	html code to display
	 */
	function search_box() {
		global $HTML, $gwords, $gexact, $otherfreeknowledge;

		$return = '<form method="post" action="/plugins/globalsearch/">';
		$return .= $HTML->html_text_input_img_submit('gwords', 'magnifier.png', 'search_associated_forges', '', $gwords, _('Search associated forges'));
		$return .= $HTML->html_checkbox('otherfreeknowledge', '1', 'search_associated_forges_otherfreeknowledge', _('Extend search to include non-software projects'), $otherfreeknowledge);
		$return .= $HTML->html_checkbox('gexact', '1', 'search_associated_forges_exact', _('Require all words'), $gexact);
		$return .= '
		</form>';
		return $return;
	}

	/**
	 * show_top_n_assocsites() - Show the n top ranked associated sites
	 *
	 * @param	string	Number of associated sites to show
	 * @return	string	html code to display
	 */
	function show_top_n_assocsites($num_assocsites) {
		$res_top_n_assoc = db_query_params('
							SELECT a.title, a.link, count(*) AS numprojects
							FROM plugin_globalsearch_assoc_site_project p, plugin_globalsearch_assoc_site a
							WHERE p.assoc_site_id = a.assoc_site_id AND p.assoc_site_id IN
								(SELECT assoc_site_id FROM plugin_globalsearch_assoc_site
								WHERE status_id = 2 AND enabled=$1 ORDER BY rank LIMIT $2)
							GROUP BY a.title, a.link',
							array('t', $num_assocsites));

		if (db_numrows($res_top_n_assoc) == 0) {
			return _('No stats available')." ".db_error();
		}

		$return .= '<table summary="" class="underline-link">';
		while ($row_topdown = db_fetch_array($res_top_n_assoc)) {
			if ($row_topdown['numprojects'] > 0) {
				$return .= '<tr><td>';
				$return .= '<a href="' . $row_topdown[link] . '/">';
				$return .= $row_topdown[title] . "</a>";
				$return .= '</td>';
				$return .= '<td class="align-right">' . number_format($row_topdown[numprojects], 0);
				$return .= " projects";
				$return .= "</td></tr>\n";
			}
		}
		$return .= "</table>";

		return $return;
	}

	/**
	 * stats_get_total_projects_assoc_sites() - Show the total number of projects of associated sites
	 *
	 * @param	string	Number of associated sites to show
	 * @return	string	statistiques
	 *
	 */
	function stats_get_total_projects_assoc_sites() {
		$res_count = db_query_params('SELECT count(*) AS numprojects FROM plugin_globalsearch_assoc_site_project p, plugin_globalsearch_assoc_site a WHERE p.assoc_site_id = a.assoc_site_id AND a.status_id = 2',
						array());
		if (db_numrows($res_count) > 0) {
			$row_count = db_fetch_array($res_count);
			return $row_count['numprojects'];
		} else {
			return _('No stats available')." ".db_error();
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
