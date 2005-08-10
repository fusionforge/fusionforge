<?php
//
//
// Copyright 1999-2000 (c) The SourceForge Crew
//
//  This is a modified version made by the Savannah Project
//  Copyright 2000, 2001, 2002 (c) Free Software Foundation
//
//  Further modified by rts for GForge
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// http://savannah.gnu.org
//
// $Id$

// This file contains the menu_* functions required by the Savannah themes.

/* The correct theme.php must be included by this point -- Geoffrey */

// Menu entry for all admin tasks when logged as site administor
function menu_site_admin() {
	global $HTML, $Language, $sys_name;
	$HTML->menuhtml_top($sys_name." ".$Language->getText('menu', 'admin'));
	$HTML->menu_entry('/admin/',$Language->getText('menu', 'admin_main_page'));
	$HTML->menu_entry('/admin/grouplist.php',$Language->getText('menu', 'admin_group_list'));
	$HTML->menu_entry('/admin/userlist.php',$Language->getText('menu', 'admin_user_list'));
	$HTML->menu_entry('/admin/approve-pending.php',$Language->getText('menu', 'admin_pending_projects'));
	$HTML->menu_entry('/news/admin/',$Language->getText('menu', 'admin_news_approval'));
	$HTML->menu_entry('/admin/massmail.php',$Language->getText('menu', 'admin_mass_mail'));
	$HTML->menu_entry('/people/admin/',$Language->getText('menu', 'admin_people_help_admin'));

	$HTML->menuhtml_bottom();

}

function menu_show_search_box() {
	global $words,$forum_id,$group_id,$atid,$exact,$type_of_search;

	// if there is no search currently, set the default
	if ( ! isset($type_of_search) ) {
		$exact = 1;
	}

	print "\t<br /><form action=\"/search/\" method=\"post\" class=\"menusearch\">\n";
	print "\t<input type=\"text\" size=\"12\" name=\"words\" value=\"$words\" />&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";
	print "\tSoftware/Group<input type=\"radio\" name=\"type_of_search\" value=\"soft\"".( $type_of_search == "soft" ? ' checked="checked"' : "" )."".( $type_of_search == "" ? ' checked="checked"' : "" )." />&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";
	print "\tPeople<input type=\"radio\" name=\"type_of_search\" value=\"people\"".( $type_of_search == "people" ? ' checked="checked"' : "" )." />&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";
	if ($atid && $group_id) {
		$group =& group_get_object($group_id);
		if ($group && is_object($group)) {
			$ath = new ArtifactTypeHtml($group,$atid);
			if ($ath && is_object($ath)) {
				print "\t".$ath->getName()."<input type=\"radio\" name=\"type_of_search\" value=\"artifact\"".( $type_of_search == "artifact" ? ' checked="checked"' : "" )." />&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";
			}
		}
	} else if ($group_id && $forum_id) {
		print "\tThis Forum<input type=\"radio\" name=\"type_of_search\" value=\"forums\"".( $type_of_search == "forums" ? ' checked="checked"' : "" )." />&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";
	}
//	print "\tRequire All Words<input type=\"checkbox\" name=\"exact\" value=\"1\"".( $exact ? " checked" : " unchecked" ).">&nbsp;&nbsp;&nbsp;&nbsp;<br />\n";

	if ( isset($forum_id) ) {
		print "\t<input type=\"hidden\" value=\"$forum_id\" name=\"forum_id\" />\n";
	}
	if ( isset($atid) ) {
		print "\t<input type=\"hidden\" value=\"$atid\" name=\"atid\" />\n";
	}
	if ( isset($group_id) ) {
		print "\t<input type=\"hidden\" value=\"$group_id\" name=\"group_id\" />\n";
	}

	print "\t<input type=\"submit\" name=\"Search\" value=\"Search\" />&nbsp;&nbsp;&nbsp;&nbsp;\n";
	print "\t</form>\n";
}

//deprecated - theme wrapper
function menuhtml_top($title) {
	/*
		Use only for the top most menu
	*/
	theme_menuhtml_top($title);
}

function menu_site_help() {
	global $HTML, $Language, $sys_name;
	$HTML->menuhtml_top($sys_name);
	$HTML->menu_entry('/', $Language->getText('menu', 'home'));
	$HTML->menu_entry('/snippet/', $Language->getText('menu', 'code_snippet'));
	if (session_loggedin()) {
		$HTML->menu_entry('/register/',$Language->getText('menu', 'new_project'));
	}
	$HTML->menu_entry('/people/',$Language->getText('menu', 'project_help_wanted'));
	$HTML->menuhtml_bottom();
}


