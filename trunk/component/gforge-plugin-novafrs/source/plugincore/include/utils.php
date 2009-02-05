<?php
/**
 * GForge Fileaion Manager
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id: fr_utils.php,v 1.7 2006/11/22 10:17:24 pascal Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	by Quentin Cregan, SourceForge 06/2000
*/

require_once ("plugins/novafrs/include/FileConfig.class.php");

/*
 * novafrs_header
 */
function novafrs_header ($title)
{
	global $group_id, $Language, $HTML;

	$project =& group_get_object ($group_id);
	if (($project == null) || (is_object ($project) == false))
	{
		exit_no_group ();
	}
	if ($project->usesPlugin ('novafrs') == false)
	{
		exit_error (dgettext ('general','error'), dgettext ('gforge-plugin-novafrs', 'turned_off'));
	}
	site_project_header (array ('title'=>$title, 'group'=>$group_id, 'toptab'=>'novafrs'));
	$config = FileConfig::getInstance ();
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novafrs', 'view_fr');
	$menu_links [] = '/plugins/novafrs/index.php?group_id=' . $group_id;
	$menu_text [] = dgettext ('gforge-plugin-novafrs', 'menuChrono');
	$menu_links [] = '/plugins/novafrs/chronos.php?group_id=' . $group_id;
	// check if the user is admin
	$is_editor = false;
	if (session_loggedin () == true)
	{
		$g =& group_get_object ($group_id); 
		$perm =& $g->getPermission (session_get_user ());
		if (($perm != null) &&  ($perm->isError () == false) && ($perm->isReleaseTechnician () == true))
		{
			$is_editor = true;
		}
		$menu_text [] = dgettext ('gforge-plugin-novafrs', 'submit_new');
		$menu_links [] = '/plugins/novafrs/card.php?group_id=' . $group_id;
	}
	if ($is_editor == true)
	{
		$menu_text [] = dgettext ('gforge-plugin-novafrs', 'copyBranch');
		$menu_links [] = '/plugins/novafrs/admin/copy.php?group_id=' . $group_id;
		$menu_text [] = dgettext ('gforge-plugin-novafrs', 'adminDir');
		if ($config->useState == true)
		{
			$menu_links [] = '/plugins/novafrs/admin/index.php?group_id=' . $group_id;
		}
		else
	{
		$menu_links [] = '/plugins/novafrs/admin/index.php?group_id=' . $group_id . '&addgroup=1';
	}
	$menu_text [] = dgettext ('gforge-plugin-novafrs', 'manageAuth');
	$menu_links [] = '/plugins/novafrs/admin/auth.php?group_id=' . $group_id;
	}
	echo $HTML->subMenu ($menu_text, $menu_links);
}

/*
 * novafrs_droplist_count
 */
function novafrs_droplist_count ($l_group_id, $language_id, $g)
{
	global $Language;

	if (session_loggedin () == true)
	{
		$perm =& $g->getPermission( session_get_user() );
		if ((isset ($perm) == false) || (is_object ($perm) == false) || ($perm->isMember () == false))
		{
			$public_flag = "dd.stateid=1";
		}
		else
		{
			$public_flag = "dd.stateid IN (1,4,5)";
		}
	}
	else
	{
		$public_flag = "dd.stateid=1";
	}
	$query = "select dd.language_id, sl.name, count(*) as count from plugin_frs_fr_groups as dg, plugin_frs_fr_data as dd, supported_languages as sl where dg.fr_group=dd.fr_group and dg.group_id='" . $l_group_id . "' and is_current='1' and " . $public_flag . " and sl.language_id=dd.language_id group by dd.language_id, sl.name";
	$gresult = db_query($query);
	if (db_numrows ($gresult) >= 1)
	{
?><form name="langchoice" action="index.php?group_id=<? echo $l_group_id; ?>" method="post"><table border="0"><tr>
	<td valign="middle"><strong><? echo dgettext ("general", "language"); ?></strong></td>
	<td valign="middle"><select name="language_id">
		<option value="*"><? echo dgettext ("gforge-plugin-novafrs", "all_languages"); ?></option>
<?
		while ($grow = db_fetch_array ($gresult))
		{
?>		<option value="<? echo $grow ["language_id"]; ?>"<? if ($language_id == $grow ["language_id"]) { ?>selected="selected"<? } ?>><? echo $grow ["name"]; ?> (<? echo $grow ["count"]; ?>)</option>
<?
		}
?>	</select></td>
	<td valign="middle"><input type="submit" value="<? echo dgettext ("general", "go"); ?>"/></td>
</tr></table></form>
<?
	}
	else
	{
		echo db_error ();
	}
}

/*
 * novafrs_get_state_box
 */
function novafrs_get_state_box ($checkedval = "xzxz")
{
	$res_states=db_query ("select * from plugin_frs_fr_states");
	echo html_build_select_box ($res_states, "stateid", $checkedval, false);
}

/*
 * novafrs_footer
 */
function novafrs_footer ()
{
   site_project_footer (array ());
}

/*
 * novafrs_display_files - Recursive function to show the files inside the groups tree
 */
