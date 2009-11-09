<?php
/**
 * FusionForge globalsearch plugin
 *
 * Copyright 2003-2004, GForge, LLC
 * Copyright 2007-2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

class globalSearchPlugin extends Plugin {
        function globalSearchPlugin () {
                $this->Plugin() ;
                $this->name = "globalsearch" ;
                $this->hooks[] = "site_admin_option_hook" ;
                $this->hooks[] = "features_boxes_bottom" ;
        }

        function CallHook ($hookname, $params) {
                global $G_SESSION, $HTML, $group_id;

                if ($hookname == "site_admin_option_hook") {
                        print '<li><a href="/plugins/globalsearch/edit_assoc_sites.php">'._("Admin Associated Forges").'</a></li>';
                } elseif ($hookname == "features_boxes_bottom") {
			$params['returned_text'] .= $HTML->boxMiddle(_('Associated Forges'));
			$params['returned_text'] .= $this->show_top_n_assocsites (5) ;
			$params['returned_text'] .= $this->search_box () ;
                }
        }
	
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
	
	function search_box() {
		global $gwords,$gexact,$otherfreeknowledge;
		
		$return = 'Search in other associated forges:<br />
        <form method="post" action="/plugins/globalsearch/"/>
        <input width="100%" type="text" name="gwords" value="'.$gwords.'"/>
        <input type="submit" name="Search" value="'._("Search").'" /><br/>
        <input type="checkbox" name="otherfreeknowledge" value="1"'.( $otherfreeknowledge ? ' checked' : ' unchecked' ).'>'._('Extend search to include non-software projects').'<br/>
        <input type="checkbox" name="gexact" value="1"'.( $gexact ? ' checked' : ' unchecked' ).'>'._("Require all words").'</form>';
		return $return;
	}
	
	/**
	 * show_top_n_assocsites() - Show the n top ranked associated sites
	 *
	 * @param   string  Number of associated sites to show
	 *
	 */
	function show_top_n_assocsites($num_assocsites) {
		$res_top_n_assoc = db_query_params ('
                SELECT a.title, a.link, count(*) AS numprojects 
                FROM plugin_globalsearch_assoc_site_project p, plugin_globalsearch_assoc_site a 
                WHERE p.assoc_site_id = a.assoc_site_id AND p.assoc_site_id IN 
                        (SELECT assoc_site_id FROM plugin_globalsearch_assoc_site 
                        WHERE status_id = 2 AND enabled=$1 ORDER BY rank LIMIT $2) 
                GROUP BY a.title, a.link',
						    array('t',
							  $num_assocsites));

		if (db_numrows($res_top_n_assoc) == 0) {
			return _('No stats available')." ".db_error();
		}

		$return .= "<div align=\"left\"><table>";
		while ($row_topdown = db_fetch_array($res_top_n_assoc)) {
			if ($row_topdown['numprojects'] > 0)
				$return .= "<tr><td><a href=\"$row_topdown[link]/\">";
                        $return .= $row_topdown[title]."</a></td>"; 
                        $return .= "<td><div align=\"right\">". number_format($row_topdown[numprojects], 0);
                        $return .= " projects</div></td></tr>\n";
		}
		$return .= "</div></table>";
        
		return $return;
	}

	/**
	 * stats_get_total_projects_assoc_sites() - Show the total number of projects of associated sites
	 *
	 * @param   string  Number of associated sites to show
	 *
	 */
	function stats_get_total_projects_assoc_sites() {
		$res_count = db_query_params ('SELECT count(*) AS numprojects FROM plugin_globalsearch_assoc_site_project p, plugin_globalsearch_assoc_site a WHERE p.assoc_site_id = a.assoc_site_id AND a.status_id = 2',
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
