<?php
/**
 * menu.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id: menu.php,v 1.202 2001/06/27 00:14:30 jbyers Exp $
 */

require_once('www/tracker/include/ArtifactTypeHtml.class');

/**
 * menu_show_search_box() - Show search box
 *
 * @param		bool	Show box horizontally
 * @param		bool	Show box in new window
 */
function menu_show_search_box($show_horizontally=false, $new_window=true) {
	global $words,$forum_id,$group_id,$atid,$exact,$type_of_search;

	if ($new_window) {
		$new_window = ' target="_blank"';
	}

	// if there is no search currently, set the default
	if ( ! isset($type_of_search) ) {
		$exact = 1;
	}

	if ($show_horizontally) {
		print '
		<TABLE BORDER=0 CELLPADDING=0 CELLSPACING=0>
		<TR><TD>';
	}

	print '<CENTER>
		<FONT SIZE="1">
		<FORM action="/search/" method="POST"'.$new_window.'>

		<SELECT name="type_of_search">';

	if ($atid && $group_id) {
		$group =& group_get_object($group_id);
		if ($group && is_object($group)) {
			$ath = new ArtifactTypeHtml($group,$atid);
			if ($ath && is_object($ath)) {
				print '
					<OPTION value="artifact"'.( $type_of_search == 'artifact' ? ' SELECTED' : '' ).'>'. $ath->getName() .'</OPTION>';
			}
		}
	} else if ($group_id && $forum_id) {
		print '
			<OPTION value="forums"'.( $type_of_search == 'forums' ? ' SELECTED' : '' ).'>This Forum</OPTION>';
	}
	print '
		<OPTION value="soft"'.( $type_of_search == 'soft' ? ' SELECTED' : '' ).'>Software/Group</OPTION>';
	print '
		<OPTION value="people"'.( $type_of_search == 'people' ? ' SELECTED' : '' ).'>People</OPTION>';
	print '
		<OPTION value="freshmeat"'.( $type_of_search == 'freshmeat' ? ' SELECTED' : '' ).'>Freshmeat.net</OPTION>';
	print '
		</SELECT>';

	print '<BR>';
	print '
		<INPUT TYPE="CHECKBOX" NAME="exact" VALUE="1"'.( $exact ? ' CHECKED' : ' UNCHECKED' ).'> Require All Words';

	if ($show_horizontally) {
		print '</TD>'; 
	} else {
		print '<BR>';
	}
	if ( isset($forum_id) ) {
		print '
		<INPUT TYPE="HIDDEN" VALUE="'.$forum_id.'" NAME="forum_id">';
	} 
	if ( isset($group_id) ) {
		print '
		<INPUT TYPE="HIDDEN" VALUE="'.$group_id.'" NAME="group_id">';
	}
	if ( isset($atid) ) {
		print '
		<INPUT TYPE="HIDDEN" VALUE="'.$atid.'" NAME="atid">';
	}
	if ($show_horizontally) {
		print '<TD>';
	}
	print '
		<INPUT TYPE="text" SIZE="12" NAME="words" VALUE="'.$words.'">';

	if ($show_horizontally) {
		print '</TD><TD>';
	} else {
		print '<BR>';
	}
	print '<INPUT TYPE="submit" NAME="Search" VALUE="Search">';

	if ($show_horizontally) {
		print '
		</TD></TR></TABLE>';
	}
	print '</FORM></FONT>';
}

/**
 * menuhtml_top() - Theme wrapper
 * DEPRECATED
 *
 * @deprecated
 */
function menuhtml_top($title) {
	/*
		Use only for the top most menu
	*/
	theme_menuhtml_top($title);
}

/**
 * menuhtml_bottom() - Theme wrapper
 * DEPRECATED
 * 
 * @deprecated
 */
function menuhtml_bottom() {
	theme_menuhtml_bottom();
}

/**
 * menu_software() - Show the software menu.
 */
function menu_software() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Software'); 
		$HTML->menu_entry('/softwaremap/',$Language->getText('menu','software_map'));
		$HTML->menu_entry('/new/',$Language->getText('menu','new_releases'));
		// $HTML->menu_entry('/mirrors/',$Language->getText('menu','other_site_mirrors'));
		$HTML->menu_entry('/snippet/',$Language->getText('menu','code_snippet_library'));
	$HTML->menuhtml_bottom();
}

/**
 * menu_sourceforge() - Show the sourceforge menu.
 */
function menu_sourceforge() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('SourceForge');
		$HTML->menu_entry('/docman/?group_id=1','<b>'.$Language->getText('menu','documentation').'</b>');
		$HTML->menu_entry('/forum/?group_id=1',$Language->getText('menu','discussion_forums'));
		$HTML->menu_entry('/people/',$Language->getText('menu','project_help_wanted'));
		$HTML->menu_entry('/top/',$Language->getText('menu','top_projects'));
		$HTML->menu_entry('/docman/display_doc.php?docid=2352&group_id=1',$Language->getText('menu','site_status'));
		print '<P>';
		$HTML->menu_entry('http://jobs.osdn.com', 'jobs.osdn.com');
		print '<P>';
		// $HTML->menu_entry('/compilefarm/',$Language->getText('menu','compile_farm'));
		// print '<P>';
		// $HTML->menu_entry('/contact.php',$Language->getText('menu','contact_us'));
		// $HTML->menu_entry('/about.php',$Language->getText('menu','about_sourceforge'));
	$HTML->menuhtml_bottom();
}