function novafrs_display_files (&$nested_groups, &$file_factory, $is_editor, $stateid = 0, $from_admin = false, $parent_group = 0)
{
	global
		$selected_fr_group_id,
		$Language;

	if (!is_array($nested_groups["$parent_group"]))
	{
		return;
	}
	echo "<ul style='list-style-type: none'>";
	$child_count = count($nested_groups["$parent_group"]);
	for ($i=0; $i < $child_count; $i++) {
		$fr_group =& $nested_groups["$parent_group"][$i];
		
		// Display group and subgroups only if it has associated files
		if ($fr_group->hasFiles($nested_groups, $file_factory, $stateid)) {
			// Recursive call
			if (($fr_group->getID() == $selected_fr_group_id || $fr_group->hasSubgroup($nested_groups, $selected_fr_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				$icon = 'ofolder15.png';
			} else {
				$icon = 'cfolder15.png';
			}
			echo "<li>".html_image('ic/'.$icon,"15","13",array("border"=>"0"))." <a href='index.php?group_id=".$fr_group->Group->getID()."&selected_fr_group_id=".$fr_group->getID()."&amp;language_id=".$GLOBALS['selected_language'];
			if ($from_admin && $stateid) {	// if we're sorting by the state, pass the state as a variable
				echo "&amp;selected_stateid=".$stateid;
			}
			echo "'>".$fr_group->getName()."</a>";
				
			// display link to add a file to the current group
			//echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."new.php?group_id=".$fr_group->Group->getID()."&amp;selected_fr_group=".$fr_group->getID()."'>";
			echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."card.php?group_id=".$fr_group->Group->getID()."&amp;selected_fr_group=".$fr_group->getID()."'>";
			echo html_image('ic/addfr12.png',"12","14",array("border"=>"0"))." ";
			echo dgettext('gforge-plugin-novafrs', 'add_frs');
			echo "</a>";
			
			if (($fr_group->getID() == $selected_fr_group_id || $fr_group->hasSubgroup($nested_groups, $selected_fr_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				frman_display_files($nested_groups, $file_factory, $is_editor, $stateid, $from_admin, $fr_group->getID());
			}
		}
		
		// Display this group's files
		if (($fr_group->hasSubgroup($nested_groups, $selected_fr_group_id) || $selected_fr_group_id == $fr_group->getID()) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
			// Retrieve all the frs from this category
			if ($stateid) {
				$file_factory->setStateID($stateid);
			}
			$file_factory->setFrGroupID($fr_group->getID());
			$frs = $file_factory->getFiles();
			if (is_array($frs)) {
				$frs_count = count($frs);
				
				echo "<ul style='list-style-type: none'>";
				for ($j=0; $j < $frs_count; $j++) {
					if ($from_admin) {
						//$link = "index.php?editfr=1&amp;frid=".$frs[$j]->getID()."&amp;group_id=".$frs[$j]->Group->getID();
						$link = "../card.php?frid=".$frs[$j]->getID()."&amp;group_id=".$frs[$j]->Group->getID();
					} else {
						$link = (( $frs[$j]->isURL() ) ? $frs[$j]->getFileName() : "view.php/".$frs[$j]->Group->getID()."/".$frs[$j]->getID()."/".$frs[$j]->getFileName() );
					}
				
					echo "<li>".
							html_image('ic/frman16b.png',"20","20",array("border"=>"0")).
							" ".
							"<a href=\"".$link."\">".
							$frs[$j]->getName().
							"</a>";
				}
				echo "</ul>";
			}
		}
	}
	echo "</ul>\n";
}

/*
 * novafrs_select_box_status
 */
function novafrs_select_box_status ($array_status, $selected = null, $htmlid = null)
{
	$html = '<select name="status" ';
	if (isset ($htmlid) == true)
	{
		$html .= ' id="' . $htmlid . '"';
	}
	$html .= '>';
	foreach ($array_status as $k => $s)
	{
		$html .=  '<option value="' . $k . '"';
		if ($k == $selected)
		{
			$html .= ' selected="selected" ';
		}
		$html .= '>' . $s . '</option>';        
	}
	$html .= '</select>';
	return $html;
}

/*
 * novafrs_unixString
 */
function novafrs_unixString ($string)
{
	$string = strtr (utf8_decode ($string), "àâäîïôöùûüéèêëç/-& '", "aaaiioouuueeeec_____");
	return  htmlspecialchars (stripslashes (eregi_replace ("[^-A-Z0-9_\.]", "", $string)));
}

/*
 * novafrs_getUserRoleId
 */
function novafrs_getUserRoleId($group_id, & $LUSER){
    if( !$LUSER ) return false;
    $user_id = $LUSER->getID();
    $sql = " SELECT role_id FROM  user_group 
                WHERE user_id = $user_id
                AND group_id = $group_id ";
    
    $res = db_query( $sql );
    
    if( db_numrows( $res ) == 0 ){
        return false;
    }else{    
        $tab = db_fetch_array( $res );
        return $tab['role_id'];
    }
}

/*
 * novafrs_select_box_type
 */
function novafrs_select_box_type ($array_type, $selected = null, $htmlid = null)
{
	$html = '<select name="frtype" ';
	if (isset ($htmlid) == true)
	{
		$html .= ' id="' . $htmlid . '"';
	}
	$html .= '>';
	foreach ($array_type as $k => $s)
	{
		$html .=  '<option value="' . $k . '"';
		if ($k == $selected)
		{
			$html .= ' selected="selected" ';
		}
		$html .= '>' . $s . '</option>';        
	}
	$html .= '</select>';
	return $html;
}

/*
 * novafrs_getRoles
 */
function novafrs_getRoles ($group_id)
{
	$sql = "SELECT role_id, role_name FROM role WHERE group_id=" . $group_id;
	$result = db_query ($sql);
	if ($result === false)
	{
		return false;
	}
	$roles = array ();
	while ($v = &db_fetch_array ($result))
	{
		$roles [] = $v;
	}
	return $roles;    
}

?>
