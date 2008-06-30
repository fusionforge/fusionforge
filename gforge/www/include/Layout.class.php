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
 * @version   $Id$
 */
 
require_once $gfcommon.'include/constants.php';
require_once $gfwww.'search/include/SearchManager.class.php';

class Layout extends Error {

	/**
	 * The default main page content */
	var $rootindex = 'index_std.php';

	/*
     * The root location of the theme
     * @var      string $themeroot
	 */
	 
	var $themeroot;  	 
	/**
	 * The root location for images
	 *
	 * @var		string	$imgroot
	 */

	var $imgroot;


	/**
	 * Layout() - Constructor
	 */
	function Layout() {
		
		$this->themeroot=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'];
		/* if images directory exists in theme, then use it as imgroot */
		if (file_exists ($this->themeroot.'/images')){
			$this->imgroot=util_make_url ('/themes/'.$GLOBALS['sys_theme'].'/images/');
		}
		// Constructor for parent class...
		if ( file_exists($GLOBALS['sys_custom_path'] . '/index_std.php') )
			$this->rootindex = $GLOBALS['sys_custom_path'] . '/index_std.php';
		$this->Error();
	}

	/**
	 *	headerStart() - common code for all themes
	 *
	 * @param	array	Header parameters array
	 */
	function headerStart($params) {
		if (!$params['title']) {
			$params['title'] =  $GLOBALS['sys_name'];
		} else {
			$params['title'] =  $GLOBALS['sys_name'] . ': ' . $params['title'];
		}
		print '<?xml version="1.0" encoding="utf-8"';
		?>

<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="<?php echo _('en') ?>">

  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $params['title']; ?></title>
	<link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS" href="<?php echo util_make_url ('/export/rss_sfnews.php'); ?>" type="application/rss+xml"/>
	<link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS 2.0" href="<?php echo  util_make_url ('/export/rss20_news.php'); ?>" type="application/rss+xml"/>
	<link rel="alternate" title="<?php echo $GLOBALS['sys_name']; ?> - New Projects RSS" href="<?php echo util_make_url ('/export/rss_sfprojects.php'); ?>" type="application/rss+xml"/>
	
	<?php	if (isset($GLOBALS['group_id'])) { 
			$activity = '<link rel="alternate" title="' . $GLOBALS['sys_name'] . ' - New Activity RSS" href="'. util_make_url ('/export/rss20_activity.php?group_id='.$GLOBALS['group_id']).'" type="application/rss+xml"/>';
			echo $activity;
		}
	?>
	<?php $this->headerCSS(); ?>

	<script language="JavaScript" type="text/javascript">
	<!--

	function admin_window(adminurl) {
		AdminWin = window.open( adminurl, 'AdminWindow','scrollbars=yes,resizable=yes, toolbar=yes, height=400, width=400, top=2, left=2');
		AdminWin.focus();
	}
	function help_window(helpurl) {
		HelpWin = window.open( helpurl,'HelpWindow','scrollbars=yes,resizable=yes,toolbar=no,height=400,width=400');
	}
	// -->
	<?php plugin_hook ("javascript",false) ; ?>
	</script>
</head>
<?php 
	} 
	
	function headerCSS() {
		/* check if a personalized css stylesheet exist, if yes include only
   		this stylesheet
   		new stylesheets should use the <themename>.css file
		*/
		$theme_cssfile=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css';
		if (file_exists($theme_cssfile)){
			echo '
	<link rel="stylesheet" type="text/css" href="'.util_make_url ('/themes/'.$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css').'"/>';
		} else {
		/* if this is not our case, then include the compatibility stylesheet
   		that contains all removed styles from the code and check if a
   		custom stylesheet exists. 
   		Used for compatibility with existing stylesheets
		*/
			echo '
	<link rel="stylesheet" type="text/css" href="'.util_make_url ('/themes/css/gforge-compat.css').'" />';
			$theme_cssfile=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'].'/css/theme.css';
			if (file_exists($theme_cssfile)){
				echo '
	<link rel="stylesheet" type="text/css" href="'.util_make_url ('/themes/'.$GLOBALS['sys_theme'].'/css/theme.css').'" />';
			}
		}
		plugin_hook ('cssfile',$this);
	}

	function header($params) {
		$this->headerStart($params); ?>
<body>
		<?php
		$this->bodyHeader($params);
	}

