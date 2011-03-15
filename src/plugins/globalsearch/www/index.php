<?php
/**
 * FusionForge globalsearch plugin
 *
 * Copyright 2003-2004 GForge, LLC
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

/**
  *
  * Parameters:
  *   $gwords     = target words to search
  *   $gexact     = 1 for search ing all words (AND), 0 - for any word (OR)
  *   $otherfreeknowledge = 1 for search in Free/Libre Knowledge Gforge Initiatives
  *   $order = "project_title" or "title"    -  criteria for ordering results: if empty or not allowed results are ordered by rank
  *
  */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

$otherfreeknowledge = getIntFromRequest('otherfreeknowledge') ;
$gwords = getStringFromRequest('gwords');
$order = getStringFromRequest('order', 'rank');
util_ensure_value_in_set ($order, 
			  array ('rank',
				 'project_title',
				 'project_description',
				 'title')) ;
$offset = getIntFromRequest('offset');
$gexact = getStringFromRequest('gexact');

// Support for short aliases
if (!$otherfreeknowledge) {
        $otherfreeknowledge = 0;
        $onlysw = "";
}
else {
        $onlysw = "AND onlysw = 'f' ";
}

function highlight_target_words($word_array,$text) {
        if (!$text) {
                return '&nbsp;';
        }
        $re=implode($word_array,'|');
        return eregi_replace("($re)",'<span style="background-color:pink">\1</span>',$text);
}

$HTML->header(array('title'=>'Search','pagename'=>'search'));

echo "<p>";

$gwords = htmlspecialchars(trim($gwords));
$gwords = ereg_replace("[ \t]+", ' ', $gwords);

// show search box which will return results on
// this very page (default is to open new window)
$gsplugin = plugin_get_object ('globalsearch') ;
echo $gsplugin->search_box ();

/*
        Force them to enter at least three characters
*/

if ($gwords && (strlen($gwords) < 3)) {
        echo "<h2>"._("Search must be at least three characters")."</h2>";
        $HTML->footer(array());
        exit;
}

if (!$gwords) {
        echo "<br /><b>"._("Enter Your Search Words Above")."</b></p>";
        $HTML->footer(array());
        exit;
}

$no_rows = 0;

if (!$offset || $offset < 0) {
        $offset = 0;
}

/*
        Query to find projects
*/

$array = explode(" ",$gwords);

$qpa = db_construct_qpa (false, 'SELECT project_title, project_link, project_description, title, link FROM plugin_globalsearch_assoc_site_project, plugin_globalsearch_assoc_site WHERE plugin_globalsearch_assoc_site_project.assoc_site_id = plugin_globalsearch_assoc_site.assoc_site_id AND enabled = $1 AND status_id = 2',
			 array ('t')) ;

if ($otherfreeknowledge) {
        $qpa = db_construct_qpa ($qpa, ' AND onlysw = $1', array ('f')) ;
}

$qpa = db_construct_qpa ($qpa, ' AND ((') ;

$i = 0 ;
foreach ($array as $val) {
	if ($i > 0) {
		if ($gexact) {
			$qpa = db_construct_qpa ($qpa, ' AND ') ;
		} else {
			$qpa = db_construct_qpa ($qpa, ' OR ') ;
		}
	}
	$i++ ;
	
	$qpa = db_construct_qpa ($qpa, 'lower(project_title) LIKE $1', array ("%$val%")) ;
}

$qpa = db_construct_qpa ($qpa, ') OR (') ;

$i = 0 ;
foreach ($array as $val) {
	if ($i > 0) {
		if ($gexact) {
			$qpa = db_construct_qpa ($qpa, ' AND ') ;
		} else {
			$qpa = db_construct_qpa ($qpa, ' OR ') ;
		}
	}
	$i++ ;
	
	$qpa = db_construct_qpa ($qpa, 'lower(project_description) LIKE $1', array ("%$val%")) ;
}
$qpa = db_construct_qpa ($qpa, ')) ORDER BY '.$order) ;

$limit=25;

$result = db_query_qpa ($qpa, $limit+1, $offset, 'DB_SEARCH');
$rows = $rows_returned = db_numrows($result);

if (!$result || $rows < 1) {
        $no_rows = 1;
        echo "<h2>".sprintf (_('No matches found for %1$s'),
			     $gwords)."</h2>";
        echo db_error('DB_SEARCH');

} else {

        if ( $rows_returned > $limit) {
                $rows = $limit;
        }

        echo "<h3>".sprintf (_('Search results for %1$s'),
			     $gwords)."</h3><p>\n\n";

        $title_arr = array();
        $title_arr[] = util_make_link ('/plugins/globalsearch/?gwords='.urlencode($gwords).'&amp;order=project_title&amp;gexact='.$gexact,
				       _("Project Name")) ;
        $title_arr[] = util_make_link ('/plugins/globalsearch/?gwords='.urlencode($gwords).'&amp;order=project_description&amp;gexact='.$gexact,
				       _('Description')) ;
        $title_arr[] = util_make_link ('/plugins/globalsearch/?gwords='.urlencode($gwords).'&amp;order=title&amp;gexact='.$gexact,
				       _('Forge')) ;

        echo $GLOBALS['HTML']->listTableTop($title_arr);

        for ( $i = 0; $i < $rows; $i++ ) {
                if (db_result($result, $i, 'type') == 2) {
                        $what = 'foundry';
                } else {
                        $what = 'projects';
                }
                
                print        "<tr ". $HTML->boxGetAltRowStyle($i)."><td><a href=\""
                        . db_result($result, $i, 'project_link')."\" target=\"blank\">"
                        . html_image("ic/msg.png","10","12",array("border"=>"0"))."&nbsp;"
                        . highlight_target_words($array,db_result($result, $i, 'project_title'))."</a></td>
<td>".highlight_target_words($array,db_result($result,$i,'project_description'))."</td>
<td><center><a href=\"".db_result($result,$i,'link')."\" target=\"_blank\">"
                        . db_result($result,$i,'title')."</a></center></td></tr>\n";
        }

        echo $GLOBALS['HTML']->listTableBottom();

}

   // This code puts the nice next/prev.
if ( !$no_rows && ( ($rows_returned > $rows) || ($offset != 0) ) ) {

        echo "<br />\n";

        echo "<table style=\"background-color:".$HTML->COLOR_LTBACK1."\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\">\n";
        echo "<tr>\n";
        echo "\t<td align=\"left\">";
        if ($offset != 0) {
                echo "<span style=\"font-family:arial, helvetica;text-decoration: none\">";
                echo "<a href=\"/plugins/globalsearch/?gwords=".urlencode($gwords)."&amp;order=".urlencode($order)."&amp;gexact=$gexact&amp;offset=".($offset-25);
                echo "\"><strong>"._("Previous Results")."</strong></a></span>";
        } else {
                echo "&nbsp;";
        }
        echo "</td>\n\t<td align=\"right\">";
        if ( $rows_returned > $rows) {
                echo "<span style=\"font-family:arial, helvetica;text-decoration: none\">";
                echo "<a href=\"/plugins/globalsearch/?gwords=".urlencode($gwords)."&amp;order=".urlencode($order)."&amp;gexact=$gexact&amp;offset=".($offset+25);
                echo "\"><strong>"._("Next Results") . html_image("t.png","15","15",array("border"=>"0","align"=>"middle")) . "</strong></a></span>";
        } else {
                echo "&nbsp;";
        }
        echo "</td>\n</tr>\n";
        echo "</table>\n";
}

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