/**
 * menu_foundry_links() - Show links to the Foundries.
 */
function menu_foundry_links() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('SourceForge Foundries');
	$HTML->menu_entry('/about_foundries.php', $Language->getText('menu','about_foundries'));
	echo '<P>';
	$HTML->menu_entry('/foundry/linuxkernel/', 'Linux Kernel');
	$HTML->menu_entry('/foundry/linuxdrivers/', 'Linux Drivers');
	$HTML->menu_entry('/foundry/3d/', '3D');
	$HTML->menu_entry('/foundry/games/', 'Games');
	$HTML->menu_entry('/foundry/java/', 'Java');
	$HTML->menu_entry('/foundry/printing/', 'Printing');
	$HTML->menu_entry('/foundry/storage/', 'Storage');
	$HTML->menuhtml_bottom();
}

/**
 * menu_search() - Show the search menu.
 */
function menu_search() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top($Language->getText('menu','search'));
	menu_show_search_box();
	$HTML->menuhtml_bottom();
}

/**
 * menu_project() - Show the project menu
 *
 * @param		string	The group name
 */
function menu_project($grp) {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Project: ' . group_getname($grp));
		$HTML->menu_entry('/projects/'. group_getunixname($grp) .'/',$Language->getText('menu','project_summary'));
		print '<P>';
		$HTML->menu_entry('/project/admin/?group_id='.$grp,$Language->getText('menu','project_admin'));
	$HTML->menuhtml_bottom();
}

function menu_site_admin() {
	GLOBAL $HTML;
	$HTML->menuhtml_top('Site Admin');
		$HTML->menu_entry('/admin/','Site Admin Home');
		$HTML->menu_entry('/admin/approve-pending.php','Approve Pending Projects');
		$HTML->menu_entry('/admin/lastlogins.php','View Last Logins');
	$HTML->menuhtml_bottom();
}

function menu_news_admin() {
	GLOBAL $HTML;
	$HTML->menuhtml_top('Site News Admin');
		$HTML->menu_entry('/news/','Site News');
		$HTML->menu_entry('/news/submit.php?group_id=' . $GLOBALS['sys_news_group'], 'Submit Site News');
		$HTML->menu_entry('/news/admin/?group_id=' . $GLOBALS['sys_news_group'], 'Approve Site News');
	$HTML->menuhtml_bottom();
}

/**
 * menu_foundry() - Show the foundry menu
 *
 * @param		string	The foundry name
 */
function menu_foundry($grp) {
	GLOBAL $HTML, $Language;
	$unix_name=strtolower(group_getunixname($grp));
	$HTML->menuhtml_top('Foundry: ' . group_getname($grp));
		$HTML->menu_entry('/foundry/'. $unix_name .'/',$Language->getText('menu','foundry_summary'));
		print '<P>';
		$HTML->menu_entry('/foundry/'. $unix_name .'/admin/', $Language->getText('menu','foundry_admin'));
	$HTML->menuhtml_bottom();
}

/**
 * menu_foundry_guides() - Show the foundry guides menu
 *
 * @param		string	The foundry name
 */
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

/**
 * menu_loggedin() - Show links appropriate for someone logged in, like account maintenance, etc
 *
 * @param		string	 The page title
 */
function menu_loggedin($page_title) {
	GLOBAL $HTML, $Language;
		
	$HTML->menuhtml_top('Logged In: '.user_getname());
		$HTML->menu_entry('/account/logout.php',$Language->getText('menu','logout'));
		$HTML->menu_entry('/register/',$Language->getText('menu','new_project'));
		$HTML->menu_entry('/account/',$Language->getText('menu','account_maintenance'));
		print '<P>';
		$HTML->menu_entry('/themes/',$Language->getText('menu','change_my_theme'));
		$HTML->menu_entry('/my/',$Language->getText('menu','my_personal_page'));

		if (!$GLOBALS['HTTP_POST_VARS']) {
			$bookmark_title = urlencode( str_replace('SourceForge: ', '', $page_title));
			print '<P>';
			$HTML->menu_entry('/my/bookmark_add.php?bookmark_url='.urlencode($GLOBALS['REQUEST_URI']).'&bookmark_title='.$bookmark_title,$Language->getText('menu','bookmark_page'));
		}
	$HTML->menuhtml_bottom();

	if (user_ismember(1, 'A')) {
		menu_site_admin();
	}		
	if (user_ismember($GLOBALS['sys_news_group'], 'A')) {
		menu_news_admin();
	}		
}

function menu_notloggedin() {
	GLOBAL $HTML, $Language;
	$HTML->menuhtml_top('Status:');
		echo '<h4><FONT COLOR="#990000">NOT LOGGED IN</h4>';
		$HTML->menu_entry('/account/login.php',$Language->getText('menu','login'));
		$HTML->menu_entry('/account/register.php',$Language->getText('menu','new_user'));
	$HTML->menuhtml_bottom();
}

/**
 * menu_language_box() - Show a form with a language pop-up box
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
	// echo menu_foundry_links();

	if (!user_isloggedin()) {
		echo menu_language_box();
	}// else {
	//echo osdn_nav_dropdown();
	//}
}
?>
