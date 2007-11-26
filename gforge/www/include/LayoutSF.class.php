<?php   
/**
 * Base layout class.
 *
 * Extends the basic Error class to add HTML functions 
 * for displaying all site dependent HTML, while allowing 
 * extendibility/overriding by themes via the Theme class.
 * 
 * Make sure browser.php is included _before_ you create an instance of this object.
 * 
 * Geoffrey Herteg, August 29, 2000
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */
require_once('menuSF.php');
function user_isloggedin(){
	return session_loggedin();
}

class LayoutSF extends Layout {

	/**
	 * The default main page content
	 */
	var $rootindex = "index_std.php";

	/**
	 * The root location for images
	 *
	 * @var		string	$imgroot
	 */
	var $imgroot = '/themes/gforge/images/';
	var $imgproj = 'images/ic/';

	// Color Constants
	/**
	 * The content background color
	 *
	 * @var		constant		$COLOR_CONTENT_BACK
	 */
	var $COLOR_CONTENT_BACK= '#FFFFFF';

	/**
	 * The background color
	 *
	 * @var		constant		$COLOR_LTBACK1
	 */
	var $COLOR_BACK= '#6C7198';

	/**
	 * The primary light background color
	 *
	 * @var		constant		$COLOR_LTBACK1
	 */
	var $COLOR_LTBACK1= '#EAECEF';

	/**
	 * The secondary light background color
	 *
	 * @var		constant		$COLOR_LTBACk2
	 */
	var $COLOR_LTBACK2= '#FAFAFA';
	
	/**
	 * The HTML box title color
	 *
	 * @var		constant		$COLOR_HTMLBOX_TITLE
	 */
	var $COLOR_HTMLBOX_TITLE = '#D1D5D7';

	/**
	 * The HTML box background color
	 *
	 * @var		constant		$COLOR_HTMLBOX_BACK
	 */
	var $COLOR_HTMLBOX_BACK = '#EAECEF';

	// Font Face Constants
	/**
	 * The content font
	 *
	 * @var		constant		$FONT_CONTENT
	 */
	var $FONT_CONTENT = 'Helvetica';

	/**
	 * The HTML box title font
	 *
	 * @var		constant		$FONT_HTMLBOX_TITLE
	 */
	var $FONT_HTMLBOX_TITLE = 'Helvetica';

	// Font Color Constants
	/**
	 * The HTML box title font color
	 *
	 * @var		constant		$FONTCOLOR_HTMLBOX_TITLE
	 */
	var $FONTCOLOR_HTMLBOX_TITLE = '#333333';

	/**
	 * The content font color
	 *
	 * @var		constant		$FONTCOLOR_CONTENT
	 */
	var $FONTCOLOR_CONTENT = '#333333';

	// Font Size Constants
	/**
	 * The font size
	 *
	 * @var		constant		$FONTSIZE
	 */
	var $FONTSIZE = 'small';

	/**
	 * The smaller font size
	 *
	 * @var		constant		$FONTSIZE_SMALLER
	 */
	var $FONTSIZE_SMALLER='x-small';

	/**
	 * The smallest font size
	 *
	 * @var		constnat		$FONTSIZE_SMALLEST
	 */
	var $FONTSIZE_SMALLEST='xx-small';

	/**
	 * The HTML box title font size
	 *
	 * @var		constant		$FONTSIZE_HTMLBOX_TITLE
	 */
	var $FONTSIZE_HTMLBOX_TITLE = 'small';

	//Define all the icons for this theme
	/**
	 * Icons array
	 *
	 * @var		array	$icons
	 */
	var $icons = array(
		'Summary'  => 'ic/Summary.png',
		'Admin'    => 'ic/Admin.png',
		'Homepage' => 'ic/Homepage.png',
		'Forums'   => 'ic/Forums.png',
		'Tracker'  => 'ic/Tracker.png',
		'Bugs'     => 'ic/Bugs.png',
		'Support'  => 'ic/Support.png',
		'Patches'  => 'ic/Patches.png',
		'Lists'    => 'ic/Lists.png',
		'Tasks'    => 'ic/Tasks.png',
		'Docs'     => 'ic/Docs.png',
		'Surveys'  => 'ic/Surveys.png',
		'News'     => 'ic/News.png',
		'SCM'      => 'ic/CVS.png',
		'Files'    => 'ic/Files.png'
		);