	function bodyHeader($params){
		?>
<div class="header">
<table border="0" width="100%" cellspacing="0" cellpadding="0" id="headertable">

	<tr>
		<td><a href="<?php echo util_make_url (''); ?>/"><?php echo html_image('logo.png',198,52,array('border'=>'0')); ?></a></td>
		<td><?php echo $this->searchBox(); ?></td>
		<td align="right"><?php
			if (session_loggedin()) {
				echo util_make_link ('/account/logout.php',_('Log Out'),array('class'=>'lnkutility'));
				echo util_make_link ('/account/',_('My Account'),array('class'=>'lnkutility'));
			} else {
				echo util_make_link ('/account/login.php',_('Log In'),array('class'=>'lnkutility'));
				if (!$GLOBALS['sys_user_reg_restricted']) {
					echo util_make_link ('/account/register.php',_('New Account'),array('class'=>'lnkutility'));
				}
			}
			
			$params['template'] = ' {menu}';
			plugin_hook ('headermenu', $params);
			
			echo $this->quickNav();

		?></td>
		<td>&nbsp;&nbsp;</td>
	</tr>

</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

	<tr>
		<td>&nbsp;</td>
		<td colspan="3">

<?php echo $this->outerTabs($params); ?>

		</td>
		<td>&nbsp;</td>
	</tr>

	<tr>
		<td align="left" class="toptab" width="9"><img src="<?php echo $this->imgroot; ?>tabs/topleft.png" height="9" width="9" alt="" /></td>
		<td class="toptab" width="30"><img src="<?php echo $this->imgroot; ?>clear.png" width="30" height="1" alt="" /></td>
		<td class="toptab"><img src="<?php echo $this->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
		<td class="toptab" width="30"><img src="<?php echo $this->imgroot; ?>clear.png" width="30" height="1" alt="" /></td>
		<td align="right" class="toptab" width="9"><img src="<?php echo $this->imgroot; ?>tabs/topright.png" height="9" width="9" alt="" /></td>
	</tr>

	<tr>

		<!-- Outer body row -->

		<td class="toptab"><img src="<?php echo $this->imgroot; ?>clear.png" width="10" height="1" alt="" /></td>
		<td valign="top" width="99%" class="toptab" colspan="3">

			<!-- Inner Tabs / Shell -->

			<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php


if (isset($params['group']) && $params['group']) {

			?>
			<tr>
				<td>&nbsp;</td>
				<td>
				<?php

				echo $this->projectTabs($params['toptab'],$params['group']);

				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<?php

}

?>
			<tr>
				<td align="left" class="projecttab" width="9"><img src="<?php echo $this->imgroot; ?>tabs/topleft-inner.png" height="9" width="9" alt="" /></td>
				<td class="projecttab" ><img src="<?php echo $this->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
				<td align="right" class="projecttab"  width="9"><img src="<?php echo $this->imgroot; ?>tabs/topright-inner.png" height="9" width="9" alt="" /></td>
			</tr>

			<tr>
				<td class="projecttab" ><img src="<?php echo $this->imgroot; ?>clear.png" width="10" height="1" alt="" /></td>
				<td valign="top" width="99%" class="projecttab">

	<?php

	}

	function footer($params) {

	?>

			<!-- end main body row -->


				</td>
				<td width="10" class="footer3" ><img src="<?php echo $this->imgroot; ?>clear.png" width="2" height="1" alt="" /></td>
			</tr>
			<tr>
				<td align="left" class="footer1" width="9"><img src="<?php echo $this->imgroot; ?>tabs/bottomleft-inner.png" height="11" width="11" alt="" /></td>
				<td class="footer3"><img src="<?php echo $this->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
				<td align="right" class="footer1" width="9"><img src="<?php echo $this->imgroot; ?>tabs/bottomright-inner.png" height="11" width="11" alt="" /></td>
			</tr>
			</table>

		<!-- end inner body row -->

		</td>
		<td width="10" class="footer2"><img src="<?php echo $this->imgroot; ?>clear.png" width="2" height="1" alt="" /></td>
	</tr>
	<tr>
		<td align="left" class="footer2" width="9"><img src="<?php echo $this->imgroot; ?>tabs/bottomleft.png" height="9" width="9" alt="" /></td>
		<td class="footer2" colspan="3"><img src="<?php echo $this->imgroot; ?>clear.png" width="1" height="1" alt="" /></td>
		<td align="right" class="footer2" width="9"><img src="<?php echo $this->imgroot; ?>tabs/bottomright.png" height="9" width="9" alt="" /></td>
	</tr>
</table>
<?php
		$this->footerEnd($params);
	}

	function footerEnd($params) { ?>

<!-- PLEASE LEAVE "Powered By GForge" on your site -->
<br />
<center>
<a href="http://gforge.org/"><img src="/images/pow-gforge.png" alt="Powered By GForge Collaborative Development Environment" border="0" /></a>
</center>

<?php
	global $sys_show_source;
	if ($sys_show_source) {
		echo util_make_link ('/source.php?file='.getStringFromServer('SCRIPT_NAME'),_('Show source'),array('class'=>'showsource'));
	}
?>

</body>
</div>
</html>
<?php

	}

	function getRootIndex() {
		return $this->rootindex;
	}

