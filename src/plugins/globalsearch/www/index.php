<?php
/**
 * FusionForge globalsearch plugin
 *
 * Copyright 2003-2004 GForge, LLC
 * Copyright 2007-2009, Roland Mas
 * Copyright 2016, Franck Villaume - TrivialDev
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

/**
 * Parameters:
 *   $gwords     = target words to search
 *   $gexact     = 1 for search ing all words (AND), 0 - for any word (OR)
 *   $otherfreeknowledge = 1 for search in Free/Libre Knowledge Gforge Initiatives
 *   $order = "project_title" or "title"    -  criteria for ordering results: if empty or not allowed results are ordered by rank
 */

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

global $HTML;

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
        return preg_replace("/($re)/i",'<span style="background-color:pink">\1</span>',$text);
}

$HTML->header(array('title'=>_('Global Project Search'),'pagename'=>'search'));

echo "<p>";

$gwords = htmlspecialchars(trim($gwords));
$gwords = preg_replace("/\s+/", ' ', $gwords);

// show search box which will return results on
// this very page (default is to open new window)
$gsplugin = plugin_get_object('globalsearch');
echo $gsplugin->search_box();

/*
        Force them to enter at least three characters
*/

if ($gwords && (strlen($gwords) < 3)) {
        echo $HTML->warning(_('Search must be at least three characters'));
        $HTML->footer();
        exit;
}

if (!$gwords) {
        echo $HTML->information(_('Enter Your Search Words Above'));
        $HTML->footer();
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

$qpa = db_construct_qpa(array(), 'SELECT project_title, project_link, project_description, title, link FROM plugin_globalsearch_assoc_site_project, plugin_globalsearch_assoc_site WHERE plugin_globalsearch_assoc_site_project.assoc_site_id = plugin_globalsearch_assoc_site.assoc_site_id AND enabled = $1 AND status_id = 2',
			 array ('t')) ;

if ($otherfreeknowledge) {
        $qpa = db_construct_qpa($qpa, ' AND onlysw = $1', array ('f')) ;
}

$qpa = db_construct_qpa($qpa, ' AND ((') ;

$i = 0 ;
foreach ($array as $val) {
	if ($i > 0) {
		if ($gexact) {
			$qpa = db_construct_qpa($qpa, ' AND ') ;
		} else {
			$qpa = db_construct_qpa($qpa, ' OR ') ;
		}
	}
	$i++ ;
	$qpa = db_construct_qpa($qpa, 'lower(project_title) LIKE $1', array ("%$val%")) ;
}

$qpa = db_construct_qpa($qpa, ') OR (') ;

$i = 0 ;
foreach ($array as $val) {
	if ($i > 0) {
		if ($gexact) {
			$qpa = db_construct_qpa($qpa, ' AND ') ;
		} else {
			$qpa = db_construct_qpa($qpa, ' OR ') ;
		}
	}
	$i++ ;
	$qpa = db_construct_qpa($qpa, 'lower(project_description) LIKE $1', array ("%$val%")) ;
}
$qpa = db_construct_qpa($qpa, ')) ORDER BY '.$order);

$limit = 25;

$result = db_query_qpa ($qpa, $limit+1, $offset);
$rows = $rows_returned = db_numrows($result);

if (!$result || $rows < 1) {
	$no_rows = 1;
	echo "<h2>".sprintf (_('No matches found for “%s”'), $gwords)."</h2>";
	echo db_error();
} else {
	if ( $rows_returned > $limit) {
		$rows = $limit;
	}

	echo "<h3>".sprintf (_('Search results for “%s”'), $gwords)."</h3><p>\n\n";

	$title_arr = array();
	$title_arr[] = util_make_link('/plugins/'.$gsplugin->name.'/?gwords='.urlencode($gwords).'&order=project_title&gexact='.$gexact,
				       _("Project Name")) ;
        $title_arr[] = util_make_link('/plugins/'.$gsplugin->name.'/?gwords='.urlencode($gwords).'&order=project_description&gexact='.$gexact,
				       _('Description')) ;
        $title_arr[] = util_make_link('/plugins/'.$gsplugin->name.'/?gwords='.urlencode($gwords).'&order=title&gexact='.$gexact,
				       _('Forge')) ;

        echo $HTML->listTableTop($title_arr);

        for ( $i = 0; $i < $rows; $i++ ) {
                print        "<tr><td><a href=\""
                        . db_result($result, $i, 'project_link')."\" target=\"blank\">"
                        . html_image("ic/msg.png", 10, 12)."&nbsp;"
                        . highlight_target_words($array,db_result($result, $i, 'project_title'))."</a></td>
<td>".highlight_target_words($array,html_entity_decode(db_result($result,$i,'project_description')))."</td>
<td><center><a href=\"".db_result($result,$i,'link')."\" target=\"_blank\">"
                        . db_result($result,$i,'title')."</a></center></td></tr>\n";
        }

        echo $HTML->listTableBottom();

}

   // This code puts the nice next/prev.
if ( !$no_rows && ( ($rows_returned > $rows) || ($offset != 0) ) ) {

	echo "<br />\n";

	echo "<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\">\n";
	echo "<tr>\n";
	echo "\t<td align=\"left\">";
	if ($offset != 0) {
		echo "<span style=\"font-family:arial, helvetica;text-decoration: none\">";
		echo util_make_link('/plugins/'.$gsplugin->name.'/?gwords='.urlencode($gwords).'&order='.urlencode($order).'&gexact='.$gexact.'&offset='.($offset-25),
			'<strong>'._('Previous Results').'</strong>').'</span>';
	} else {
		echo "&nbsp;";
	}
	echo "</td>\n\t<td align=\"right\">";
	if ( $rows_returned > $rows) {
		echo "<span style=\"font-family:arial, helvetica;text-decoration: none\">";
		echo util_make_link('/plugins/'.$gsplugin->name.'/?gwords='.urlencode($gwords).'&order='.urlencode($order).'&gexact='.$gexact.'&offset='.($offset+25),
			'<strong>'._('Next Results').$HTML->getNextPic('', '', array("align"=>"middle")).'</strong>').'</span>';
	} else {
		echo "&nbsp;";
	}
	echo "</td>\n</tr>\n";
	echo "</table>\n";
}

$HTML->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
