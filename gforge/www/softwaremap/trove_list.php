<?php
/**
 * FusionForge Trove Software Map
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2009, Roland Mas
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

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php'; 

// entry for hierarchy plugin
// we didn't find any other way to do it :(
plugin_hook('tree');  

require_once $gfwww.'include/trove.php';

if (!$sys_use_trove) {
	exit_disabled();
}

$form_cat = getIntFromRequest('form_cat');
$page = getIntFromRequest('page',1);


// assign default. 18 is 'topic'
if (!$form_cat) {
	$form_cat = $default_trove_cat;
}

// get info about current folder
$res_trove_cat = db_query_params ('
	SELECT *
	FROM trove_cat
	WHERE trove_cat_id=$1 ORDER BY fullname',
			array($form_cat));

if (db_numrows($res_trove_cat) < 1) {
	exit_error(
		_('Invalid Trove Category'),
		_('That Trove category does not exist').': '.db_error()
	);
}

$HTML->header(array('title'=>_('Software Map')));

$subMenuTitle = array();
$subMenuUrl = array();

if ($GLOBALS['sys_use_project_tags']) {
	$subMenuTitle[] = _('Tag cloud');
	$subMenuUrl[] = '/softwaremap/tag_cloud.php';
}

if ($GLOBALS['sys_use_trove']) {
	$subMenuTitle[] = _('Project Tree');
	$subMenuUrl[] = '/softwaremap/trove_list.php';
}

$subMenuTitle[] = _('Project List');
$subMenuUrl[] = '/softwaremap/full_list.php';

echo ($HTML->subMenu($subMenuTitle, $subMenuUrl));

echo '<div id="project-tree" class="underline-link">' . "\n";
echo '<h2>' . _('Project tree') . '</h2>' . "\n";

$row_trove_cat = db_fetch_array($res_trove_cat);

// #####################################
// this section limits search and requeries if there are discrim elements

$discrim = getStringFromRequest('discrim');
$discrim_url = '';
$discrim_desc = '';

$qpa_alias = db_construct_qpa () ;
$qpa_and = db_construct_qpa () ;

if ($discrim) {
	$discrim_url_b = array();

	// commas are ANDs
	$expl_discrim = explode(',',$discrim);

	if (sizeof($expl_discrim) > 6) {
		array_splice ($expl_discrim, 6) ;
	}

	// one per argument	
	for ($i=0;$i<sizeof($expl_discrim);$i++) {
		// make sure these are all ints, no url trickery
		$expl_discrim[$i] = intval($expl_discrim[$i]);

		// need one aliased table for everything
		$qpa_alias = db_construct_qpa ($qpa_alias,
					       ', trove_agg trove_agg_'.$i) ;
		
		// need additional AND entries for aliased tables
		$qpa_and = db_construct_qpa ($qpa_and,
					     sprintf (' AND trove_agg_%d.trove_cat_id=$%d AND trove_agg_%d.group_id=trove_agg.group_id ', $i, $i+1, $i),
					     array ($expl_discrim[$i])) ;

		$expl_discrim_b = array () ;
		for ($j=0;$j<sizeof($expl_discrim);$j++) {
			if ($i!=$j) {
				$expl_discrim_b[] = $expl_discrim[$j] ;
			}
		}
		$discrim_url_b[$i] = '&discrim=' . implode (',', $expl_discrim_b) ;

	}
	$discrim_url = '&discrim=' . implode (',', $expl_discrim);

	// build text for top of page on what viewier is seeing
	$discrim_desc = _('Now limiting view to projects in the following categories:');
	
	for ($i=0;$i<sizeof($expl_discrim);$i++) {
		$discrim_desc .= '<br /> &nbsp; &nbsp; &nbsp; '
			.trove_getfullpath($expl_discrim[$i])
			.util_make_link ('/softwaremap/trove_list.php?form_cat='.$form_cat .$discrim_url_b[$i],' ['._('Remove This Filter').']');
	}
	$discrim_desc .= "<hr />\n";
} 

// #######################################

print '<p>'. (isset($discrim_desc) ? $discrim_desc : '') . '</p>';

// ######## two column table for key on right
// first print all parent cats and current cat
print '<table summary="">' . "\n";
print '<tr>' . "\n";
print '<td id="project-tree-col1">' . "\n";

$folders = explode(" :: ",$row_trove_cat['fullpath']);
$folders_ids = explode(" :: ",$row_trove_cat['fullpath_ids']);
$folders_len = count($folders);

print "<p>";
print html_image("category.png",'32','33',array('alt'=>""));
print "&nbsp;";

for ($i=0;$i<$folders_len;$i++) {
	// no anchor for current cat
	if ($folders_ids[$i] != $form_cat) {
		print util_make_link ('/softwaremap/trove_list.php?form_cat=' .$folders_ids[$i].$discrim_url,
				      $folders[$i]
			) ;
		print "&nbsp; &gt; &nbsp;";
	} else {
		print '<strong>'.$folders[$i].'</strong>';
	}
}
print "</p>";

// print subcategories
$res_sub = db_query_params ('
	SELECT trove_cat.trove_cat_id AS trove_cat_id,
		trove_cat.fullname AS fullname,
		trove_treesums.subprojects AS subprojects
	FROM trove_cat LEFT JOIN trove_treesums USING (trove_cat_id) 
	WHERE (
		trove_treesums.limit_1=0 
		OR trove_treesums.limit_1 IS NULL
	) AND trove_cat.parent=$1
	ORDER BY fullname',
			array ($form_cat));
echo db_error();

print "<ul>";
while ($row_sub = db_fetch_array($res_sub)) {
	print "<li>";
	print '<a href="trove_list.php?form_cat=' . $row_sub['trove_cat_id'] . $discrim_url . '">';
	print $row_sub['fullname'];
	print '</a>';
	print '&nbsp;<em>(';
	print sprintf(_('%1$s projects'), $row_sub['subprojects']?$row_sub['subprojects']:'0');
	print ')</em>';
	print "</li>\n";
}
print "</ul>";
// ########### right column: root level
print "</td>\n";
print '<td id="project-tree-col2">';
// here we print list of root level categories, and use open folder for current
$res_rootcat = db_query_params ('
	SELECT trove_cat_id,fullname
	FROM trove_cat
	WHERE parent=0
	AND trove_cat_id!=0
	ORDER BY fullname',
			array ());
echo db_error();

print "<p>";
print _('Browse By').':';
print "</p> \n";

print '<ul id="project-tree-branches">';
while ($row_rootcat = db_fetch_array($res_rootcat)) {
	// print open folder if current, otherwise closed
	// also make anchor if not current
	if (($row_rootcat['trove_cat_id'] == $row_trove_cat['root_parent'])
		|| ($row_rootcat['trove_cat_id'] == $row_trove_cat['trove_cat_id'])) {
		print '<li class="current-cat">' . $row_rootcat['fullname'] . "</li>\n";			
	} else {
		
		print "<li>";
		print util_make_link ('/softwaremap/trove_list.php?form_cat=' .$row_rootcat['trove_cat_id'].$discrim_url, $row_rootcat['fullname']); 
		print "</li>\n";
	}
}
print "</ul>\n";
print "</td>\n</tr>\n</table>\n";

?>
<hr />
<?php
// one listing for each project

$qpa = db_construct_qpa () ;
$qpa = db_construct_qpa ($qpa, 'SELECT * FROM trove_agg') ;
$qpa = db_join_qpa ($qpa, $qpa_alias) ;
$qpa = db_construct_qpa ($qpa, ' WHERE trove_agg.trove_cat_id=$1', array ($form_cat)) ;
$qpa = db_join_qpa ($qpa, $qpa_and) ;
$qpa = db_construct_qpa ($qpa, ' ORDER BY trove_agg.trove_cat_id ASC, trove_agg.ranking ASC') ;
$res_grp = db_query_qpa ($qpa, $TROVE_HARDQUERYLIMIT, 0, SYS_DB_TROVE);

echo db_error();
$querytotalcount = db_numrows($res_grp);
	
// #################################################################
// limit/offset display

// store this as a var so it can be printed later as well
$html_limit = '';
if ($querytotalcount == $TROVE_HARDQUERYLIMIT){
	$html_limit .= 'More than ';
	$html_limit .= sprintf(_('More than <strong>%1$s</strong> projects in result set.'), $querytotalcount);
	
	}
$html_limit .= sprintf(ngettext('<strong>%1$s</strong> project in result set.', '<strong>%1$s</strong> projects in result set.', $querytotalcount), $querytotalcount);

// only display pages stuff if there is more to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	$html_limit .= ' Displaying '.$TROVE_BROWSELIMIT.' per page. Projects sorted by activity ranking.<br />';

	// display all the numbers
	for ($i=1;$i<=ceil($querytotalcount/$TROVE_BROWSELIMIT);$i++) {
		$html_limit .= ' ';
		$displayed_i = '&lt;'.$i.'&gt;';
		if ($page == $i) {
			$html_limit .= "<strong>$displayed_i</strong>" ;
		} else {
			$html_limit .= util_make_link ('/softwaremap/trove_list.php?form_cat='.$form_cat.$discrim_url.'&page='.$i,
						       $displayed_i
				) ;
		}
		$html_limit .= ' ';
	}
}

//$html_limit .= '</span>';

print $html_limit."<hr />\n";

// #################################################################
// print actual project listings
// note that the for loop starts at 1, not 0
for ($i_proj=1;$i_proj<=$querytotalcount;$i_proj++) {
	$row_grp = db_fetch_array($res_grp);

	// check to see if row is in page range
	if (($i_proj > (($page-1)*$TROVE_BROWSELIMIT)) && ($i_proj <= ($page*$TROVE_BROWSELIMIT))) {
		$viewthisrow = 1;
	} else {
		$viewthisrow = 0;
	}	

	if ($row_grp && $viewthisrow) {
		print '<table border="0" cellpadding="0" width="100%"><tr valign="top"><td colspan="2">';
		print "$i_proj. " ;
		print util_make_link_g ($row_grp['unix_group_name'],
					$row_grp['group_id'],
					"<strong>".htmlspecialchars($row_grp['group_name'])."</strong> ");
		if ($row_grp['short_description']) {
			print "- " . htmlspecialchars($row_grp['short_description']);
		}

		print '<br />&nbsp;';
		// extra description
		print '</span></td></tr><tr valign="top"><td>';
		// list all trove categories
		print trove_getcatlisting($row_grp['group_id'],1,0,1);
		print '</span></td>'."\n";
		print '<td style="text-align:right">'; // now the right side of the display
		if (group_get_object($row_grp['group_id'])->usesStats()) {
			print _('Activity Percentile:&nbsp;').'<strong>'. number_format($row_grp['percentile'],2) .'</strong>';
			print '<br />'._('Activity Ranking:&nbsp;').' <strong>'. number_format($row_grp['ranking'],2) .'</strong>';
		}
		print '<br />'._('Registered:&nbsp;').' <strong>'.date(_('Y-m-d H:i'),$row_grp['register_time']).'</strong>';
		print '</span></td>';
		print '</tr>';
		print '</table>';
		print '<hr />';
	} // end if for row and range chacking
}

// print bottom navigation if there are more projects to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	print $html_limit;
}

// print '<p><FONT size="-1">This listing was produced by the following query: '
//	.$query_projlist.'</FONT>';
echo '</div><!-- id="project-tree" -->' . "\n";

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
