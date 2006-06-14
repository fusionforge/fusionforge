<?php
/**
  *
  * SourceForge Trove Software Map
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    
require_once('www/include/trove.php');

if (!$sys_use_trove) {
	exit_disabled();
}

$form_cat = intval(getIntFromRequest('form_cat'));

// assign default. 18 is 'topic'
if (!$form_cat) {
	$form_cat = $default_trove_cat;
}

// get info about current folder
$res_trove_cat = db_query("
	SELECT *
	FROM trove_cat
	WHERE trove_cat_id='$form_cat' ORDER BY fullname");

if (db_numrows($res_trove_cat) < 1) {
	exit_error(
		$Language->getText('trove_list','invalid_category_title'),
		$Language->getText('trove_list','invalid_category_text').': '.db_error()
	);
}

$HTML->header(array('title'=>$Language->getText('trove_list','title')));

echo'
	<hr />';

$row_trove_cat = db_fetch_array($res_trove_cat);

// #####################################
// this section limits search and requeries if there are discrim elements

$discrim = getStringFromRequest('discrim');
$discrim_url = '';
$discrim_desc = '';

if ($discrim) {
	unset ($discrim_queryalias);
	unset ($discrim_queryand);
	unset ($discrim_url_b);

	// commas are ANDs
	$expl_discrim = explode(',',$discrim);

	// need one link for each "get out of this limit" links
	$discrim_url = '&discrim=';

	$lims=sizeof($expl_discrim);
	if ($lims > 6) {
		$lims=6;
	}

	// one per argument	
	for ($i=0;$i<$lims;$i++) {
		// make sure these are all ints, no url trickery
		$expl_discrim[$i] = intval($expl_discrim[$i]);

		// need one aliased table for everything
//[CB]		$discrim_queryalias .= ', trove_group_link trove_group_link_'.$i.' ';
		$discrim_queryalias .= ', trove_agg trove_agg_'.$i.' ';
		
		// need additional AND entries for aliased tables
//[CB]		$discrim_queryand .= 'AND trove_group_link_'.$i.'.trove_cat_id='
//[CB]			.$expl_discrim[$i].' AND trove_group_link_'.$i.'.group_id='
//[CB]			.'trove_group_link.group_id ';
		$discrim_queryand .= 'AND trove_agg_'.$i.'.trove_cat_id='
			.$expl_discrim[$i].' AND trove_agg_'.$i.'.group_id='
			.'trove_agg.group_id ';

		// must build query string for all urls
		if ($i==0) {
			$discrim_url .= $expl_discrim[$i];
		} else {
			$discrim_url .= ','.$expl_discrim[$i];
		}
		// must also do this for EACH "get out of this limit" links
		// convoluted logic to build urls for these, but works quickly
		for ($j=0;$j<sizeof($expl_discrim);$j++) {
			if ($i!=$j) {
				if (!$discrim_url_b[$j]) {
					$discrim_url_b[$j] = '&discrim='.$expl_discrim[$i];
				} else {
					$discrim_url_b[$j] .= ','.$expl_discrim[$i];
				}
			}
		}

	}

	// build text for top of page on what viewier is seeing
	$discrim_desc = $Language->getText('trove_list','limiting_view').':';
	
	for ($i=0;$i<sizeof($expl_discrim);$i++) {
		$discrim_desc .= '<br /> &nbsp; &nbsp; &nbsp; '
			.trove_getfullpath($expl_discrim[$i])
			.' <a href="/softwaremap/trove_list.php?form_cat='.$form_cat
			.$discrim_url_b[$i].'">['.$Language->getText('trove_list','remove_filter').']'
			.'</a>';
	}
	$discrim_desc .= "<hr />\n";
} 

// #######################################

print '<p>'. (isset($discrim_desc) ? $discrim_desc : '') . '</p>';

// ######## two column table for key on right
// first print all parent cats and current cat
print '<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr valign="top"><td>';
$folders = explode(" :: ",$row_trove_cat['fullpath']);
$folders_ids = explode(" :: ",$row_trove_cat['fullpath_ids']);
$folders_len = count($folders);
for ($i=0;$i<$folders_len;$i++) {
	for ($sp=0;$sp<($i*2);$sp++) {
		print " &nbsp; ";
	}
	echo html_image("ic/ofolder15.png",'15','13',array());
	print "&nbsp; ";
	// no anchor for current cat
	if ($folders_ids[$i] != $form_cat) {
		print '<a href="/softwaremap/trove_list.php?form_cat='
			.$folders_ids[$i].$discrim_url.'">';
	} else {
		print '<strong>';
	}
	print $folders[$i];
	if ($folders_ids[$i] != $form_cat) {
		print '</a>';
	} else {
		print '</strong>';
	}
	print "<br />\n";
}

// print subcategories
$res_sub = db_query("
	SELECT trove_cat.trove_cat_id AS trove_cat_id,
		trove_cat.fullname AS fullname,
		trove_treesums.subprojects AS subprojects
	FROM trove_cat LEFT JOIN trove_treesums USING (trove_cat_id) 
	WHERE (
		trove_treesums.limit_1=0 
		OR trove_treesums.limit_1 IS NULL
	) AND " // need no discriminators
	."trove_cat.parent='$form_cat'
	ORDER BY fullname
", -1, 0, SYS_DB_TROVE);
echo db_error();

while ($row_sub = db_fetch_array($res_sub)) {
	for ($sp=0;$sp<($folders_len*2);$sp++) {
		print " &nbsp; ";
	}
	print ('<a href="trove_list.php?form_cat='.$row_sub['trove_cat_id'].$discrim_url.'">');
	echo html_image("ic/cfolder15.png",'15','13',array());
	print ('&nbsp; '.$row_sub['fullname'].'</a> <em>('.
		$Language->getText('trove_list','projects',array($row_sub['subprojects']?$row_sub['subprojects']:'0'))
		.')</em><br />');
		
}
// ########### right column: root level
print '</td><td>';
// here we print list of root level categories, and use open folder for current
$res_rootcat = db_query("
	SELECT trove_cat_id,fullname
	FROM trove_cat
	WHERE parent=0
	AND trove_cat_id!=0
	ORDER BY fullname");
echo db_error();

print $Language->getText('trove_list','browse_by').':';
while ($row_rootcat = db_fetch_array($res_rootcat)) {
	// print open folder if current, otherwise closed
	// also make anchor if not current
	print ('<br />');
	if (($row_rootcat['trove_cat_id'] == $row_trove_cat['root_parent'])
		|| ($row_rootcat['trove_cat_id'] == $row_trove_cat['trove_cat_id'])) {
		echo html_image('ic/ofolder15.png','15','13',array());
		print ('&nbsp; <strong>'.$row_rootcat['fullname']."</strong>\n");
	} else {
		print ('<a href="/softwaremap/trove_list.php?form_cat='
			.$row_rootcat['trove_cat_id'].$discrim_url.'">');
		echo html_image('ic/cfolder15.png','15','13',array());
		print ('&nbsp; '.$row_rootcat['fullname']."\n");
		print ('</a>');
	}
}
print '</td></tr></table>';
?>
<hr />
<?php
// one listing for each project

if(!isset($discrim_queryalias)) {
	$discrim_queryalias = '';
}

if(!isset($discrim_queryand)) {
	$discrim_queryand = '';
}

$res_grp = db_query("
	SELECT * 
	FROM trove_agg
	$discrim_queryalias
	WHERE trove_agg.trove_cat_id='$form_cat'
	$discrim_queryand
	ORDER BY trove_agg.trove_cat_id ASC, trove_agg.ranking ASC
", $TROVE_HARDQUERYLIMIT, 0, SYS_DB_TROVE);
echo db_error();
$querytotalcount = db_numrows($res_grp);
	
// #################################################################
// limit/offset display

// no funny stuff with get vars

$page = getStringFromRequest('page');

if (!is_numeric($page)) {
	$page = 1;
}

// store this as a var so it can be printed later as well
$html_limit = '';
if ($querytotalcount == $TROVE_HARDQUERYLIMIT){
	$html_limit .= 'More than ';
	$html_limit .= $Language->getText('trove_list','more_than',array($querytotalcount));
	
	}
$html_limit .= $Language->getText('trove_list','number_of_projects',array($querytotalcount));

// only display pages stuff if there is more to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	$html_limit .= ' Displaying '.$TROVE_BROWSELIMIT.' per page. Projects sorted by activity ranking.<br />';

	// display all the numbers
	for ($i=1;$i<=ceil($querytotalcount/$TROVE_BROWSELIMIT);$i++) {
		$html_limit .= ' ';
		if ($page != $i) {
			$html_limit .= '<a href="/softwaremap/trove_list.php?form_cat='.$form_cat;
			$html_limit .= $discrim_url.'&page='.$i;
			$html_limit .= '">';
		} else $html_limit .= '<strong>';
		$html_limit .= '&lt;'.$i.'&gt;';
		if ($page != $i) {
			$html_limit .= '</a>';
		} else $html_limit .= '</strong>';
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
		print "$i_proj. <a href=\"/projects/". strtolower($row_grp['unix_group_name']) ."/\"><strong>"
			.htmlspecialchars($row_grp['group_name'])."</strong></a> ";
		if ($row_grp['short_description']) {
			print "- " . htmlspecialchars($row_grp['short_description']);
		}

		print '<br />&nbsp;';
		// extra description
		print '</span></td></tr><tr valign="top"><td>';
		// list all trove categories
		print trove_getcatlisting($row_grp['group_id'],1,0);

		print '</span></td>'."\n".'<td align="right">'; // now the right side of the display
		print 'Activity Percentile: <strong>'. number_format($row_grp['percentile'],2) .'</strong>';
		print '<br />Activity Ranking: <strong>'. number_format($row_grp['ranking'],2) .'</strong>';
		print '<br />Register Date: <strong>'.date($sys_datefmt,$row_grp['register_time']).'</strong>';
		print '</span></td></tr>';
/*
                if ($row_grp['jobs_count']) {
                	print '<tr><td colspan="2" align="center">'
                              .'<a href="/people/?group_id='.$row_grp['group_id'].'">[This project needs help]</a></td></td>';
                }
*/
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

$HTML->footer(array());

?>