	/**
	 * boxTop() - Top HTML box
	 *
	 * @param   string  Box title
	 * @param   bool	Whether to echo or return the results
	 * @param   string  The box background color
	 */
	function boxTop($title) {
		return '
		<!-- Box Top Start -->

		<table cellspacing="0" cellpadding="0" width="100%" border="0" style="background:url('.$this->imgroot.'vert-grad.png)">
		<tr align="center">
			<td valign="top" align="right" width="10" style="background:url('.$this->imgroot.'box-topleft.png)"><img src="'.$this->imgroot.'clear.png" width="10" height="20" alt="" /></td>
			<td width="100%" style="background:url('.$this->imgroot.'box-grad.png)"><span class="titlebar">'.$title.'</span></td>
			<td valign="top" width="10" style="background:url('.$this->imgroot.'box-topright.png)"><img src="'.$this->imgroot.'clear.png" width="10" height="20" alt="" /></td>
		</tr>
		<tr>
			<td colspan="3">
			<table cellspacing="2" cellpadding="2" width="100%" border="0">
				<tr align="left">
					<td colspan="2">

		<!-- Box Top End -->';
	}

	/**
	 * boxMiddle() - Middle HTML box
	 *
	 * @param   string  Box title
	 * @param   string  The box background color
	 */
	function boxMiddle($title) {
		return '
		<!-- Box Middle Start -->
					</td>
				</tr>
				<tr align="center">
					<td colspan="2" style="background:url('.$this->imgroot.'box-grad.png)"><span class="titlebar">'.$title.'</span></td>
				</tr>
				<tr align="left">
					<td colspan="2">
		<!-- Box Middle End -->';
	}

	/**
	 * boxBottom() - Bottom HTML box
	 *
	 * @param   bool	Whether to echo or return the results
	 */
	function boxBottom() {
		return '
			<!-- Box Bottom Start -->
					</td>
				</tr>
			</table>
			</td>
		</tr>
		</table><br />
		<!-- Box Bottom End -->';
	}

	/**
	 * boxGetAltRowStyle() - Get an alternating row style for tables
	 *
	 * @param			   int			 Row number
	 */
	function boxGetAltRowStyle($i) {
		if ($i % 2 == 0) {
			return ' class="altRowStyleEven"';
		} else {
			return ' class="altRowStyleOdd"';
		}
	}

	/**
	 * listTableTop() - Takes an array of titles and builds the first row of a new table.
	 *
	 * @param	   array   The array of titles
	 * @param	   array   The array of title links
	 */
	function listTableTop ($title_arr,$links_arr=false) {
		$return = '
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr align="center">
	<!--		<td valign="top" align="right" width="10" style="background:url('.$this->imgroot.'box-grad.png)"><img src="'.$this->imgroot.'box-topleft.png" width="10" height="75" alt="" /></td> -->
			<td style="background:url('.$this->imgroot.'box-grad.png)">
		<table width="100%" border="0" cellspacing="1" cellpadding="2" >
			<tr class="tableheading">';
		$count=count($title_arr);
		if ($links_arr) {
			for ($i=0; $i<$count; $i++) {
				$return .= '<td>'.util_make_link ($links_arr[$i],$title_arr[$i],array('class'=>'sortbutton')).'</td>';
			}
		} else {
			for ($i=0; $i<$count; $i++) {
				$return .= '
				<td>'.$title_arr[$i].'</td>';
			}
		}
		return $return.'</tr>';
	}

	function listTableBottom() {
		return '</table></td>
			<!-- <td valign="top" align="right" width="10" style="background:url('.$this->imgroot.'box-grad.png)"><img src="'.$this->imgroot.'box-topright.png" width="10" height="75" alt="" /></td> -->
			</tr></table>';
	}

