<?php
/**
 * GForge Documentaion Manager
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id: doc_utils.php,v 1.7 2006/11/22 10:17:24 pascal Exp $
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

require_once('plugins/novadoc/include/DocumentConfig.class.php');

/*
 * novadoc_header
 */
function novadoc_header ($title)
{
	global $group_id, $Language, $HTML;

	$project =& group_get_object ($group_id);
	if (($project == null) || (is_object ($project) == false))
	{
		exit_no_group ();
	}
	if ($project->usesPlugin ('novadoc') == false)
	{
		exit_error (dgettext ('general', 'error'), dgettext ('gforge-plugin-novadoc', 'turned_off'));
	}
	site_project_header (array ('title'=>$title, 'group'=>$group_id, 'toptab'=>'novadoc'));
	$config = DocumentConfig::getInstance ();
	$menu_text = array ();
	$menu_links = array ();
	$menu_text [] = dgettext ('gforge-plugin-novadoc', 'view_doc');
	$menu_links [] = '/plugins/novadoc/index.php?group_id=' . $group_id;
	$menu_text [] = dgettext ('gforge-plugin-novadoc', 'menuChrono');
	$menu_links [] ='/plugins/novadoc/chronos.php?group_id='.$group_id;
	// check if the user is admin
	$is_editor = false;
	if (session_loggedin () == true)
	{
		$g =& group_get_object ($group_id); 
		$perm =& $g->getPermission (session_get_user ());
		if (($perm != null) && ($perm->isError () == false) && ($perm->isDocEditor () == true))
		{
			$is_editor = true;
		}
		$menu_text [] = dgettext ('gforge-plugin-novadoc', 'submit_new');
		$menu_links [] = '/plugins/novadoc/card.php?group_id=' . $group_id;
	}
	if ($is_editor == true)
	{
		$menu_text [] = dgettext ('gforge-plugin-novadoc', 'copyBranch');
		$menu_links [] = '/plugins/novadoc/admin/copy.php?group_id=' . $group_id;
		$menu_text [] = dgettext ('gforge-plugin-novadoc', 'adminDir');
		if ($config->useState == true)
		{
			$menu_links [] = '/plugins/novadoc/admin/index.php?group_id=' . $group_id;
		}
		else
		{
			$menu_links [] = '/plugins/novadoc/admin/index.php?group_id=' . $group_id . '&addgroup=1';
		}
		$menu_text [] = dgettext ('gforge-plugin-novadoc', 'manageAuth');
		$menu_links [] = '/plugins/novadoc/admin/auth.php?group_id=' . $group_id;
	}
	echo $HTML->subMenu ($menu_text, $menu_links);
}

/*
 * novadoc_droplist_count
 */