function menu_project_info($group) {
	global $HTML, $Language;
	$project =& group_get_object($group);
	if ($project->isError()) {

	} elseif (!$project->isProject()) {

	} else {
		$HTML->menuhtml_top($project->getPublicName());
		$HTML->menu_entry('/projects/'.$project->getUnixName().'/', $Language->getText('group', 'short_summary'));
		if (user_ismember($group, 'A')) {
			// Project admin
			$HTML->menu_entry('/project/admin/?group_id='.$group, $Language->getText('group', 'short_admin'));
		}
		// Forums
		if ($project->usesForum()) {
			$HTML->menu_entry('/forum/?group_id='.$group, $Language->getText('group', 'short_forum'));
		}
		// Artifact tracking
		$HTML->menu_entry('/tracker/?group_id='.$group, $Language->getText('group', 'short_tracker'));
		// Mailing lists
		if ($project->usesMail()) {
			$HTML->menu_entry('/mail/?group_id='.$group, $Language->getText('group', 'short_mail'));
		}
		// Project Manager
		if ($project->usesPm()) {
			$HTML->menu_entry('/pm/?group_id='.$group, $Language->getText('group', 'short_pm'));
		}
		// Doc Manager
		if ($project->usesDocman()) {
			$HTML->menu_entry('/docman/?group_id='.$group, $Language->getText('group', 'short_docman'));
		}
		// Surveys
		if ($project->usesSurvey()) {
			$HTML->menu_entry('/survey/?group_id='.$group, $Language->getText('group', 'short_survey'));
		}
		//newsbytes
		if ($project->usesNews()) {
			$HTML->menu_entry('/news/?group_id='.$group, $Language->getText('group', 'short_news'));
		}
		// SCM
		if ($project->usesSCM()) {
			$HTML->menu_entry('/scm/?group_id='.$group, $Language->getText('group', 'short_scm'));
		}
		// Downloads
		$HTML->menu_entry('/project/showfiles.php?group_id='.$group, $Language->getText('group', 'short_files'));
		$HTML->menuhtml_bottom();
	}
}

function menu_search() {
	global $HTML, $Language;
	$HTML->menuhtml_top($Language->getText('menu', 'search'));
	menu_show_search_box();
	$HTML->menuhtml_bottom();
}

function menu_valid_html() {
    /*
	GLOBAL $HTML;
	$HTML->menuhtml_top(' ');
	print "<center>";
	// /check?uri= works better than  /check/referer
	$valid_server = getStringFromServer('HTTP_HOST');
	$valid_page = getStringFromServer('PHP_SELF');
	echo "<a href=\"http://validator.w3.org/check?uri=http://".$valid_server.$valid_page."\">";
	print html_image("valid-html401.png",array('width'=>'88', 'height'=>'31', 'alt'=>'Valid HTML 4.01!'));
	echo "</a>";
	print "\t</center>\n";
	$HTML->menuhtml_bottom();
*/
}

function menu_loggedin($page_title) {
	global $HTML, $Language, $sys_name;
	/*
		Show links appropriate for someone logged in, like account maintenance, etc
	*/
	$HTML->menuhtml_top($Language->getText('menu', 'logged_in_as', user_getname()));
	$HTML->menu_entry('/my/',$Language->getText('menu', 'my_personal_page'));
	$HTML->menu_entry('/account/',$Language->getText('menu', 'my_account'));
	if (!$GLOBALS['HTTP_POST_VARS']) {
		$bookmark_title = urlencode( str_replace($sys_name.': ', '', $page_title));
		$HTML->menu_entry('/my/bookmark_add.php?bookmark_url='.urlencode(getStringFromServer('REQUEST_URI')).'&amp;bookmark_title='.$bookmark_title,$Language->getText('menu', 'bookmark_page'));
	}
	$HTML->menu_entry('/account/logout.php',$Language->getText('menu', 'logout'));
	$HTML->menuhtml_bottom();
}

function menu_notloggedin() {
	global $HTML, $Language;
	$HTML->menuhtml_top('Login Status:');
	echo "<font class=\"error\">NOT LOGGED IN</font>&nbsp;&nbsp;&nbsp;<br />";
//	$HTML->menu_entry($GLOBALS['sys_home'].'faq/?group='.$GLOBALS['sys_unix_group_name'].'&amp;question=Why_to_log_in.txt','Why Log In?');
	$HTML->menu_entry('/account/login.php',$Language->getText('menu', 'login'));
	$HTML->menu_entry('/account/register.php',$Language->getText('menu', 'new_user'));
	$HTML->menuhtml_bottom();
}

function menu_print_sidebar($params) {
	if (!session_loggedin()) {
		echo menu_notloggedin();
	} else {
		echo menu_loggedin($params['title']);
	}
	// Site Admin menu added here
	if (user_ismember(1,'A')) {
		echo menu_site_admin();
	}
	echo menu_site_help();
	if ($params['group']) {
		echo menu_project_info($params['group']);
	}
	echo menu_search();
	echo menu_valid_html();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
