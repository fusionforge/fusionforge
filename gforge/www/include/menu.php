<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: menu.php,v 1.186 2000/11/30 05:33:42 tperdue Exp $

/* The correct theme.php must be included by this point -- Geoffrey */

function menu_show_search_box() {
	global $words,$forum_id,$group_id,$is_bug_page,$exact,$type_of_search;

	  // if there is no search currently, set the default
	if ( ! isset($type_of_search) ) {
		$exact = 1;
	}

	print "\t<CENTER>\n";
	print "\t<FONT SIZE=\"2\">\n";
	print "\t<FORM action=\"/search/\" method=\"post\">\n";

	print "\t<SELECT name=\"type_of_search\">\n";
	if ($is_bug_page && $group_id) {
		print "\t<OPTION value=\"bugs\"".( $type_of_search == "bugs" ? " SELECTED" : "" ).">Bugs</OPTION>\n";
	} else if ($group_id && $forum_id) {
		print "\t<OPTION value=\"forums\"".( $type_of_search == "forums" ? " SELECTED" : "" ).">This Forum</OPTION>\n";
	}
	print "\t<OPTION value=\"soft\"".( $type_of_search == "soft" ? " SELECTED" : "" ).">Software/Group</OPTION>\n";
	print "\t<OPTION value=\"people\"".( $type_of_search == "people" ? " SELECTED" : "" ).">People</OPTION>\n";
	print "\t</SELECT>\n";

	print "\t<BR>\n";
	print "\t<INPUT TYPE=\"CHECKBOX\" NAME=\"exact\" VALUE=\"1\"".( $exact ? " CHECKED" : " UNCHECKED" )."> Require All Words \n";

	print "\t<BR>\n";
	if ( isset($forum_id) ) {
		print "\t<INPUT TYPE=\"HIDDEN\" VALUE=\"$forum_id\" NAME=\"forum_id\">\n";
	} 
	if ( isset($is_bug_page) ) {
		print "\t<INPUT TYPE=\"HIDDEN\" VALUE=\"$is_bug_page\" NAME=\"is_bug_page\">\n";
	}
	if ( isset($group_id) ) {
		print "\t<INPUT TYPE=\"HIDDEN\" VALUE=\"$group_id\" NAME=\"group_id\">\n";
	}

	print "\t<INPUT TYPE=\"text\" SIZE=\"12\" NAME=\"words\" VALUE=\"$words\">\n";
	print "\t<BR>\n";
	print "\t<INPUT TYPE=\"submit\" NAME=\"Search\" VALUE=\"Search\">\n";
	print "\t</FORM>\n";
}

//depricated - theme wrapper
function menuhtml_top($title) {
	/*
		Use only for the top most menu
	*/
	theme_menuhtml_top($title);
}

//deprecated - theme wrapper
function menuhtml_bottom() {
	theme_menuhtml_bottom();
}

function menu_software() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Software'); 
		$HTML->menu_entry('/softwaremap/',$Language->SOFTWARE_MAP);
		$HTML->menu_entry('/new/',$Language->NEW_RELEASES);
		$HTML->menu_entry('/mirrors/',$Language->OTHER_SITE_MIRRORS);
		$HTML->menu_entry('/snippet/',$Language->CODE_SNIPPET_LIBRARY);
	$HTML->menuhtml_bottom();
}

function menu_sourceforge() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('SourceForge');
		$HTML->menu_entry('/docman/?group_id=1','<b>'.$Language->DOCUMENTATION.'</b>');
		$HTML->menu_entry('/forum/?group_id=1',$Language->DISCUSSION_FORUMS);
		$HTML->menu_entry('/people/',$Language->PROJECT_HELP_WANTED);
		$HTML->menu_entry('/top/',$Language->TOP_PROJECTS);
		print '<P>';
		$HTML->menu_entry('/compilefarm/',$Language->COMPILE_FARM);
		print '<P>';
		$HTML->menu_entry('/contact.php',$Language->CONTACT_US);
		$HTML->menu_entry('/about.php',$Language->ABOUT_SOURCEFORGE);
	$HTML->menuhtml_bottom();
}

function menu_foundry_links() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('SourceForge Foundries');
		$HTML->menu_entry('/about_foundries.php', $Language->ABOUT_FOUNDRIES);
		echo '<P>
';
		$HTML->menu_entry('/foundry/3d/', '3D');
		$HTML->menu_entry('/foundry/games/', 'Games');
		$HTML->menu_entry('/foundry/java/', 'Java');
		$HTML->menu_entry('/foundry/printing/', 'Printing');
		$HTML->menu_entry('/foundry/storage/', 'Storage');
	$HTML->menuhtml_bottom();
}

function menu_search() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top($Language->SEARCH);
	menu_show_search_box();
	$HTML->menuhtml_bottom();
}

function menu_project($grp) {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Project: ' . group_getname($grp));
		$HTML->menu_entry('/projects/'. group_getunixname($grp) .'/',$Language->PROJECT_SUMMARY);
		print '<P>';
		$HTML->menu_entry('/project/admin/?group_id='.$grp,$Language->PROJECT_ADMIN);
	$HTML->menuhtml_bottom();
}