	function outerTabs($params) {
		global $sys_use_trove,$sys_use_snippet,$sys_use_people;

		$TABS_DIRS[]='/';
		$TABS_DIRS[]='/my/';
		if ($sys_use_trove) {
			$TABS_DIRS[]='/softwaremap/';
		}
		if ($sys_use_snippet) {
			$TABS_DIRS[]='/snippet/';
		}
		if ($sys_use_people) {
			$TABS_DIRS[]='/people/';
		}
		$TABS_TITLES[]=_('Home');
		$TABS_TITLES[]=_('My&nbsp;Page');
		if ($sys_use_trove) {
			$TABS_TITLES[]=_('Project&nbsp;Tree');
		}
		if ($sys_use_snippet) {
			$TABS_TITLES[]=_('Code&nbsp;Snippets');
		}
		if ($sys_use_people) {
			$TABS_TITLES[]=_('Project&nbsp;Openings');
		}

		// outermenu hook
		$PLUGIN_TABS_DIRS = Array();
		$hookParams['DIRS'] = &$PLUGIN_TABS_DIRS;
		$hookParams['TITLES'] = &$TABS_TITLES;
		plugin_hook ("outermenu", $hookParams) ;
		$TABS_DIRS = array_merge($TABS_DIRS, $PLUGIN_TABS_DIRS);

		$user_is_super=false;
		if (session_loggedin()) {
			$projectmaster =& group_get_object(GROUP_IS_MASTER);
			$projectstats =& group_get_object(GROUP_IS_STATS);
			$permmaster =& $projectmaster->getPermission( session_get_user() );
			$permstats =& $projectstats->getPermission( session_get_user() );

			if ($permmaster->isAdmin()) {
				$user_is_super=true;
				$TABS_DIRS[]='/admin/';
				$TABS_TITLES[]=_('Admin');
			}
			if ($permstats->isMember()) {
				$TABS_DIRS[]='/reporting/';
				$TABS_TITLES[]=_('Reporting');
			}
		}
		if(isset($params['group']) && $params['group']) {
			// get group info using the common result set
			$project =& group_get_object($params['group']);
			if ($project && is_object($project)) {
				if ($project->isError()) {

				} elseif (!$project->isProject()) {

				} else {
					if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
						$TABS_DIRS[]='/project/?group_id'.$project->getId();
					} else {
						$TABS_DIRS[]='/projects/'.$project->getUnixName().'/';
					}
					$TABS_TITLES[]=$project->getPublicName();
					$selected=count($TABS_DIRS)-1;
				}
			}
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/my/') || 
				strstr(getStringFromServer('REQUEST_URI'),'/account/') || 
				strstr(getStringFromServer('REQUEST_URI'),'/register/') ||  
				strstr(getStringFromServer('REQUEST_URI'),'/themes/') ) {
			$selected=array_search("/my/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'softwaremap')) {
			$selected=array_search("/softwaremap/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/snippet/')) {
			$selected=array_search("/snippet/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/people/')) {
			$selected=array_search("/people/", $TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/reporting/')) {
			$selected=array_search('/reporting/',$TABS_DIRS);
		} elseif (strstr(getStringFromServer('REQUEST_URI'),'/admin/') && $user_is_super) {
			$selected=array_search('/admin/',$TABS_DIRS);
		} elseif (count($PLUGIN_TABS_DIRS)>0) {
			foreach ($PLUGIN_TABS_DIRS as $PLUGIN_TABS_DIRS_VALUE) {
				if (strstr(getStringFromServer('REQUEST_URI'),$PLUGIN_TABS_DIRS_VALUE)) {
					$selected=array_search($PLUGIN_TABS_DIRS_VALUE,$TABS_DIRS);
					break;
				}
			}
		} else {
			$selected=0;
		}
		echo $this->tabGenerator($TABS_DIRS,$TABS_TITLES,false,$selected,'','100%');

	}

	/**
	 *	projectTabs() - Prints out the project tabs, contained here in case
	 *		we want to allow it to be overriden
	 *
	 *	@param	string	Is the tab currently selected
	 *	@param	string	Is the group we should look up get title info
	 */
	function projectTabs($toptab,$group) {
		// get group info using the common result set
		$project =& group_get_object($group);
		if (!$project || !is_object($project)) {
			return;
		}
		if ($project->isError()) {
			//wasn't found or some other problem
			return;
		}
		if (!$project->isProject()) {
			return;
		}

		// Summary
		if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
			$TABS_DIRS[]='/project/?group_id='. $project->getId();
		} else {
			$TABS_DIRS[]='/projects/'. $project->getUnixName() .'/';
		}
		$TABS_TITLES[]=_('Summary');
		(($toptab == 'home') ? $selected=(count($TABS_TITLES)-1) : '' );

		// Project Admin
		$perm =& $project->getPermission( session_get_user() );
		if ($perm->isAdmin()) {
			$TABS_DIRS[]='/project/admin/?group_id='. $group;
			$TABS_TITLES[]=_('Admin');
			(($toptab == 'admin') ? $selected=(count($TABS_TITLES)-1) : '' );
		}
		/* Homepage
		$TABS_DIRS[]='http://'. $project->getHomePage();
		$TABS_TITLES[]=_('Home Page');
		*/

		// Project Activity tab 

		$TABS_DIRS[]='/activity/?group_id='. $group;
		$TABS_TITLES[]=_('Activity');
		(($toptab == 'activity') ? $selected=(count($TABS_TITLES)-1) : '' );

		// Forums
		if ($project->usesForum()) {
			$TABS_DIRS[]='/forum/?group_id='.$group;
			$TABS_TITLES[]=_('Forums');
			(($toptab == 'forums') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// Artifact Tracking
		if ($project->usesTracker()) {
			$TABS_DIRS[]='/tracker/?group_id='.$group;
			$TABS_TITLES[]=_('Tracker');
			(($toptab == 'tracker' || $toptab == 'bugs' || $toptab == 'support' || $toptab == 'patch')
				? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// Mailing Lists
		if ($project->usesMail()) {
			$TABS_DIRS[]='/mail/?group_id='.$group;
			$TABS_TITLES[]=_('Lists');
			(($toptab == 'mail') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// Project Manager
		if ($project->usesPm()) {
			$TABS_DIRS[]='/pm/?group_id='.$group;
			$TABS_TITLES[]=_('Tasks');
			(($toptab == 'pm') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// Doc Manager
		if ($project->usesDocman()) {
			$TABS_DIRS[]='/docman/?group_id='.$group;
			$TABS_TITLES[]=_('Docs');
			(($toptab == 'docman') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// Surveys
		if ($project->usesSurvey()) {
			$TABS_DIRS[]='/survey/?group_id='.$group;
			$TABS_TITLES[]=_('Surveys');
			(($toptab == 'surveys') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		//newsbytes
		if ($project->usesNews()) {
			$TABS_DIRS[]='/news/?group_id='.$group;
			$TABS_TITLES[]=_('News');
			(($toptab == 'news') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// SCM systems
		if ($project->usesSCM()) {
			$TABS_DIRS[]='/scm/?group_id='.$group;
			$TABS_TITLES[]=_('SCM');
			(($toptab == 'scm') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// groupmenu_after_scm hook
		$hookParams['DIRS'] = &$TABS_DIRS;
		$hookParams['TITLES'] = &$TABS_TITLES;
		$hookParams['toptab'] = &$toptab;
		$hookParams['selected'] = &$selected;
		$hookParams['group_id'] = $group ;
				
		plugin_hook ("groupmenu_scm", $hookParams) ; 

		// Downloads
		if ($project->usesFRS()) {
			$TABS_DIRS[]='/frs/?group_id='.$group;
			$TABS_TITLES[]=_('Files');
			(($toptab == 'frs') ? $selected=(count($TABS_TITLES)-1) : '' );
		}

		// groupmenu hook
		$hookParams['DIRS'] = &$TABS_DIRS;
		$hookParams['TITLES'] = &$TABS_TITLES;
		$hookParams['toptab'] = &$toptab;
		$hookParams['selected'] = &$selected;
		$hookParams['group'] = $group;
				
		plugin_hook ("groupmenu", $hookParams) ; 

		echo $this->tabGenerator($TABS_DIRS,$TABS_TITLES,true,$selected,'white','100%');

	}

	function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='WHITE',$total_width='100%') {

		$count=count($TABS_DIRS);
		$width=intval((100/$count));
		
		$return = '';
		
		$return .= '

		<!-- start tabs -->

		<table border="0" cellpadding="0" cellspacing="0" width="'.$total_width.'">
		<tr>';
		if ($nested) {
			$inner='bottomtab';
		} else {
			$inner='toptab';
		}
		$rowspan = '';
		for ($i=0; $i<$count; $i++) {
			if ($i == 0) {
				//
				//	this is the first tab, choose an image with end-name
				//
				$wassel=false;
				$issel=($selected==$i);
				$bgimg=(($issel)?'theme-'.$inner.'-selected-bg.png':'theme-'.$inner.'-notselected-bg.png');
		//		$rowspan=(($issel)?'rowspan="2" ' : '');

				$return .= '
					<td '.$rowspan.'valign="top" width="10" style="background:url('.$this->imgroot . 'theme-'.$inner.'-end-'.(($issel) ? '' : 'not').'selected.png)">'.
						'<img src="'.$this->imgroot . 'clear.png" height="25" width="10" alt="" /></td>'.
						'<td '.$rowspan.'style="background:url('.$this->imgroot . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink'))).'</td>';
			} elseif ($i==$count-1) {
				//
				//	this is the last tab, choose an image with name-end
				//
				$wassel=($selected==$i-1);
				$issel=($selected==$i);
				$bgimg=(($issel)?'theme-'.$inner.'-selected-bg.png':'theme-'.$inner.'-notselected-bg.png');
		//		$rowspan=(($issel)?'rowspan="2" ' : '');
				//
				//	Build image between current and prior tab
				//
				$return .= '
					<td '.$rowspan.'colspan="2" valign="top" width="20" style="background:url('.$this->imgroot . 'theme-'.$inner.'-'.(($wassel) ? '' : 'not').'selected-'.(($issel) ? '' : 'not').'selected.png)">'.
						'<img src="'.$this->imgroot . 'clear.png" height="2" width="20" alt="" /></td>'.
						'<td '.$rowspan.'style="background:url('.$this->imgroot . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink'))).'</td>';
				//
				//	Last graphic on right-side
				//
				$return .= '
					<td '.$rowspan.'valign="top" width="10" style="background:url('.$this->imgroot . 'theme-'.$inner.'-'.(($issel) ? '' : 'not').'selected-end.png)">'.
						'<img src="'.$this->imgroot . 'clear.png" height="2" width="10" alt="" /></td>';

			} else {
				//
				//	middle tabs
				//
				$wassel=($selected==$i-1);
				$issel=($selected==$i);
				$bgimg=(($issel)?'theme-'.$inner.'-selected-bg.png':'theme-'.$inner.'-notselected-bg.png');
		//		$rowspan=(($issel)?'rowspan="2" ' : '');
				//
				//	Build image between current and prior tab
				//
				$return .= '
					<td '.$rowspan.'colspan="2" valign="top" width="20" style="background:url('.$this->imgroot . 'theme-'.$inner.'-'.(($wassel) ? '' : 'not').'selected-'.(($issel) ? '' : 'not').'selected.png)">'.
						'<img src="'.$this->imgroot . 'clear.png" height="2" width="20" alt="" /></td>'.
						'<td '.$rowspan.'style="background:url('.$this->imgroot . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink'))).'</td>';

			}
		}
		$return .= '</tr>';

		//
		//	Building a bottom row in this table, which will be darker
		//
		if ($selected == 0) {
			$beg_cols=0;
			$end_cols=((count($TABS_DIRS)*3)-3);
		} elseif ($selected == (count($TABS_DIRS)-1)) {
			$beg_cols=((count($TABS_DIRS)*3)-3);
			$end_cols=0;
		} else {
			$beg_cols=($selected*3);
			$end_cols=(((count($TABS_DIRS)*3)-3)-$beg_cols);
		}
		$return .= '<tr>';
		if ($beg_cols > 0) {
			$return .= '<td colspan="'.$beg_cols.'" height="1" class="notSelTab"><img src="'.$this->imgroot.'clear.png" height="1" width="10" alt="" /></td>';
		}
		$return .= '<td colspan="3" height="1" class="selTab"><img src="'.$this->imgroot.'clear.png" height="1" width="10" alt="" /></td>';
		if ($end_cols > 0) {
			$return .= '<td colspan="'.$end_cols.'" height="1" class="notSelTab"><img src="'.$this->imgroot.'clear.png" height="1" width="10" alt="" /></td>';
		}
		$return .= '</tr>';


		return $return.'
		</table> 

		<!-- end tabs -->
';
	}

	function searchBox() {
		global $words,$forum_id,$group_id,$group_project_id,$atid,$exact,$type_of_search;

		if(get_magic_quotes_gpc()) {
			$defaultWords = stripslashes($words);
		} else {
			$defaultWords = $words;
		}

		//Fix CVE-2007-0176
		$defaultWords = htmlspecialchars($defaultWords);
		
		// if there is no search currently, set the default
		if ( ! isset($type_of_search) ) {
			$exact = 1;
		}

		print '
		<form action="/search/" method="get">
		<table border="0" cellpadding="0" cellspacing="0">
		<tr><td>
		<div align="center" class="searchbox">';
		$parameters = array(
			SEARCH__PARAMETER_GROUP_ID => $group_id,
			SEARCH__PARAMETER_ARTIFACT_ID => $atid,
			SEARCH__PARAMETER_FORUM_ID => $forum_id,
			SEARCH__PARAMETER_GROUP_PROJECT_ID => $group_project_id
		);
		
		$searchManager =& getSearchManager();
		$searchManager->setParametersValues($parameters);
		$searchEngines =& $searchManager->getAvailableSearchEngines();
		
		echo '<select name="type_of_search">';
		for($i = 0, $max = count($searchEngines); $i < $max; $i++) {
			$searchEngine =& $searchEngines[$i];
			echo '<option value="'.$searchEngine->getType().'"'.( $type_of_search == $searchEngine->getType() ? ' selected="selected"' : '' ).'>'.$searchEngine->getLabel($parameters).'</option>'."\n";
		}
		echo '</select></div>';

//		print '<br />';
//		print '
//		<input type="CHECKBOX" name="exact" value="1"'.( $exact ? ' CHECKED' : ' UNCHECKED' ).'> Require All Words';

		print '</td><td>&nbsp;';
		$parameters = $searchManager->getParameters();
		foreach($parameters AS $name => $value) {
			print '<input type="hidden" value="'.$value.'" name="'.$name.'" />';
		}
		print '</td><td>';
		print '<input type="text" size="12" name="words" value="'.$defaultWords.'" />';

		print '</td><td>&nbsp;</td><td>';
		print '<input type="submit" name="Search" value="'._('Search').'" />';
		print '</td>';

		if (isset($group_id) && $group_id) {
			print '
					<td width="10">&nbsp;</td>
					<td>'.util_make_link ('/search/advanced_search.php?group_id='.$group_id,_('Advanced search'),array('class'=>'lnkutility')).'</td>';
		}
		print '</tr></table>';
		print '</form>';

	}
	
	function advancedSearchBox($sectionsArray, $group_id, $words, $isExact) {
		 // display the searchmask
		print '
		<form name="advancedsearch" action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="search" value="1"/>
		<input type="hidden" name="group_id" value="'.$group_id.'"/>
		<div align="center"><br />
			<table border="0">
				<tr>
					<td colspan ="2">
						<input type="text" size="60" name="words" value="'.stripslashes(htmlspecialchars($words)).'" />
						<input type="submit" name="submitbutton" value="'._('Search').'" />
					</td>
				</tr>
				<tr>
					<td valign="top">
						<input type="radio" name="mode" value="'.SEARCH__MODE_AND.'" '.($isExact ? 'checked="checked"' : '').' />'._('with all words').'
					</td>
					<td>
						<input type="radio" name="mode" value="'.SEARCH__MODE_OR.'" '.(!$isExact ? 'checked="checked"' : '').' />'._('with one word').'
					</td>
				</tr>
			</table><br /></div>'
		.$this->createUnderSections($sectionsArray).'
		</form>';


		//create javascript methods for select none/all
		print '
		<script type="text/javascript">
			<!-- method for disable/enable checkboxes
			function setCheckBoxes(parent, checked) {


				for (var i = 0; i < document.advancedsearch.elements.length; i++)
					if (document.advancedsearch.elements[i].type == "checkbox") 
							if (document.advancedsearch.elements[i].name.substr(0, parent.length) == parent)
								document.advancedsearch.elements[i].checked = checked;
				}
			//-->
		</script>
		';

	}
	
	function createUnderSections($sectionsArray) {
		$countLines = 0;
		foreach ($sectionsArray as $section) {
			if(is_array($section)) {
				$countLines += (3 + count ($section));
			} else {
				//2 lines one for section name and one for checkbox
				$countLines += 3;
			}
		}
		$breakLimit = round($countLines/3);
		$break = $breakLimit;
		$countLines = 0;
		$return = '
			<table width="100%" border="0" cellspacing="0" cellpadding="1">
				<tr class="tableheader">
					<td>
						<table width="100%" cellspacing="0" border="0">
							<tr class="tablecontent">
								<!--<td colspan="2">'._('Search in').':</td-->
								<td align="right">'._('Select').' <a href="javascript:setCheckBoxes(\'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\'\', false)">'._('none').'</a></td>
							</tr>
							<tr height="20" class="tablecontent">
								<td colspan="3">&nbsp;</td>
							</tr>
							<tr align="center" valign="top" class="tablecontent">
								<td>';
		foreach($sectionsArray as $key => $section) {
			$oldcountlines = $countLines;
			if (is_array($section)) {
				$countLines += (3 + count ($section));
			} else {
				$countLines += 3;
			}
				
			if ($countLines >= $break) {
				//if the next block is so large that shifting it to the next column hits the breakpoint better
				//the second part of statement (behind &&) proofs, that no 4th column is added
				if ((($countLines - $break) >= ($break - $countLines)) && ((($break + $breakLimit)/$breakLimit) <= 3)) {
					$return .= '</td><td>';
					$break += $breakLimit;
				}
			}
		
			$return .= '<table width="90%" border="0" cellpadding="1" cellspacing="0">
							<tr><td><table width="100%" border="0" cellspacing="0" cellpadding="3">
							<tr>
								<td cellspacing="0">
									<a href="#'.$key.'">'.$group_subsection_names[$key].'</a>'
							.'	</td>
								<td align="right">'
								._('Select').' <a href="javascript:setCheckBoxes(\''.$key.'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\''.$key.'\', false)">'._('none').'</a>
								</td>
							</tr>
							<tr class="tablecontent">
								<td colspan="2">';
								
			if (!is_array($section)) {
				$return .= '		<input type="checkbox" name="'.urlencode($key).'"';
				if (isset($GLOBALS[urlencode($key)]))
					$return .= ' checked="checked" ';
				$return .= ' /></input>'.$group_subsection_names[$key].'<br />';
			}
			else
				foreach($section as $underkey => $undersection) {
					$return .= '	<input type="checkbox" name="'.urlencode($key.$underkey).'"';
					if (isset($GLOBALS[urlencode($key.$underkey)]))
						$return .= ' checked ';
					$return .= '></input>'.$undersection.'<br />';				
					
				}
				
			$return .=		'	</td>
							</tr>
						</table></td></tr></table><br />';
						
			if ($countLines >= $break) {
				if (($countLines - $break) < ($break - $countLines)) {
					$return .= '</td><td width="33%">';
					$break += $breakLimit;
				}
			}
		}
		
		return $return.'		</td>
							</tr>
						</table></td></tr></table>';
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
		
		$return = '';
		for ($i=0; $i<$count; $i++) {
			$return .= util_make_link ($links_arr[$i],$title_arr[$i]).' | ';
		}
		$return .= util_make_link ($links_arr[$i],$title_arr[$i]);
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
	 * multiTableRow() - create a mutlilevel row in a table
	 *
	 * @param	string	the row attributes
	 * @param	array	the array of cell data, each element is an array,
	 *				  	the first item being the text,
	 *					the subsequent items are attributes (dont include
	 *					the bgcolor for the title here, that will be
	 *					handled by $istitle
	 * @param	boolean is this row part of the title ?
	 *
	 */
	 function multiTableRow($row_attr, $cell_data, $istitle) {
		$return= '
		<tr '.$row_attr;
		if ( $istitle ) {
			$return .=' align="center" class="multiTableRowTitle"';
		}
		$return .= '>';
		for ( $c = 0; $c < count($cell_data); $c++ ) {
			$return .='<td ';
			for ( $a=1; $a < count($cell_data[$c]); $a++) {
				$return .= $cell_data[$c][$a].' ';
			}
			$return .= '>';
			if ( $istitle ) {
				$return .='<span class="multiTableRowTitle">';
			}
			$return .= $cell_data[$c][0];
			if ( $istitle ) {
				$return .='</span>';
			}
			$return .= '</td>';

		}
		$return .= '</tr>
		';

		return $return;
	}
	
	/**
	 * feedback() - returns the htmlized feedback string when an action is performed.
	 *
	 * @param string feedback string
	 * @return string htmlized feedback
	 */
	function feedback($feedback) {
		if (!$feedback) {
			return '';
		} else {
			return '
				<span class="feedback">'.strip_tags($feedback, '<br>').'</span>';
		}
	}

	/**
	 * getThemeIdFromName()
	 *
	 * @param	string  the dirname of the theme
	 * @return	integer the theme id	
	 */
	function getThemeIdFromName($dirname) {
	 	$res=db_query("SELECT theme_id FROM themes WHERE dirname='$dirname'");
	        return db_result($res,0,'theme_id');
	}

	function quickNav() {
		if (!session_loggedin()) {
			return '';
		} else {
			$res=db_query("SELECT * FROM groups NATURAL JOIN user_group WHERE user_id='".user_getid()."' ORDER BY group_name");
echo db_error();
			if (!$res || db_numrows($res) < 1) {
				return '';
			} else {
				$ret = '
		<form name="quicknavform">
			<select name="quicknav" onChange="location.href=document.quicknavform.quicknav.value">';
				$ret .= '
				<option value="">Quick Jump To...</option>';
				for ($i=0; $i<db_numrows($res); $i++) {
					$ret .= '
				<option value="'.util_make_url_g (db_result($res,$i,'unix_group_name'),db_result($res,$i,'group_id')).'">'.db_result($res,$i,'group_name').'</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A') {
					$ret .= '
				<option value="'.util_make_url ('/project/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
		//tracker
					if (db_result($res,$i,'use_tracker')) {
					$ret .= '
				<option value="'.util_make_url ('/tracker/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tracker</option>';
						if (db_result($res,$i,'admin_flags') || db_result($res,$i,'artifact_flags')) {
					$ret .= '
				<option value="'.util_make_url ('/tracker/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//task mgr
					if (db_result($res,$i,'use_pm')) {
					$ret .= '
				<option value="'.util_make_url ('/pm/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Task Manager</option>';
						if (trim(db_result($res,$i,'admin_flags')) =='A' || db_result($res,$i,'project_flags')) {
					$ret .= '
				<option value="'.util_make_url ('/pm/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//FRS
					if (db_result($res,$i,'use_frs')) {
					$ret .= '
				<option value="'.util_make_url('/frs/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Files</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'release_flags')) {
					$ret .= '
				<option value="'.util_make_url('/frs/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//SCM
					if (db_result($res,$i,'use_scm')) {
					$ret .= '
				<option value="'.util_make_url('/scm/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SCM</option>';
						/*if (db_result($res,$i,'admin_flags') || db_result($res,$i,'project_flags')) {
					$ret .= '
				<option value="'.util_make_url('/pm/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						} */
					}
		//forum
					if (db_result($res,$i,'use_forum')) {
					$ret .= '
				<option value="'.util_make_url('/forum/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forum</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'forum_flags')) {
					$ret .= '
				<option value="'.util_make_url('/forum/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//mail
					if (db_result($res,$i,'use_mail')) {
					$ret .= '
				<option value="'.util_make_url('/mail/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A') {
					$ret .= '
				<option value="'.util_make_url('/mail/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//doc
					if (db_result($res,$i,'use_docman')) {
					$ret .= '
				<option value="'.util_make_url('/docman/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Docs</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'doc_flags')) {
					$ret .= '
				<option value="'.util_make_url('/docman/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//news
					if (db_result($res,$i,'use_news')) {
					$ret .= '
				<option value="'.util_make_url('/news/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;News</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A') {
					$ret .= '
				<option value="'.util_make_url('/news/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
		//survey
					if (db_result($res,$i,'use_survey')) {
					$ret .= '
				<option value="'.util_make_url('/survey/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Surveys</option>';
						if (trim(db_result($res,$i,'admin_flags'))=='A') {
					$ret .= '
				<option value="'.util_make_url('/survey/admin/?group_id='.db_result($res,$i,'group_id')).'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						}
					}
				}	
				$ret .= '
			</select>
		</form>';
			}
		}
		return $ret;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
