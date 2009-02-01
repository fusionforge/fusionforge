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

require_once $gfwww.'search/include/SearchManager.class.php';
require_once $gfcommon.'include/FusionForge.class.php';

class Theme extends Error {

	/**
	 * The default main page content
	 */
	var $rootindex = 'index_std.php';

	/**
	* The root location of the theme
	* @var      string $themeroot
	*/
	var $themeroot;

	/**
	 * The root location for images
	 *
	 * @var		string	$imgroot
	 */

	var $imgroot = '/themes/gforge/images/';

	var $selected_title;

	var $selected_dir;

	var $selected_id;
	/**
	 * Layout() - Constructor
	 */
	function Layout() {

		$this->themeroot=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'];
		/* if images directory exists in theme, then use it as imgroot */
		if (file_exists ($this->themeroot.'/images')){
			$this->imgroot='/themes/'.$GLOBALS['sys_theme'].'/images/';
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

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo _('en') ?>"
	lang="<?php echo _('en') ?>">

<head>
<meta http-equiv="Content-Type"
	content="text/html; charset=utf-8" />
<title><?php echo $params['title']; ?></title>
<link rel="alternate"
	title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS"
	href="<?php echo util_make_url ('/export/rss_sfnews.php'); ?>"
	type="application/rss+xml" />
<link rel="alternate"
	title="<?php echo $GLOBALS['sys_name']; ?> - Project News Highlights RSS 2.0"
	href="<?php echo util_make_url ('/export/rss20_news.php'); ?>"
	type="application/rss+xml" />
<link rel="alternate"
	title="<?php echo $GLOBALS['sys_name']; ?> - New Projects RSS"
	href="<?php echo util_make_url ('/export/rss_sfprojects.php'); ?>"
	type="application/rss+xml" />

		<?php	if (isset($GLOBALS['group_id'])) {
			$activity = '<link rel="alternate" title="'.$GLOBALS['sys_name'].' - New Activity RSS" href="'.
			util_make_url ('/export/rss20_activity.php?group_id='.$GLOBALS['group_id']).
			'" type="application/rss+xml"/>';
			echo $activity;
		}
		?>
		<?

		/* check if a personalized css stylesheet exist, if yes include only
		 this stylesheet
		 new stylesheets should use the <themename>.css file
		 */
		$theme_cssfile=$GLOBALS['sys_themeroot'].$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css';
		if (file_exists($theme_cssfile)){
			echo '
<link rel="stylesheet" type="text/css" href="'.util_make_url ('/themes/'.$GLOBALS['sys_theme'].'/css/'.$GLOBALS['sys_theme'].'.css').'"/>';
		}
		?>

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
	<?php plugin_hook ('cssfile',$this); ?>

</head>
	<?php

}

function header($params) {
	$this->headerStart($params); ?>
<body>



<div id="container">
<div id="logo">
<h1><?php echo util_make_link ('/',_('Home')); ?></h1>
</div>
<div id="util">
<?php
if (session_loggedin()) {
	echo util_make_link ('/account/logout.php',_('Log Out'));
	echo '<br />';
	echo util_make_link ('/account/',_('My Account'));
	echo '<br />';
} else {
	echo util_make_link ('/account/login.php',_('Log In'));
	echo '<br />';
	if (!$GLOBALS['sys_user_reg_restricted']) {
		echo util_make_link ('/account/register.php',_('New Account'));
		echo '<br />';
	}
}
?>
</div>

<div id="headercontent"><br />
<?php echo $this->searchBox();
?></div>


</div>

<div id="outertabscontainer">
<div id="outertabs-left"></div>
<div id="outertabs-content"><?php echo $this->outerTabs($params); 
?></div>
<div id="outertabs-right"></div>

</div>

<?php
if (isset($params['group']) && $params['group']) {
	?>
<div id="projecttabscontainer">
<div id="projecttabs-left"></div>
<div id="projecttabs-content"><?echo $this->projectTabs($params['toptab'],$params['group']);?>
</div>
<div id="projecttabs-right"></div>
</div>

	<?php }
	?>


<div id="gforge-content">
<div id="<?php echo "gforge-content-".$this->selected_id; 
?>">
<fieldset><legend><?php 
echo '<a href="'.$this->selected_dir.'">'.$this->selected_title.'</a>';
?></legend> <?php
}


function footer($params){
	echo '</fieldset></div></div>';
	$this->footerEnd($params);

}
function footerEnd($params) { ?> <!-- PLEASE LEAVE "Powered By FusionForge" on your site -->
<div id="footer"><a href="http://fusionforge.org/"><img
	src="<?php echo util_make_url ('/images/pow-fusionforge.png'); ?>"
	alt="Powered By FusionForge"
	border="0" /></a>
<br />
<?php
		    $forge = new FusionForge() ;
	    printf (_('This site is running %1$s version %2$s'),
		    $forge->software_name,
		    $forge->software_version) ;
?>
</div>
<?php
global $sys_show_source;
if ($sys_show_source) {
	echo util_make_link ('/source.php?file='.getStringFromServer('SCRIPT_NAME'),_('Show source'),array('class'=>'showsource'));
}
?>

</body>
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
		
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr class="tableheading">
			<td valign="top" align="right" width="10"><img src="'.$this->imgroot.'clear.png'.'" width="10" height="20" alt="" /></td>
			<td width="100%"><span class="titlebar">'.$title.'</span></td>
			<td valign="top" width="10"><img src="'.$this->imgroot.'clear.png'.'" width="10" height="20" alt=""/></td>
		</tr>
		<tr>
			<td colspan="3">
			<table cellspacing="2" cellpadding="2" width="100%" border="0" class="tablecontent">
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
				<tr class="tableheading">
					<td colspan="2">'.$title.'</td>
				</tr>
				<tr align="left" class="tablecontent">
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
function listTableTop ($title_arr,$links_arr=false,$cnt=0) {

	$return='';
	if ($cnt==0){
		$return='<div id="tabletop">';
	}
	else{
		$return='<div id="tabletop'.$cnt.'">';
	}

	$return .= '
	<table>
		<thead><tr>';
	$count=count($title_arr);
	if ($links_arr) {
		for ($i=0; $i<$count; $i++) {
			$return .= '
			<td><a href="'.$links_arr[$i].'">'.$title_arr[$i].'</a></td>';
		}
	} else {
		for ($i=0; $i<$count; $i++) {
			$return .= '
			<td>'.$title_arr[$i].'</td>';
		}
	}
	return $return.'</tr></thead>';


}

function listTableBottom() {
	return '</table>
	<!--</td>
			</tr></table>
	--></div>';
}


function outerTabs($params) {
	global $sys_use_trove,$sys_use_snippet,$sys_use_people;

	$selected=0;
	$TABS_DIRS[]='/';
	$TABS_IDS[]='home';
	$TABS_TITLES[]=_('Home');

	$TABS_IDS[]='my';
	$TABS_DIRS[]='/my/';
	$TABS_TITLES[]=_('My&nbsp;Page');
	if (strstr(getStringFromServer('REQUEST_URI'),'/my/') || strstr(getStringFromServer('REQUEST_URI'),'/account/') ) {
		$selected=count($TABS_DIRS)-1;
	}

	if ($sys_use_trove) {
		$thistab='softwaremap';
		$TABS_IDS[]='softwaremap';
		$TABS_DIRS[]='/'.$thistab.'/';
		$TABS_TITLES[]=_('Project&nbsp;Tree');
		if (strstr(getStringFromServer('REQUEST_URI'),$thistab)){
			$selected=count($TABS_DIRS)-1;
		}
	}
	if ($sys_use_snippet) {
		$thistab='snippet';
		$TABS_IDS[]=$thistab;
		$TABS_DIRS[]='/'.$thistab.'/';
		$TABS_TITLES[]=_('Code&nbsp;Snippets');
		if (strstr(getStringFromServer('REQUEST_URI'),$thistab)){
			$selected=count($TABS_DIRS)-1;
		}
	}
	if ($sys_use_people) {
		$thistab='people';
		$TABS_IDS[]=$thistab;
		$TABS_DIRS[]='/'.$thistab.'/';
		$TABS_TITLES[]=_('Project&nbsp;Openings');
		if (strstr(getStringFromServer('REQUEST_URI'),$thistab)){
			$selected=count($TABS_DIRS)-1;
		}
	}
	// outermenu hook
	$PLUGIN_TABS_DIRS = Array();
	$hookParams['DIRS'] = &$PLUGIN_TABS_DIRS;
	$hookParams['TITLES'] = &$TABS_TITLES;
	plugin_hook ("outermenu", $hookParams) ;
	$TABS_DIRS = array_merge($TABS_DIRS, $PLUGIN_TABS_DIRS);

	if (user_ismember(1,'A')) {
		$thistab='admin';
		$TABS_IDS[]=$thistab;
		$TABS_DIRS[]='/'.$thistab.'/';
		$TABS_TITLES[]=_('Admin');
		if (strstr(getStringFromServer('REQUEST_URI'),$thistab)){
			$selected=count($TABS_DIRS)-1;
		}

	}
	if (user_ismember($GLOBALS['sys_stats_group'])) {
		$thistab='reporting';
		$TABS_IDS[]=$thistab;
		$TABS_DIRS[]='/'.$thistab.'/';
		$TABS_TITLES[]=_('Reporting');
		if (strstr(getStringFromServer('REQUEST_URI'),$thistab)){
			$selected=count($TABS_DIRS)-1;
		}
	}

	if(isset($params['group']) && $params['group']) {
		// get group info using the common result set
		$project =& group_get_object($params['group']);
		if ($project && is_object($project)) {
			if ($project->isError()) {

			} elseif (!$project->isProject()) {

			} else {
				$TABS_DIRS[]=util_make_url_g ($project->getUnixName(),$params['group']);
				$TABS_TITLES[]=$project->getPublicName();
				$selected=count($TABS_DIRS)-1;
			}
		}
	} elseif (count($PLUGIN_TABS_DIRS)>0) {
		foreach ($PLUGIN_TABS_DIRS as $PLUGIN_TABS_DIRS_VALUE) {
			if (strstr($GLOBALS['REQUEST_URI'],$PLUGIN_TABS_DIRS_VALUE)) {
				$selected=array_search($PLUGIN_TABS_DIRS_VALUE,$TABS_DIRS);
				break;
			}
		}
	}
	$c=(count($TABS_TITLES)-1);
	if(isset($params['group']) && $params['group']) {
		/* project is selected */
		$c--;
	}
	for ($i=0; $i<=$c; $i++) {
		if ($selected == $i){
			$this->selected_title = $TABS_TITLES[$i];
			$this->selected_dir = $TABS_DIRS[$i];
			$this->selected_id = $TABS_IDS[$i];
			@$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>'gforge-selected','title'=>$TABS_TITLES[$i]));
		}
		else{
			@$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>'gforge-'.$TABS_IDS[$i],'title'=>$TABS_TITLES[$i]));
		}
	}
	if(isset($params['group']) && $params['group']) {
		$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>'gforge-selected','title'=>$TABS_TITLES[$i]));
		$this->selected_title = $TABS_TITLES[$i];
	}
	return $return;
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

	//		$TABS_DIRS[]='/projects/'.$project->getUnixName().'/';
	//		$TABS_TITLES[]=$project->getPublicName();

	// Summary
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		$TABS_DIRS[]='/project/?group_id='.$group;
	} else {
		$TABS_DIRS[]='/projects/'.$project->getUnixName();
	}
	$TABS_IDS[]='gforge-project-summary';
	$TABS_TITLES[]=_('Summary');
	(($toptab == 'home') ? $selected=(count($TABS_TITLES)-1) : '' );

	if (user_ismember($group,'A')) {
		// Project Admin
		$TABS_DIRS[]='/project/admin/?group_id='. $group;
		$TABS_IDS[]='gforge-project-admin';
		$TABS_TITLES[]=_('Admin');
		(($toptab == 'admin') ? $selected=(count($TABS_TITLES)-1) : '' );
	}
	/* Homepage */
	/*		$TABS_DIRS[]='http://'. $project->getHomePage();
		$TABS_TITLES[]=_('Home Page');
		*/

	$TABS_DIRS[]='/activity/?group_id='. $group;
	$TABS_IDS[]='gforge-project-activity';
	$TABS_TITLES[]=_('Activity');
	(($toptab == 'activity') ? $selected=(count($TABS_TITLES)-1) : '' );

	// Forums
	if ($project->usesForum()) {
		$TABS_DIRS[]='/forum/?group_id='.$group;
		$TABS_IDS[]='gforge-project-forum';
		$TABS_TITLES[]=_('Forums');
		(($toptab == 'forums') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// Artifact Tracking
	if ($project->usesTracker()) {
		$TABS_DIRS[]='/tracker/?group_id='.$group;
		$TABS_IDS[]='gforge-project-tracker';
		$TABS_TITLES[]=_('Tracker');
		(($toptab == 'tracker' || $toptab == 'bugs' || $toptab == 'support' || $toptab == 'patch')
		? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// Mailing Lists
	if ($project->usesMail()) {
		$TABS_DIRS[]='/mail/?group_id='.$group;
		$TABS_IDS[]='gforge-project-mail';
		$TABS_TITLES[]=_('Lists');
		(($toptab == 'mail') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// Project Manager
	if ($project->usesPm()) {
		$TABS_IDS[]='gforge-project-task';
		$TABS_DIRS[]='/pm/?group_id='.$group;
		$TABS_TITLES[]=_('Tasks');
		(($toptab == 'pm') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// Doc Manager
	if ($project->usesDocman()) {
		$TABS_DIRS[]='/docman/?group_id='.$group;
		$TABS_IDS[]='gforge-project-docman';
		$TABS_TITLES[]=_('Docs');
		(($toptab == 'docman') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// Surveys
	if ($project->usesSurvey()) {
		$TABS_DIRS[]='/survey/?group_id='.$group;
		$TABS_IDS[]='gforge-project-survey';
		$TABS_TITLES[]=_('Surveys');
		(($toptab == 'surveys') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	//newsbytes
	if ($project->usesNews()) {
		$TABS_IDS[]='gforge-project-news';
		$TABS_DIRS[]='/news/?group_id='.$group;
		$TABS_TITLES[]=_('News');
		(($toptab == 'news') ? $selected=(count($TABS_TITLES)-1) : '' );
	}

	// SCM systems
	if ($project->usesSCM()) {
		$TABS_IDS[]='gforge-project-scm';
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
		$TABS_IDS[]='gforge-project-frs';
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

	$return ='';
	$c=(count($TABS_TITLES)-1);
	for ($i=0; $i<=$c; $i++) {
		if ($selected==$i){
			$this->selected_title = $TABS_TITLES[$i];
			$this->selected_dir = $TABS_DIRS[$i];
			//$this->selected_id = $TABS_IDS[$i];
			$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>'gforge-project-selected'));

		}
		else{
			if (!isset($TABS_IDS[$i]) || $TABS_IDS[$i]==''){
				$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>'gforge-project-std','title'=>$TABS_TITLES[$i]));

			}
			else {
				$return .= util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('id'=>$TABS_IDS[$i],'title'=>$TABS_TITLES[$i]));
			}
		}
	}

	return $return;
}


function searchBox() {
	global $words,$forum_id,$group_id,$group_project_id,$atid,$exact,$type_of_search;

	if(get_magic_quotes_gpc()) {
		$defaultWords = stripslashes($words);
	} else {
		$defaultWords = $words;
	}

	// if there is no search currently, set the default
	if ( ! isset($type_of_search) ) {
		$exact = 1;
	}

	print '
		<form action="/search/" method="get">';


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
	echo '</select>';
	$parameters = $searchManager->getParameters();
	foreach($parameters AS $name => $value) {
		print '<input type="hidden" value="'.$value.'" name="'.$name.'" />';
	}

	print '<input type="text" size="12" name="words" value="'.$defaultWords.'" />';


	print '<input type="submit" name="Search" value="'._('Search').'" />';


	if (isset($group_id)&& $group_id) {
		echo util_make_link ('/search/advanced_search.php?group_id='.$group_id,_('Advanced search'),array('class'=>'lnkutility'));
	}
	print '</form>';

}

function advancedSearchBox($sectionsArray, $group_id, $words, $isExact) {
	// display the searchmask
	print '
		<form name="advancedsearch" action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="search" value="1"/>
		<input type="hidden" name="group_id" value="'.$group_id.'"/>
		<div id="advancedsearchtext">
						<input type="text" size="60" name="words" value="'.stripslashes(htmlspecialchars($words)).'" />
						<input type="submit" name="submitbutton" value="'._('Search').'" />
						<input type="radio" name="mode" value="'.SEARCH__MODE_AND.'" '.($isExact ? 'checked="checked"' : '').' />'._('with all words').'
						<input type="radio" name="mode" value="'.SEARCH__MODE_OR.'" '.(!$isExact ? 'checked="checked"' : '').' />'._('with one word').'
</div>';
	print '<br/>	'._('Select').' <a href="javascript:setCheckBoxes(\'\',true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\'\', false)">'._('none').'</a>';

	print $this->createUnderSections($sectionsArray).'
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
		<div id="advancedsearch"><ul>';
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
				$break += $breakLimit;
			}
		}

		$return .= '<li><fieldset><legend>'.$group_subsection_names[$key].'</legend>'
							._('Select').' <a href="javascript:setCheckBoxes(\''.$key.'\', true)">'._('all').'</a> / <a href="javascript:setCheckBoxes(\''.$key.'\', false)">'._('none').'</a>
			<div id="list"><ul>
							';

		if (!is_array($section)) {
			$return .= '<li><input type="checkbox" name="'.urlencode($key).'"';
			if (isset($GLOBALS[urlencode($key)]))
			$return .= ' checked="checked" ';
			$return .= ' /></input>'.$group_subsection_names[$key].'</li>';
		}
		else
		foreach($section as $underkey => $undersection) {
			$return .= '	<li><input type="checkbox" name="'.urlencode($key.$underkey).'"';
			if (isset($GLOBALS[urlencode($key.$underkey)]))
			$return .= ' checked ';
			$return .= '></input>'.$undersection.'</li>';

		}
		$return .= '</ul></div></fieldset></li>';

		if ($countLines >= $break) {
			if (($countLines - $break) < ($break - $countLines)) {
				$break += $breakLimit;
			}
		}
	}
	$return.='</div></ul>';
	return $return;
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
		<form name="quicknavform" action="" >
			<select name="quicknav" onChange="location.href=document.quicknavform.quicknav.value">';
			$ret .= '
				<option value="">Quick Jump To...</option>';
			for ($i=0; $i<db_numrows($res); $i++) {
				$ret .= '
				<option value="/projects/'.db_result($res,$i,'unix_group_name').'/">'.db_result($res,$i,'group_name').'</option>';
				if (trim(db_result($res,$i,'admin_flags'))=='A') {
					$ret .= '
				<option value="/project/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
				}
				//tracker
				if (db_result($res,$i,'use_tracker')) {
					$ret .= '
				<option value="/tracker/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tracker</option>';
					if (db_result($res,$i,'admin_flags') || db_result($res,$i,'artifact_flags')) {
						$ret .= '
				<option value="/tracker/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//task mgr
				if (db_result($res,$i,'use_pm')) {
					$ret .= '
				<option value="/pm/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Task Manager</option>';
					if (trim(db_result($res,$i,'admin_flags')) =='A' || db_result($res,$i,'project_flags')) {
						$ret .= '
				<option value="/pm/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//FRS
				if (db_result($res,$i,'use_frs')) {
					$ret .= '
				<option value="/frs/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Files</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'release_flags')) {
						$ret .= '
				<option value="/frs/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//SCM
				if (db_result($res,$i,'use_scm')) {
					$ret .= '
				<option value="/scm/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SCM</option>';
					/*if (db_result($res,$i,'admin_flags') || db_result($res,$i,'project_flags')) {
					 $ret .= '
					 <option value="/pm/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
						} */
				}
				//forum
				if (db_result($res,$i,'use_forum')) {
					$ret .= '
				<option value="/forum/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forum</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'forum_flags')) {
						$ret .= '
				<option value="/forum/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//mail
				if (db_result($res,$i,'use_mail')) {
					$ret .= '
				<option value="/mail/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Lists</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A') {
						$ret .= '
				<option value="/mail/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//doc
				if (db_result($res,$i,'use_docman')) {
					$ret .= '
				<option value="/docman/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Docs</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A' || db_result($res,$i,'doc_flags')) {
						$ret .= '
				<option value="/docman/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//news
				if (db_result($res,$i,'use_news')) {
					$ret .= '
				<option value="/news/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;News</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A') {
						$ret .= '
				<option value="/news/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
					}
				}
				//survey
				if (db_result($res,$i,'use_survey')) {
					$ret .= '
				<option value="/survey/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Surveys</option>';
					if (trim(db_result($res,$i,'admin_flags'))=='A') {
						$ret .= '
				<option value="/survey/admin/?group_id='.db_result($res,$i,'group_id').'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admin</option>';
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