function menu_foundry($grp) {
	GLOBAL $HTML, $Language;
	$unix_name=strtolower(group_getunixname($grp));
	$HTML->menuhtml_top('Foundry: ' . group_getname($grp));
		$HTML->menu_entry('/foundry/'. $unix_name .'/',$Language->FOUNDRY_SUMMARY);
		print '<P>';
		$HTML->menu_entry('/foundry/'. $unix_name .'/admin/', $Language->FOUNDRY_ADMIN);
	$HTML->menuhtml_bottom();
}

function menu_foundry_guides($grp) {
	GLOBAL $HTML, $Language;
	/*
		Show list of projects in this portal
	*/
	$foundry=&group_get_object($grp);
	if (!$foundry) {
		return 'Foundry Error';
	}
	$HTML->menuhtml_top('Foundry Guides');

	echo html_dbimage($foundry->getGuideImageID()).'<BR>';

	$sql = "SELECT users.realname,users.user_id,users.user_name ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.admin_flags='A' ".
		"AND user_group.group_id='$grp'";

	$result=db_query($sql);
	$rows=db_numrows($result);

	if (!$result || $rows < 1) {
		echo 'No Projects';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$HTML->menu_entry('/users/'. db_result($result,$i,'user_name').'/', db_result($result,$i,'realname'));
		}
	}
	$HTML->menuhtml_bottom();

}

function menu_loggedin($page_title) {
	GLOBAL $HTML, $Language;
	/*
		Show links appropriate for someone logged in, like account maintenance, etc
	*/
	$HTML->menuhtml_top('Logged In: '.user_getname());
		$HTML->menu_entry('/account/logout.php',$Language->LOGOUT);
		$HTML->menu_entry('/register/',$Language->NEW_PROJECT);
		$HTML->menu_entry('/account/',$Language->ACCOUNT_MAINTENANCE);
		print '<P>';
		$HTML->menu_entry('/themes/',$Language->CHANGE_MY_THEME);
		$HTML->menu_entry('/my/',$Language->MY_PERSONAL_PAGE);

		if (!$GLOBALS['HTTP_POST_VARS']) {
			$bookmark_title = urlencode( str_replace('SourceForge: ', '', $page_title));
			print '<P>';
			$HTML->menu_entry('/my/bookmark_add.php?bookmark_url='.urlencode($GLOBALS['REQUEST_URI']).'&bookmark_title='.$bookmark_title,$Language->BOOKMARK_PAGE);
		}
	$HTML->menuhtml_bottom();
}

function menu_notloggedin() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Status:');
		echo '<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>';
		$HTML->menu_entry('/account/login.php',$Language->LOGIN);
		$HTML->menu_entry('/account/register.php',$Language->NEW_USER);
	$HTML->menuhtml_bottom();
}

/**
 *
 *  Show a form with a language pop-up box
 *
 */
function menu_language_box() {
	GLOBAL $HTML, $Language,$cookie_language_id;
	$HTML->menuhtml_top('Language:');

	//which option should be checked 
	//in the pop-up box
	if ($cookie_language_id) {
		$lang=$cookie_language_id;
	} else {
		$lang=$Language->getLanguageId();
	}

	echo '
	<!--    

		this document.write is necessary
		to prevent the ads from screwing up
		the rest of the site in netscape...

		Thanks, netscape, for your cheesy browser

	-->
	<FONT SIZE="1">
	<FORM ACTION="/account/setlang.php" METHOD="POST">
	'. eregi_replace('<select ','<select onchange="submit()" ',html_get_language_popup ($Language,'language_id',$lang)) .'
	<BR>
	<NOSCRIPT>
	<INPUT TYPE=SUBMIT NAME=SUBMIT VALUE="Change">
	</NOSCRIPT>
	</FORM></FONT>';

	$HTML->menuhtml_bottom();
}

function menu_print_sidebar($params) {
	/*
		See if this is a project or a foundry
		and show the correct nav menus
	*/

	if (!user_isloggedin()) {
		echo menu_notloggedin();
	} else {
		echo menu_loggedin($params['title']);
	}

	//search menu
	echo menu_search();

	if ($params['group']) {
		$grp=&group_get_object($params['group']);
	}
	if ($params['group'] && $grp && $grp->isProject()) {
		//this is a project page
		//sf global choices
		echo menu_project ($params['group']);
		echo menu_software();
		echo menu_sourceforge();
	} else if ($params['group'] && $grp) {
		//this is a foundry page
		echo menu_foundry_guides($params['group']);
		echo menu_foundry($params['group']);
	} else {
		echo menu_software();
		echo menu_sourceforge();
	}

	//Foundry Links
	echo menu_foundry_links();

	if (!user_isloggedin()) {
		echo menu_language_box();
	}// else {
	//echo osdn_nav_dropdown();
	//}
}
?>