	/**
	 * LayoutSF() - Constructor
	 */
	function LayoutSF() {
		// Constructor for parent class...
		$this->Layout();

		//determine font for this platform
		if (browser_is_windows() && browser_is_ie()) {

			//ie needs smaller fonts
			$this->FONTSIZE='x-small';
			$this->FONTSIZE_SMALLER='xx-small';
			$this->FONTSIZE_SMALLEST='7pt';

		} else if (browser_is_windows()) {

			//netscape on wintel
			$this->FONTSIZE='small';
			$this->FONTSIZE_SMALLER='x-small';
			$this->FONTSIZE_SMALLEST='x-small';

		} else if (browser_is_mac()){

			//mac users need bigger fonts
			$this->FONTSIZE='medium';
			$this->FONTSIZE_SMALLER='small';
			$this->FONTSIZE_SMALLEST='x-small';

		} else {

			//linux and other users
			$this->FONTSIZE='small';
			$this->FONTSIZE_SMALLER='x-small';
			$this->FONTSIZE_SMALLEST='xx-small';

		}

		$this->FONTSIZE_HTMLBOX_TITLE = $this->FONTSIZE;
	}

	function boxTop($title){
		$this->box1_top($title);
	}
	/**
	 * box1_top() - Box Top, equivalent to html_box1_top()
	 *
	 * @param	string	The box top title
	 * @param	bool	Whether to echo or return the output
	 * @param	string	The box top background color
	 * @param	bool	Whether to start the first row or not
	 */
	function box1_top($title,$echoout=1,$bgcolor='',$start_first_row=1){
		if (!$bgcolor) {
			$bgcolor=$this->COLOR_HTMLBOX_BACK;
		}
		$url_image=url_image($this->imgroot . "background.png");
		$return = '<table cellspacing="1" cellpadding="5" width="100%" border="0" bgcolor="'.$this->COLOR_HTMLBOX_BACK.'">
			<tr bgcolor="'.$this->COLOR_HTMLBOX_TITLE.'" align="center">
				<td background=' . $url_image . ' colspan=2><SPAN class=titlebar>'.$title.'</SPAN></td>
			</tr>';

		//backwards compatibility hack
		//many places assumed the row would be started
		if ($start_first_row) {
			$return .= '<tr align=left bgcolor="'.$bgcolor.'">
				<td colspan=2>';
		}
		if ($echoout) {
			print $return;
		} else {
			return $return;
		}
	}

	function boxMiddle($title){
		$this->box1_middle($title);
	}
	/**
	 * box1_middle() - Box Middle, equivalent to html_box1_middle()
	 *
	 * @param	string	The box title
	 * @param	string	The box background color
	 * @param	string  The title background color
	 * @param	bool	Whether to start the first row or not
	 * @returns	Middle box HTML content
	 */
	function box1_middle($title,$bgcolor='',$title_bgcolor='',$start_first_row=1) {
		if (!$bgcolor) {
			$bgcolor=$this->COLOR_HTMLBOX_BACK;
		}
		if (!$title_bgcolor) {
			$title_bgcolor=$this->COLOR_HTMLBOX_BACK;
			$title_bgimg=url_image($this->imgroot . "background.png");
		}
		$return = '
				</td>
			</tr>
			<tr bgcolor="' . $title_bgcolor . '" align="center">
				<td background=' . $title_bgimg . ' colspan=2><SPAN class=titlebar>'.$title.'</SPAN></td>
			</tr>';

		//backwards compatibility hack
		//many places assumed the row would be finished up
		if ($start_first_row) {
			$return .= '<tr align=left bgcolor="'.$bgcolor.'">
				<td colspan=2>';
		}
		return $return;
	}