function novadoc_droplist_count ($l_group_id, $language_id, $g)
{
	global $Language;

	if (session_loggedin () == true)
	{
		$perm =& $g->getPermission (session_get_user ());
		if ((isset ($perm) == false) || (is_object ($perm) == false) || ($perm->isMember () == false))
		{
			$public_flag = "dd.stateid=1";
		}
		else
		{
			$public_flag = "dd.stateid in (1,4,5)";
		}
	}
	else
	{
		$public_flag = "dd.stateid=1";
	}
	$query = "select dd.language_id, sl.name, count(*) as count from plugin_docs_doc_groups as dg, plugin_docs_doc_data as dd, supported_languages as sl where dg.doc_group=dd.doc_group and dg.group_id='" . $l_group_id . "' and is_current='1' and " . $public_flag . " and sl.language_id=dd.language_id group by dd.language_id, sl.name";
	$gresult = db_query ($query);
	if (db_numrows ($gresult) >= 1)
	{
?><form name="langchoice" action="index.php?group_id=<? echo $l_group_id; ?>" method="post"><table border="0"><tr>
	<td valign="middle"><strong><? echo dgettext ("general", "language"); ?></strong></td>
	<td valign="middle"><select name="language_id">
		<option value="*"><? echo dgettext ("gforge-plugin-novadoc", "all_languages"); ?></option>
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
 * novadoc_get_state_box
 */
function novadoc_get_state_box ($checkedval = "xzxz")
{
	$res_states = db_query ("select * from plugin_docs_doc_states");
	echo html_build_select_box ($res_states, "stateid", $checkedval, false);
}

/*
 * novadoc_footer
 */
function novadoc_footer ()
{
   site_project_footer (array ());
}

/**
 * novadoc_display_documents - Recursive function to show the documents inside the groups tree
 */
function novadoc_display_documents (&$nested_groups, &$document_factory, $is_editor, $stateid = 0, $from_admin = false, $parent_group = 0)
{
	global
		$selected_doc_group_id,
		$Language;

	if (!is_array($nested_groups["$parent_group"]))
	{
		return;
	}
	echo "<ul style='list-style-type: none'>";
	$child_count = count($nested_groups["$parent_group"]);
	for ($i=0; $i < $child_count; $i++) {
		$doc_group =& $nested_groups["$parent_group"][$i];
		
		// Display group and subgroups only if it has associated documents
		if ($doc_group->hasDocuments($nested_groups, $document_factory, $stateid)) {
			// Recursive call
			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup($nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				$icon = 'ofolder15.png';
			} else {
				$icon = 'cfolder15.png';
			}
			echo "<li>".html_image('ic/'.$icon,"15","13",array("border"=>"0"))." <a href='index.php?group_id=".$doc_group->Group->getID()."&selected_doc_group_id=".$doc_group->getID()."&amp;language_id=".$GLOBALS['selected_language'];
			if ($from_admin && $stateid) {	// if we're sorting by the state, pass the state as a variable
				echo "&amp;selected_stateid=".$stateid;
			}
			echo "'>".$doc_group->getName()."</a>";
				
			// display link to add a document to the current group
			//echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."new.php?group_id=".$doc_group->Group->getID()."&amp;selected_doc_group=".$doc_group->getID()."'>";
			echo " &nbsp;&nbsp;&nbsp;&nbsp;<a href='".($from_admin ? "../" : "")."card.php?group_id=".$doc_group->Group->getID()."&amp;selected_doc_group=".$doc_group->getID()."'>";
			echo html_image('ic/adddoc12.png',"12","14",array("border"=>"0"))." ";
			echo dgettext('gforge-plugin-novadoc', 'add_docs');
			echo "</a>";
			
			if (($doc_group->getID() == $selected_doc_group_id || $doc_group->hasSubgroup($nested_groups, $selected_doc_group_id)) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
				docman_display_documents($nested_groups, $document_factory, $is_editor, $stateid, $from_admin, $doc_group->getID());
			}
		}
		
		// Display this group's documents
		if (($doc_group->hasSubgroup($nested_groups, $selected_doc_group_id) || $selected_doc_group_id == $doc_group->getID()) && (!$stateid || $stateid == $GLOBALS['selected_stateid'])) {
			// Retrieve all the docs from this category
			if ($stateid) {
				$document_factory->setStateID($stateid);
			}
			$document_factory->setDocGroupID($doc_group->getID());
			$docs = $document_factory->getDocuments();
			if (is_array($docs)) {
				$docs_count = count($docs);
				
				echo "<ul style='list-style-type: none'>";
				for ($j=0; $j < $docs_count; $j++) {
					if ($from_admin) {
						//$link = "index.php?editdoc=1&amp;docid=".$docs[$j]->getID()."&amp;group_id=".$docs[$j]->Group->getID();
						$link = "../card.php?docid=".$docs[$j]->getID()."&amp;group_id=".$docs[$j]->Group->getID();
					} else {
						$link = (( $docs[$j]->isURL() ) ? $docs[$j]->getFileName() : "view.php/".$docs[$j]->Group->getID()."/".$docs[$j]->getID()."/".$docs[$j]->getFileName() );
					}
				
					echo "<li>".
							html_image('ic/docman16b.png',"20","20",array("border"=>"0")).
							" ".
							"<a href=\"".$link."\">".
							$docs[$j]->getName().
							"</a>";
				}
				echo "</ul>";
			}
		}
	}
	echo "</ul>\n";
}

/*
 * novadoc_select_box_status
 */
function novadoc_select_box_status ($array_status, $selected = null, $htmlid = null)
{
	$html = '<select name="status" ';
	if ($htmlid)
	{
		$html .= ' id="' . $htmlid . '"';
	}
	$html .= '>';
	foreach ($array_status as $k  => $s)
	{
		$html .=  '<option value="' . $k . '"';
		if ($k == $selected)
		{
			$html .= ' selected="selected"';
		}
		$html .= '>' . $s . '</option>';
	}
	$html .= '</select>';
	return $html;
}

/*
 * novadoc_unixString
 */
function novadoc_unixString ($string)
{
	$string = strtr (utf8_decode ($string), "àâäîïôöùûüéèêëç/-& '", "aaaiioouuueeeec_____");
	return  htmlspecialchars (stripslashes (eregi_replace ("[^-A-Z0-9_\.]", "", $string)));
}

/*
 * novadoc_getUserRoleId
 */
function novadoc_getUserRoleId ($group_id, &$LUSER)
{
	if (isset ($LUSER) == false)
	{
		return false;
	}
	$user_id = $LUSER->getID ();
	$sql = "SELECT role_id FROM user_group WHERE user_id=" . $user_id . " AND group_id=" . $group_id;
	$res = db_query ($sql);
	if (db_numrows ($res) == 0)
	{
		return false;
	}
	else
	{
		$tab = db_fetch_array ($res);
		return $tab ["role_id"];
	}
}

/*
 * novadoc_getRoles
 */
function novadoc_getRoles ($group_id)
{
	$sql = "SELECT role_id, role_name FROM role WHERE group_id=" . $group_id;
	$result = db_query ($sql);
	if($result === false)
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
