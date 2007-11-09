<?php
/**
  *
  * SourceForge Trove Software Map
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: trove_list.php,v 1.2 2005/11/28 13:29:52 uid20157 Exp $
  *
  */

session_start();
require_once('../../env.inc.php');    
require_once('pre.php');    
require_once('www/include/trove.php');
require_once('www/docman/include/doc_utils.php');

if (!$sys_use_trove) {
	exit_disabled();
}

//we check if the user has already chosen the tree
if(isset($_GET['cat'])){
	$_SESSION['cat'] = $_GET['cat'];
}

// assign default. 18 is 'topic'
if (!isset($form_cat) || !$form_cat) {
	$form_cat = $default_trove_cat;
}

$form_cat = intval($form_cat);

// get info about current folder
$res_trove_cat = db_query("
	SELECT *
	FROM trove_cat
	WHERE trove_cat_id='$form_cat' ORDER BY fullname");

if (db_numrows($res_trove_cat) < 1) {
	exit_error(
		_('Invalid Trove Category'),
		_('That Trove category does not exist').': '.db_error()
	);
}

$HTML->header(array('title'=>_('Software Map'),'pagename'=>'softwaremap'));

//rajout fab a mettre dans head
?>
<link rel="StyleSheet" href="dtree.css" type="text/css" />
	<script type="text/javascript" src="dtree.js"></script>
<?php
//fin rajout

print '<b><a href="./softwaremap.php?cat=c">'._('By Category').'</a> | <a href="./softwaremap.php?cat=t">'._('By Tree').'</a></b>';

echo'
	<hr />';

if(@$_SESSION['cat'] != 't'){
		$row_trove_cat = db_fetch_array($res_trove_cat);
		
		// #####################################
		// this section limits search and requeries if there are discrim elements
		
		$discrim_url = '';
		$discrim_desc = '';
		
		if (isset($discrim) && $discrim) {
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
			$discrim_desc = '<span style="color:red;font-size:smaller">'._('Now limiting view to projects in the following categories').':
		</span>';
			
			for ($i=0;$i<sizeof($expl_discrim);$i++) {
				$discrim_desc .= '<br /> &nbsp; &nbsp; &nbsp; '
					.trove_getfullpath($expl_discrim[$i])
					.' <a href="softwaremap.php?form_cat='.$form_cat
					.$discrim_url_b[$i].'">['._('Remove This Filter').']'
					.'</a>';
			}
			$discrim_desc .= "<hr />\n";
		} 
		
		// #######################################
		
		print '<p>'. (isset($discrim_desc) ? $discrim_desc : '') . '</p>';
		
		// ######## two column table for key on right
		// first print all parent cats and current cat
		print '<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top"><td><span style="font-family:arial,helvetica">';
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
				print '<a href="softwaremap.php?form_cat='
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
			print ('<a href="softwaremap.php?form_cat='.$row_sub['trove_cat_id'].$discrim_url.'">');
			echo html_image("ic/cfolder15.png",'15','13',array());
			print ('&nbsp; '.$row_sub['fullname'].'</a> <em>('.
				sprintf(_('%1$s projects'), $row_sub['subprojects']?$row_sub['subprojects']:'0')
				.')</em><br />');
				
		}
		// ########### right column: root level
		print '</span></td><td><span style="font-family:arial,helvetica">';
		// here we print list of root level categories, and use open folder for current
		$res_rootcat = db_query("
			SELECT trove_cat_id,fullname
			FROM trove_cat
			WHERE parent=0
			AND trove_cat_id!=0
			ORDER BY fullname");
		echo db_error();
		
		print _('Browse By').':';
		while ($row_rootcat = db_fetch_array($res_rootcat)) {
			// print open folder if current, otherwise closed
			// also make anchor if not current
			print ('<br />');
			if (($row_rootcat['trove_cat_id'] == $row_trove_cat['root_parent'])
				|| ($row_rootcat['trove_cat_id'] == $row_trove_cat['trove_cat_id'])) {
				echo html_image('ic/ofolder15.png','15','13',array());
				print ('&nbsp; <strong>'.$row_rootcat['fullname']."</strong>\n");
			} else {
				print ('<a href="softwaremap.php?form_cat='
					.$row_rootcat['trove_cat_id'].$discrim_url.'">');
				echo html_image('ic/cfolder15.png','15','13',array());
				print ('&nbsp; '.$row_rootcat['fullname']."\n");
				print ('</a>');
			}
		}
		print '</span></td></tr></table>';
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
		
		if (!isset($page) || !is_numeric($page)) {
			$page = 1;
		}
		
		// store this as a var so it can be printed later as well
		$html_limit = '<span style="text-align:center;font-size:smaller">';
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
				if ($page != $i) {
					$html_limit .= '<a href="softwaremap.php?form_cat='.$form_cat;
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
		
		$html_limit .= '</span>';
		
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
				print '<table border="0" cellpadding="0" width="100%"><tr valign="top"><td colspan="2"><span style="font-family:arial,helvetica">';
				print '$i_proj. <a href="'.$GLOBALS['sys_urlprefix'].'/projects/'. strtolower($row_grp['unix_group_name']) .'/"><strong>'
					.htmlspecialchars($row_grp['group_name']).'</strong></a> ';
				if ($row_grp['short_description']) {
					print "- " . htmlspecialchars($row_grp['short_description']);
				}
		
				print '<br />&nbsp;';
				// extra description
				print '</span></td></tr><tr valign="top"><td><span style="font-family:arial,helvetica">';
				// list all trove categories
				print trove_getcatlisting($row_grp['group_id'],1,0);
		
				print '</span></td>'."\n".'<td align="right"><span style="font-family:arial,helvetica">'; // now the right side of the display
				print 'Activity Percentile: <strong>'. number_format($row_grp['percentile'],2) .'</strong>';
				print '<br />Activity Ranking: <strong>'. number_format($row_grp['ranking'],2) .'</strong>';
				print '<br />Register Date: <strong>'.date(_('Y-m-d H:i'),$row_grp['register_time']).'</strong>';
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
}
else {
		function build_tree() {
			global $project_name ;
			$query = "select p1.group_id as father_id,p1.unix_group_name as father_unix_name,p1.group_name as father_name,p2.group_id as son_id,p2.unix_group_name as son_unix_name,p2.group_name as son_name from groups as p1,groups as p2,plugin_projects_hierarchy where p1.group_id=plugin_projects_hierarchy.project_id and p2.group_id=plugin_projects_hierarchy.sub_project_id and plugin_projects_hierarchy.activated='t' AND plugin_projects_hierarchy.link_type='shar'";
			$res = db_query($query);
			echo db_error();
			// construction du tableau associatif
			// key = name of the father
			// value = list of sons
			while ($row = db_fetch_array($res)) {
				//$tree[$row['father_name']][] = $row['son_name'];
				$tree[$row['father_id']][] = $row['son_id'];
				//get the unix name of the project 
				$project_name[$row['father_id']][0] = $row['father_name'];
				$project_name[$row['son_id']][0] = $row['son_name'];
				$project_name[$row['father_id']][1] = $row['father_unix_name'];
				$project_name[$row['son_id']][1] = $row['son_unix_name'];
			}
			return $tree;
		}
		
		function aff_tree($tree, $lvl) {
			global $project_name ;
			global $Language ;
			
			echo "<br/>";
			$arbre = "" ;
			$cpt_pere = 0 ;
			
			while (list($key, $sons) = each($tree)) {
				//echo $key . "<br/>";
				//we build a array with id of father and son.
				//If no father --> 0
			// Really don't know why there is a warning there, and added @
				if(@!$arbre[$key] != 0){ 
					$arbre[$key] = 0 ;
				}
				$cpt_pere = $key;
				foreach ($sons as $son) {
					//echo "&nbsp;" . $son . "<br/>";
					$arbre[$son] = $cpt_pere; 
				}
				
			}
			
			echo '<table ><tr><td>';
			
			?>
			<script type="text/javascript">
				<!--
			//add files dtress.css, dtree.js et du dossier img
				d = new dTree('d');	
				d.add(0,-1,'<?php echo _('Project Tree');?>');
				<?
				reset($arbre);
				//construction automatique de l'arbre format : (num_fils, num_pere,nom,nom_unix)
				while (list($key2, $sons2) = each($arbre)) {
				print "d.add(".$key2.",".$sons2.",'".$project_name[$key2][0]."','".$GLOBALS['sys_urlprefix']."/projects/".$project_name[$key2][1]."');\n";
				}
				?>
		
				document.write(d);
		
				
			</script>
			<?php
			echo '</td></tr></table>';
		}
		
		/*function aff_node($node, $lvl) {
			for ($i = 0; $i < $lvl; ++$i) {
				echo "&nbsp;";
			}
			echo $node . "<br/>";
		}*/
		
		$tree = build_tree();
		aff_tree($tree, 0);

}
//docman_display_documents($nested_groups,$df,$is_editor);
docman_display_documents($nested_groups,$df,'');

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