	function boxGetAltRowStyle($i){
		$this->box1_get_alt_row_style($i);
	}
	/**
	 * box1_get_alt_row_style() - Get an alternating row style for tables
	 *
	 * @param               int             Row number
	 */
	function box1_get_alt_row_style($i) {
		if ($i % 2 == 0) {
			return 'BGCOLOR="#FFFFFF"';
		} else {
			return 'BGCOLOR="' . $this->COLOR_LTBACK1 . '"';
		}
	}

	function boxBottom(){
		$this->box1_bottom();
	}
	/**
	 * box1_bottom() - Box Bottom, equivalent to html_box1_bottom()
	 *
	 * @param	bool	Whether to echo or return the output
	 */
	function box1_bottom($echoout=1) {
		$return = '
		</td>
			</tr>
	</table>
';
		if ($echoout) {
			print $return;
		} else {
			return $return;
		}
	}

	/**
	 * generic_header_start() - Start a generic HTML header
	 *
	 * @param	array	Header parameters array
	 */
	function generic_header_start($params) {

		global $G_SESSION, $sys_name;

		if (!$params['title']) {
			$params['title'] = $GLOBALS['sys_name'];
		} else {
			$params['title'] = $GLOBALS['sys_name'].": " . $params['title'];
		}
		?>

<!-- Server: <?php echo $sys_name; ?> -->
<html lang="<?php echo $Language->getLanguageCode(); ?>">
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $Language->getEncoding(); ?>">
    <TITLE><?php echo $params['title']; ?></TITLE>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['sys_urlprefix']; ?>/themes/css/gforge.css" />
	<SCRIPT language="JavaScript">
	<!--
	function help_window(helpurl) {
		HelpWin = window.open( '<?php echo ((session_issecure()) ? 'https://'.$GLOBALS['sys_default_domain'] : 'http://'.$GLOBALS['sys_default_domain'] ); ?>' + helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');
	}
	// -->
	<?php plugin_hook ("javascript",false) ; ?>
	</SCRIPT>
<?php
	}

	/**
	 * generic_header_end() - End a generic HTML header
	 *
	 * @param	array	Header parameters array
	 */
	function generic_header_end($params) {
	?>
   </HEAD>
<?php
	}

	/**
	 * generic_footer() - Display a generic HTML footer
	 *
	 * @param	array	Footer parameters array
	 */
	function generic_footer($params) {
		global $sys_name;
		echo '<P><A HREF="'.$GLOBALS['sys_urlprefix'].'/source.php?page_url='.getStringFromServer('PHP_SELF').'"><B><FONT COLOR="WHITE">Show Source</FONT></B></A><P>';

		global $IS_DEBUG,$QUERY_COUNT;
		if ($IS_DEBUG && user_isloggedin() && user_ismember(1,'A')) {
			echo "<CENTER><B><FONT COLOR=RED>Server: $sys_name</FONT ></B></CENTER>";
			echo "<CENTER><B><FONT COLOR=RED>Query Count: $QUERY_COUNT</FONT ></B></CENTER>";
			echo "<P>$GLOBALS[G_DEBUGQUERY]";
		}

		?>
<span class="center"><font face="arial, helvetica" size="1" color="#cccccc">
<? echo _('This is %1$s.  For more about it, including copyright info, see <a href="/about.php">this page</a>.'); ?>
</font></span>
</p>&nbsp;

<?php

//
//  Actual layer call must be outside of table for some reason
//

//if (!session_issecure() && !$GLOBALS['IS_DEBUG']) {
//
//echo '
//<LAYER SRC="http://sfads.osdn.com/1.html" width=468 height=60 visibility=\'hide\' '.
//'onLoad="moveToAbsolute(adlayer.pageX,adlayer.pageY); clip.height=60; clip.width=468; visibility=\'show\';"></LAYER>';
//
//}

?>

</body>
</html>
	<?php
	}

	/**
	 *	header() - "theme" top of page
	 *
	 * @param	array	Header parameters array
	 */
	function header($params) {

		$this->generic_header_start($params); 
/*



	WARNING - changing this font call can affect
	INTERNATIONALIZATION


*/


		//gets font from Language Object
		$site_fonts=$GLOBALS['Language']->getFont();

	?>

		<link rel="icon" type="image/png" href="<?php echo $GLOBALS['sys_urlprefix']; ?>/images/debian-sf-icon.png">
		<style type="text/css">
			<!--
	OL,UL,P,BODY,TD,TR,TH,FORM { font-family: <?php echo $site_fonts; ?>; font-size:<?php echo $this->FONTSIZE; ?>; color: <?php echo $this->FONTCOLOR_CONTENT ?>; }

	H1 { font-size: x-large; font-family: <?php echo $site_fonts; ?>; }
	H2 { font-size: large; font-family: <?php echo $site_fonts; ?>; }
	H3 { font-size: medium; font-family: <?php echo $site_fonts; ?>; }
	H4 { font-size: small; font-family: <?php echo $site_fonts; ?>; }
	H5 { font-size: x-small; font-family: <?php echo $site_fonts; ?>; }
	H6 { font-size: xx-small; font-family: <?php echo $site_fonts; ?>; }

	PRE,TT { font-family: courier,sans-serif }

	.prior1 { background-color: #dadada; }
	.prior2 { background-color: #dad0d0; }
	.prior3 { background-color: #dacaca; }
	.prior4 { background-color: #dac0c0; }
	.prior5 { background-color: #dababa; }
	.prior6 { background-color: #dab0b0; }
	.prior7 { background-color: #daaaaa; }
	.prior8 { background-color: #da9090; }
	.prior9 { background-color: #da8a8a; }

	SPAN.center { text-align: center }
	SPAN.boxspace { font-size: 2pt; }
	SPAN.osdn {font-size: <?php echo $this->FONTSIZE_SMALLER; ?>; font-family: <?php echo $site_fonts ?>;}
	SPAN.search {font-size: <?php echo $this->FONTSIZE_SMALLER; ?>; font-family:  <?php echo $site_fonts ?>;}
	SPAN.slogan {font-size: large; font-weight: bold; font-family: <?php echo $site_fonts; ?>;}
	SPAN.footer {font-size: <?php echo $this->FONTSIZE_SMALLER; ?>; font-family: <?php echo $site_fonts; ?>;}

	A.maintitlebar { color: #FFFFFF }
	A.maintitlebar:visited { color: #FFFFFF }

	A.sortbutton { color: #FFFFFF; text-decoration: underline; }
	A.sortbutton:visited { color: #FFFFFF; text-decoration: underline; }

	.menus { color: #6666aa; text-decoration: none; }
	.menus:visited { color: #6666aa; text-decoration: none; }

	A:link { text-decoration:none }
	A:visited { text-decoration:none }
	A:active { text-decoration:none }
	A:hover { text-decoration:underline; color:#FF0000 }

	.tabs { color: #000000; }
	.tabs:visited { color: #000000; }
	.tabs:hover { color:#FF0000; }
	.tabselect { color: #000000; font-weight: bold; }
	.tabselect:visited { font-weight: bold;}
	.tabselect:hover { color:#FF0000; font-weight: bold; }

	.titlebar { text-decoration:none; color:#000000; font-family: <?php echo $this->FONT_HTMLBOX_TITLE . ',' . $site_fonts; ?>; font-size: <?php echo $this->FONTSIZE_HTMLBOX_TITLE; ?>; font-weight: bold; }
	.develtitle { color:#000000; font-weight: bold; }
	.legallink { color:#000000; font-weight: bold; }
			-->
		</style>

 	<?php
	$this->generic_header_end($params); 
?>
<body text="#333333" link="#6666aa" alink="#aa6666" vlink="#6666aa" bgcolor="<?php echo $this->COLOR_BACK; ?>" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0" marginwidth="0" marginheight="0">

<?php

/*

	OSDN NAV BAR

*/

osdn_print_navbar();
$s = ((session_issecure()) ? 's' : '' );

?>
<br>
<!-- start page body -->
<div align="left">
<table cellpadding="0" cellspacing="0" border="0" width="99%">
	<tr>
		<td background=<?php echo url_image($this->imgroot . "tbar1.png"); ?> width="1%" height="17"><?php echo html_image($this->imgroot . "tleft1.png","17","17",array()); ?></td>
		<td background=<?php echo url_image($this->imgroot . "tbar1.png"); ?> align="center" colspan="3" width="99%"><?php echo html_image($this->imgroot . "tbar1.png","1","17",array()); ?></td>
		<td background=<?php echo url_image($this->imgroot . "tbar1.png"); ?> width="1%" height="17"><?php echo html_image($this->imgroot . "tright1.png","17","17",array()); ?></td>
	</tr>
	<tr>
		<td width="17" background=<?php echo url_image($this->imgroot . "leftbar1.png"); ?> align="left" valign="bottom"><?php echo html_image($this->imgroot . "leftbar1.png","17","25",array()); ?></td>
		<td colspan="3" bgcolor="#ffffff">
<!-- start main body cell -->

	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td width="141" background=<?php echo url_image($this->imgroot . "leftmenubg.png"); ?> bgcolor="#cfd1d4" align="left" valign="top">

	<?php
	?>

	<CENTER>
	<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/"><?php echo html_image($this->imgroot . "sflogo.png","150","150",array()); ?></A>
	</CENTER>
	<P>
	<!-- menus -->
	<?php
	//html_blankimage(1,140);
	menu_print_sidebar($params);
	?>
	<P>
	</td>

	<td width="20" background=<?php echo url_image($this->imgroot . "fade1.png"); ?> nowrap="nowrap">&nbsp;</td>
	<td valign="top" bgcolor="<?php echo $this->COLOR_CONTENT_BACK; ?>" width="99%">
	<BR>

	<?php

	if ($params['titlevals']) {
		$title =        $Language->getText($params['pagename'],'title',$params['titlevals']);
	} else {
		$title =        $Language->getText($params['pagename'],'title');
	}

	if ($params['sectionvals']) {
		$section =      $Language->getText($params['pagename'],'section',$params['sectionvals']);
	} else {
		$section =      $Language->getText($params['pagename'],'section');
	}

	if ($section) {
		print "<b>$section</b>\n";
	}

	if ($title) {
	       print "<h2>$title</h2>\n";
	}

	}

	function footer($params) {
		$s = ((session_issecure()) ? 's' : '' );
	?>
	<!-- end content -->
	<p>&nbsp;</p>
	</td>
	<td width="9" bgcolor="<?php echo $this->COLOR_CONTENT_BACK; ?>">&nbsp;
	</td>

	</tr>
	</table>
		</td>
		<td width="17" background=<?php echo url_image($this->imgroot . "rightbar1.png"); ?> align="right" valign="bottom"><?php echo html_image($this->imgroot . "rightbar1.png","17","17",array()); ?>
</td>
	</tr>
	<tr>
		<td background=<?php echo url_image($this->imgroot . "bbar1.png"); ?> height="17"><?php echo html_image($this->imgroot . "bleft1.png","17","17",array()); ?></td>
		<td background=<?php echo url_image($this->imgroot . "bbar1.png"); ?> align="center" colspan="3"><?php echo html_image($this->imgroot . "bbar1.png","1","17",array()); ?></td>
		<td background=<?php echo url_image($this->imgroot . "bbar1.png"); ?> bgcolor="#7c8188"><?php echo html_image($this->imgroot . "bright1.png","17","17",array()); ?></td>
	</tr>
</table>
</div>

<!-- themed page footer -->
<?php 
	$this->generic_footer($params);
	}


	/**
	 * beginSubMenu() - Opening a submenu.
	 *
	 * @return	string	Html to start a submenu.
	 */
	function beginSubMenu () {
		$return = '
			<p><strong>';
		return $return;
	}

	/**
	 * endSubMenu() - Closing a submenu.
	 *
	 * @return	string	Html to end a submenu.
	 */
	function endSubMenu () {
		$return = '</strong></p>';
		return $return;
	}

	/**
	 * printSubMenu() - Takes two array of titles and links and builds the contents of a menu.
	 *
	 * @param	   array   The array of titles.
	 * @param	   array   The array of title links.
	 * @return	string	Html to build a submenu.
	 */
	function printSubMenu ($title_arr,$links_arr) {
		$count=count($title_arr);
		$count--;
		for ($i=0; $i<$count; $i++) {
			$return .= '
				<a href='.$links_arr[$i].'>'.$title_arr[$i].'</a> | ';
		}
		$return .= '
				<a href='.$links_arr[$i].'>'.$title_arr[$i].'</a>';
		return $return;
	}

	/**
	 * subMenu() - Takes two array of titles and links and build a menu.
	 *
	 * @param	   array   The array of titles.
	 * @param	   array   The array of title links.
	 * @return	string	Html to build a submenu.
	 */
	function subMenu ($title_arr,$links_arr) {
		$return  = $this->beginSubMenu () ;
		$return .= $this->printSubMenu ($title_arr,$links_arr) ;
		$return .= $this->endSubMenu () ;
		return $return;
	}

	/**
	 * menuhtml_top() - HTML top menu
	 *
	 * Use only for the top most menu
	 *
	 * @param	string	Menu title
	 */
	function menuhtml_top($title) {
?>
<table cellpadding="0" cellspacing="0" border="0" width="140">
	<tr>
		<td align="left" valign="middle"><b><?php echo $title; ?></b><br></td>
	</tr>
	<tr>
		<td align="right" valign="middle">
<?php
	}


	/**
	 * menuhtml_bottom() - HTML bottom menu
	 *
	 * End the table
	 */
	function menuhtml_bottom() {
		print '
			<BR>
			</td>
		</tr>
	</table>
';
	}

	function menu_entry($link, $title) {
		print "\t".'<A class="menus" href="'.$link.'">'.$title.'</A> &nbsp;' . html_image($this->imgroot . "point1.png","7","7",array()) . '<br>';
	}

	/**
	 *	tab_entry() - Prints out the a themed tab, used by project_tabs
	 *
	 *	@param	string	Is the URL to link to
	 *	@param	string	Us the image to use (if the theme uses it)
	 *	@param	string	Is the title to use in the link tags
	 *	@param	bool	Is a boolean to test if the tab is 'selected'
	 */
	function tab_entry($url='http://localhost/', $icon='', $title='Home', $selected=0) {
		print '
		<A ';
		if ($selected){
			print 'class=tabselect ';
		} else {
			print 'class=tabs ';
		}
		print 'href="'. $url .'">' . $title . '</A>&nbsp;|&nbsp;';
	}

	/**
	 *	project_tabs() - Prints out the project tabs, contained here in case
	 *		we want to allow it to be overriden
	 *
	 *	@param	string	Is the tab currently selected
	 *	@param	string	Is the group we should look up get title info
	 *  @param	string	Any extra text to print out
	 */
	function project_tabs($toptab,$group,$extra_text='') {
		// get group info using the common result set
		$project=group_get_object($group);
		if ($project->isError()) {
			//wasn't found or some other problem
			return;
		}
		if (!$project->isProject()) {
			return;
		}

/*		print '<H2>'. $project->getPublicName() .' - ';
		// specific to where we're at
		switch ($toptab) {
			case 'home': print _('Summary'); break;
			case 'admin': print _('Admin'); break;
			case 'forums': print _('Forums'); break;
			case 'tracker': print _('Tracker'); break;
			case 'mail': print _('Lists'); break;
			case 'pm': print _('Tasks'); break;
			case 'docman': print _('Docs'); break;
			case 'surveys': print _('Surveys'); break;
			case 'scm': print _('SCM'); break;
			case 'downloads': print _('Files'); break;
			case 'news': print _('News'); break;
			case 'memberlist': print _('Developers'); break;
			default: print _('Summary'); break;
		}

		if ($extra_text) {
			print ' - '.$extra_text;
		}

		print '</H2>';
*/

		print '<P>
		<HR SIZE="1" NoShade>';

		// Summary
		$this->tab_entry('/projects/'. $project->getUnixName() .'/', $this->icons['Summary'],
			_('Summary'), $toptab == 'home');

		// Project Admin 
		$this->tab_entry('/project/admin/?group_id='. $group, $this->icons['Admin'],
			_('Admin'), $toptab == 'admin');

		// Homepage
		$this->tab_entry('http://'. $project->getHomePage(), $this->icons['Homepage'],
			_('Home Page'));

		// Forums
		if ($project->usesForum()) {
			$this->tab_entry('/forum/?group_id='.$group, $this->icons['Forums'],
				_('Forums'), $toptab == 'forums');
		}

		// Artifact Tracking
		$this->tab_entry('/tracker/?group_id='.$group, $this->icons['Tracker'],
			_('Tracker'), $toptab == 'tracker');

/*
	Messy hack but they insisted on it -

	We need to get the bug, support, and patch tracker info
	and display it here if they are public
*/
		$res=db_query("SELECT * 
			FROM artifact_group_list 
			WHERE group_id='$group'
			AND is_public='1' 
			AND datatype > 0
			ORDER BY datatype ASC");
		$rows=db_numrows($res);
//
//	Iterate through the public pre-defined trackers and add them to nav bar
//
		for ($i=0; $i<$rows; $i++) {
			if (db_result($res,$i,'datatype') == 1) {
				//bug Tracker
				$this->tab_entry('/tracker/?group_id='.$group.'&atid='.db_result($res,$i,'group_artifact_id'), 
				$this->icons['Bugs'],
				_('Bugs'), $toptab == 'bugs');
			} elseif (db_result($res,$i,'datatype') == 2) {
				//support Tracker
				$this->tab_entry('/tracker/?group_id='.$group.'&atid='.db_result($res,$i,'group_artifact_id'), 
				$this->icons['Support'],
				_('Support'), $toptab == 'support');
			} elseif (db_result($res,$i,'datatype') == 3) {
				//patch Tracker
				$this->tab_entry('/tracker/?group_id='.$group.'&atid='.db_result($res,$i,'group_artifact_id'), 
				$this->icons['Patches'],
				_('Patches'), $toptab == 'patch');
			}
		}

		// Mailing Lists
		if ($project->usesMail()) {
			$this->tab_entry('/mail/?group_id='.$group, $this->icons['Lists'], 
				_('Lists'), $toptab == 'mail');
		}

		// Project Manager
		if ($project->usesPm()) {
			$this->tab_entry('/pm/?group_id='.$group, $this->icons['Tasks'], 
				_('Tasks'), $toptab == 'pm');
		}

		// Doc Manager
		if ($project->usesDocman()) {
			$this->tab_entry('/docman/?group_id='.$group, $this->icons['Docs'], 
				_('Docs'), $toptab == 'docman');
		}

		// Surveys
		if ($project->usesSurvey()) {
			$this->tab_entry('/survey/?group_id='.$group, $this->icons['Surveys'], 
				_('Surveys'), $toptab == 'surveys');
		}

		//newsbytes
		if ($project->usesNews()) {
			$this->tab_entry('/news/?group_id='.$group, $this->icons['News'], 
				_('News'), $toptab == 'news');
		}

		// SCM
		if ($project->usesSCM()) {
			$this->tab_entry('/scm/?group_id='.$group, $this->icons['SCM'], 
				_('SCM'), $toptab == 'scm');
		}

		// Downloads
		$this->tab_entry('/project/showfiles.php?group_id='.$group, $this->icons['Files'], 
			_('Files'), $toptab == 'downloads');

		print '<HR SIZE="1" NoShade><BR>';
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
