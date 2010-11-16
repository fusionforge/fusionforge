<?php
/**
 * Base layout class.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 - Alain Peyrat
 * Copyright 2010 - Franck Villaume - Capgemini
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 *
 * Extends the basic Error class to add HTML functions
 * for displaying all site dependent HTML, while allowing
 * extendibility/overriding by themes via the Theme class.
 *
 * Make sure browser.php is included _before_ you create an instance
 * of this object.
 *
 */

require_once $gfcommon.'include/constants.php';
require_once $gfcommon.'include/Navigation.class.php';

class Layout extends Error {

	/**
	 * Which doctype to use. Can be configured in the
	 * constructor. If set to 'strict', headerHTMLDeclaration will
	 * create a doctype definition that uses the strict doctype,
	 * otherwise it will use the transitional doctype.  
	 * @var string $doctype
	 */
	var $doctype = 'transitional';

	/**
	 * The default main page content 
	 * @var      string $rootindex
	 */
	var $rootindex = 'index_std.php';

	/**
	 * The base directory of the theme in the servers file system
	 * @var      string $themedir
	 */ 
	var $themedir;

	/**
	 * The base url of the theme
	 * @var      string $themeurl
	 */ 
	var $themeurl;

	/**
	 * The base directory of the image files in the servers file system
	 * @var      string $imgdir
	 */ 
	var $imgdir;

	/**
	 * The base url of the image files
	 * @var      string $imgbaseurl
	 */ 
	var $imgbaseurl;

	/**
	 * The base directory of the js files in the servers file system
	 * @var      string $jsdir
	 */ 
	var $jsdir;

	/**
	 * The base url of the js files
	 * @var      string $jsbaseurl
	 */ 
	var $jsbaseurl;

	/*
	 * kept for backwards compatibility
	 */
	/**
	 * The base directory of the theme
	 * @var string $themeroot
	 * @todo: remove in 5.0
	 * @deprecated deprecated since 4.9
	 */
	var $imgroot;

	/**
	 * The navigation object that provides the basic links. Should
	 * not be modified.
	 */
	var $navigation;


	/**
	 * The color bars in pm reporting
	 */
	var $COLOR_LTBACK1 = '#C0C0C0';


	var $js = array();
	var $js_min = array();
	var $javascripts = array();
	var $css = array();
	var $css_min = array();
	var $stylesheets = array();

	/**
	 * Layout() - Constructor
	 */
	function Layout() {
		// parent constructor
		$this->Error();

		$this->navigation = new Navigation();

		// determine rootindex
		if ( file_exists(forge_get_config('custom_path') . '/index_std.php') ) {
			$this->rootindex = forge_get_config('custom_path') . '/index_std.php';
		} else {
			$this->rootindex = $GLOBALS['gfwww'].'index_std.php';
		}

		// determine theme{dir,url}
		$this->themedir = forge_get_config('themes_root') . '/' . forge_get_config('default_theme') . '/';
		if (!file_exists ($this->themedir)) {
			html_error_top(_("Can't find theme directory!"));
			return;
		}
		$this->themeurl = util_make_url('themes/' . forge_get_config('default_theme') . '/');

		// determine {css,img,js}{url,dir}
		if (file_exists ($this->themedir . 'images/')) {
			$this->imgdir = $this->themedir . 'images/';
			$this->imgbaseurl = $this->themeurl . 'images/';
		} else {
			$this->imgdir = $this->themedir;
			$this->imgbaseurl = $this->themeurl;
		}

		if (file_exists ($this->themedir . 'js/')) {
			$this->jsdir = $this->themedir . 'js/';
			$this->jsbaseurl = $this->themeurl . 'js/';
		} else {
			$this->jsdir = $this->themedir;
			$this->jsbaseurl = $this->themeurl;
		}

		$this->addStylesheet('/themes/css/fusionforge.css');

		// for backward compatibility 
		$this->imgroot = $this->imgbaseurl;
	}

	function addJavascript($js) {
		if (isset($this->js_min[$js])) {
			$js = $this->js_min[$js];
		}
		if (!isset($this->js[$js])) {
			$this->js[$js] = true;
			$filename = $GLOBALS['fusionforge_basedir'].'/www'.$js;
			if (file_exists($filename)) {
				$js .= '?'.date ("U", filemtime($filename));
			} else {
				$filename = str_replace('/scripts/', $GLOBALS['fusionforge_basedir'].'/lib/vendor/', $js);
				if (file_exists($filename)) {
					$js .= '?'.date ("U", filemtime($filename));
				}
			}
			$this->javascripts[] = $js;
		}
	}

	function addStylesheet($css, $media='') {
		if (isset($this->css_min[$css])) {
			$css = $this->css_min[$css];
		}
		if (!isset($this->css[$css])) {
			$this->css[$css] = true;
			$filename = $GLOBALS['fusionforge_basedir'].'/www'.$css;
			if (file_exists($filename)) {
				$css .= '?'.date ("U", filemtime($filename));
			} else {
				$filename = str_replace('/scripts/', $GLOBALS['fusionforge_basedir'].'/lib/vendor/', $css);
				if (file_exists($filename)) {
					$css .= '?'.date ("U", filemtime($filename));
				}
			}
			$this->stylesheets[] = array('css' => $css, 'media' => $media);
		}
	}

	function getJavascripts() {
		$code = '';
		foreach ($this->javascripts as $js) {
			$code .= '    <script type="text/javascript" src="'.$js.'"></script>'."\n";
		}
		return $code;
	}

	function getStylesheets() {
		$code = '';
		foreach ($this->stylesheets as $c) {
			if ($c['media']) {
				$code .= '    <link rel="stylesheet" type="text/css" href="'.$c['css'].'" media="'.$c['media'].'" />'."\n";
			} else {
				$code .= '    <link rel="stylesheet" type="text/css" href="'.$c['css'].'"/>'."\n";
			}
		}
		return $code;
	}

	/** 
	 * header() - generates the complete header of page by calling 
	 * headerStart() and bodyHeader().
	 */
	function header($params) {
		$this->headerStart($params); ?>
			<body>
			<?php
			$this->bodyHeader($params);
	}


	/**
	 * headerStart() - generates the header code for all themes up to the 
	 * closing </head>.
	 * Override any of the methods headerHTMLDeclaration(), headerTitle(), 
	 * headerFavIcon(), headerRSS(), headerSearch(), headerCSS(), or 
	 * headerJS() to adapt your theme. 
	 *
	 * @param	array	Header parameters array
	 */
	function headerStart($params) {
		$this->headerHTMLDeclaration();
		?>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?php
		$this->headerTitle($params);
		$this->headerFavIcon();
		$this->headerRSS();
		$this->headerSearch();
		$this->headerCSS();
		$this->headerJS(); 
		?>
			</head>
		<?php
	} 

	/**
	 * headerLink() - creates the link headers of the page (FavIcon, RSS and Search)
	 * @deprecated deprecated since 4.9, use the individual header-functions
	 * @todo remove in 5.0
	 */
	function headerLink() {
		$this->headerFavIcon();
		$this->headerRSS();
		$this->headerSearch();
	}

	/**
	 * headerHTMLDeclaration() - generates the HTML declaration, i.e. the
	 * XML declaration, the doctype definition, and the opening <html>. 
	 *
	 */
	function headerHTMLDeclaration() {
		print '<?xml version="1.0" encoding="utf-8"?>'."\n";
		if (isset($this->doctype) && $this->doctype=='strict') {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
		} else {
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
		} 
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'
			. _('en') . '" lang="' . _('en') . '">'."\n";
	}

	/**
	 * headerTitle() - creates the <title> header 
	 *
	 * @param	array	Header parameters array
	 */
	function headerTitle($params) {
		echo $this->navigation->getTitle($params);
	}


	/**
	 * headerFavIcon() - creates the favicon <link> headers.
	 *
	 */
	function headerFavIcon() {
		echo $this->navigation->getFavIcon();
	}

	/**
	 * headerRSS() - creates the RSS <link> headers.
	 *
	 */
	function headerRSS() {
		echo $this->navigation->getRSS();
	}

	/**
	 * headerSearch() - creates the search <link> header.
	 *
	 */
	function headerSearch() {
		echo '<link rel="search" title="' 
			. forge_get_config ('forge_name').'" href="' 
			. util_make_url ('/export/search_plugin.php') 
			. '" type="application/opensearchdescription+xml"/>'."\n";
	}

	/** 
	 * Create the CSS headers for all cssfiles in $cssfiles and
	 * calls the plugin cssfile hook.
	 */
	function headerCSS() {
		plugin_hook ('cssfile',$this);
		echo $this->getStylesheets();
	}

	/**
	 * headerJS() - creates the JS headers and calls the plugin javascript hook
	 * @todo generalize this
	 */
	function headerJS() {
		echo '
<script type="text/javascript" src="/scripts/prototype/prototype.js"></script>
                        <script type="text/javascript" src="/scripts/scriptaculous/scriptaculous.js"></script>
                        <script type="text/javascript" src="/scripts/codendi/Tooltip.js"></script>
                        <script type="text/javascript" src="/scripts/codendi/LayoutManager.js"></script>
                        <script type="text/javascript" src="/scripts/codendi/ReorderColumns.js"></script>
                        <script type="text/javascript" src="/scripts/codendi/codendi-1236793993.js"></script>
                        <script type="text/javascript" src="/scripts/codendi/validate.js"></script>
			<script type="text/javascript" src="'. util_make_uri('/js/common.js') .'"></script>
			<script type="text/javascript">';
		plugin_hook ("javascript",false);
		echo '
			</script>';
		plugin_hook ("javascript_file",false);
		echo $this->getJavascripts();
	}

	function bodyHeader($params){
		?>
			<div class="header">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" id="headertable">
			<tr>
			<td><a href="<?php echo util_make_url ('/'); ?>"><?php echo html_image('logo.png',198,52,array('border'=>'0')); ?></a></td>
			<td><?php $this->searchBox(); ?></td>
			<td align="right"><?php
			$items = $this->navigation->getUserLinks();
		for ($j = 0; $j < count($items['titles']); $j++) {
			echo util_make_link($items['urls'][$j], $items['titles'][$j], array('class'=>'lnkutility'), true);
		}

		$params['template'] = ' {menu}';
		plugin_hook ('headermenu', $params);

		$this->quickNav();

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
			<td align="left" class="toptab" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/topleft.png" height="9" width="9" alt="" /></td>
			<td class="toptab" width="30"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="30" height="1" alt="" /></td>
			<td class="toptab"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="1" height="1" alt="" /></td>
			<td class="toptab" width="30"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="30" height="1" alt="" /></td>
			<td align="right" class="toptab" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/topright.png" height="9" width="9" alt="" /></td>
			</tr>

			<tr>

			<!-- Outer body row -->

			<td class="toptab"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="10" height="1" alt="" /></td>
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
			<td align="left" class="projecttab" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/topleft-inner.png" height="9" width="9" alt="" /></td>
			<td class="projecttab" ><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="1" height="1" alt="" /></td>
			<td align="right" class="projecttab"  width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/topright-inner.png" height="9" width="9" alt="" /></td>
			</tr>

			<tr>
			<td class="projecttab" ><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="10" height="1" alt="" /></td>
			<td valign="top" width="99%" class="projecttab">

			<?php

	}

	function footer($params) {

		?>

			<!-- end main body row -->

			</td>
			<td width="10" class="footer3" ><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="2" height="1" alt="" /></td>
			</tr>
			<tr>
			<td align="left" class="footer1" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/bottomleft-inner.png" height="11" width="11" alt="" /></td>
			<td class="footer3"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="1" height="1" alt="" /></td>
			<td align="right" class="footer1" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/bottomright-inner.png" height="11" width="11" alt="" /></td>
			</tr>
			</table>

			<!-- end inner body row -->

			</td>
			<td width="10" class="footer2"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="2" height="1" alt="" /></td>
			</tr>
			<tr>
			<td align="left" class="footer2" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/bottomleft.png" height="9" width="9" alt="" /></td>
			<td class="footer2" colspan="3"><img src="<?php echo $this->imgbaseurl; ?>clear.png" width="1" height="1" alt="" /></td>
			<td align="right" class="footer2" width="9"><img src="<?php echo $this->imgbaseurl; ?>tabs/bottomright.png" height="9" width="9" alt="" /></td>
			</tr>
			</table>
			<?php
			$this->footerEnd($params);
	}

	function footerEnd($params) { ?>

		<!-- PLEASE LEAVE "Powered By FusionForge" on your site -->
			<div align="right">
			<?php echo $this->navigation->getPoweredBy(); ?>
			</div>

			<?php echo $this->navigation->getShowSource(); ?>

			</body>
			</html>
			<?php

	}

	function getRootIndex() {
		return $this->rootindex;
	}

	/**
	 * boxTop() - Top HTML box.
	 *
	 * @param	string	Box title
	 * @return	string	the html code
	 */
	function boxTop($title) {
		return '
			<!-- Box Top Start -->

			<table cellspacing="0" cellpadding="0" width="100%" border="0" style="background:url('.$this->imgbaseurl.'vert-grad.png)">
			<tr class="align-center">
			<td valign="top" align="right" width="10" style="background:url('.$this->imgbaseurl.'box-topleft.png)"><img src="'.$this->imgbaseurl.'clear.png" width="10" height="20" alt="" /></td>
			<td width="100%" style="background:url('.$this->imgbaseurl.'box-grad.png)"><span class="titlebar">'.$title.'</span></td>
			<td valign="top" width="10" style="background:url('.$this->imgbaseurl.'box-topright.png)"><img src="'.$this->imgbaseurl.'clear.png" width="10" height="20" alt="" /></td>
			</tr>
			<tr>
			<td colspan="3">
			<table cellspacing="2" cellpadding="2" width="100%" border="0">
			<tr align="left">
			<td colspan="2">

			<!-- Box Top End -->';
	}

	/**
	 * boxMiddle() - Middle HTML box.
	 *
	 * @param	string	Box title
	 * @return	string	The html code
	 */
	function boxMiddle($title) {
		return '
			<!-- Box Middle Start -->
			</td>
			</tr>
			<tr class="align-center">
			<td colspan="2" style="background:url('.$this->imgbaseurl.'box-grad.png)"><span class="titlebar">'.$title.'</span></td>
			</tr>
			<tr align="left">
			<td colspan="2">
			<!-- Box Middle End -->';
	}

	/**
	 * boxBottom() - Bottom HTML box.
	 *
	 * @return	string	the html code
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
	 * boxGetAltRowStyle() - Get an alternating row style for tables.
	 *
	 * @param	int	Row number
	 * @return	string	the class code
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
	 * @param	array	The array of titles
	 * @param	array	The array of title links
	 * @param	string	The css classes to add (optional)
	 * @param	string	The id of the table (needed by sortable for example)
	 * @param	array	specific class for th column
	 * @return	string	the html code
	 */
	function listTableTop ($titleArray, $linksArray=false, $class='', $id='', $thClassArray=array()) {
		$args = '';
		if ($class) {
			$args .= ' class="listing '.$class.'"';
		} else {
			$args .= ' class="listing full"';
		}
		if ($id) {
			$args .= ' id="'.$id.'"';
		}
		$return = "\n".
			'<table'.$args.'>'.
			'<thead><tr>';

		$count=count($titleArray);
		for ($i=0; $i<$count; $i++) {
			$th = '';
			if ($thClassArray && $thClassArray[$i]) {
				$th .= ' class="'.$thClassArray[$i].'"';
			}
			$cell = $titleArray[$i];
			if ($linksArray) {
				$cell = util_make_link($linksArray[$i],$titleArray[$i]);
			}
			$return .= "\n".' <th'.$th.'>'.$cell.'</th>';
		}
		return $return .= "\n".'</tr></thead>'."\n".'<tbody>';
	}

	function listTableBottom() {
		return '</tbody>'."\n".'</table>';
	}

	function outerTabs($params) {
		$menu = $this->navigation->getSiteMenu();
		echo $this->tabGenerator($menu['urls'], $menu['titles'], false, $menu['selected'], '');
	}

	/**
	 * Prints out the quicknav menu, contained here in case we
	 * want to allow it to be overridden.
	 */
	function quickNav() {
		if (!session_loggedin()) {
			return;
		} else {
			// get all projects that the user belongs to
			$groups = session_get_user()->getGroups();

			if (count($groups) < 1) {
				return;
			} else {
				sortProjectList($groups);

				echo '
					<form id="quicknavform" name="quicknavform" action=""><div>
					<select name="quicknav" id="quicknav" onChange="location.href=document.quicknavform.quicknav.value">
					<option value="">'._('Quick Jump To...').'</option>';

				foreach ($groups as $g) {
					$group_id = $g->getID();
					$menu =& $this->navigation->getProjectMenu($group_id);

					echo '
						<option value="' . $menu['starturl'] . '">' 
						. $menu['name'] .'</option>';

					for ($j = 0; $j < count($menu['urls']); $j++) {
						echo '
							<option value="' . $menu['urls'][$j] .'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' 
							. $menu['titles'][$j] . '</option>';
						if (@$menu['adminurls'][$j]) {
							echo  '
								<option value="' . $menu['adminurls'][$j] 
								. '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' 
								. _('Admin') . '</option>';
						}
					}
				}
				echo '
					</select>
					</div></form>';
			}
		}
	}

	/**
	 * projectTabs() - Prints out the project tabs, contained here in case
	 * we want to allow it to be overriden.
	 *
	 * @param	string	Is the tab currently selected
	 * @param	string	Is the group we should look up get title info
	 */
	function projectTabs($toptab, $group_id) {
		// get group info using the common result set
		$menu =& $this->navigation->getProjectMenu($group_id, $toptab);
		echo $this->tabGenerator($menu['urls'], $menu['titles'], true, $menu['selected'], 'white');
	}

	function tabGenerator($TABS_DIRS,$TABS_TITLES,$nested=false,$selected=false,$sel_tab_bgcolor='white',$total_width='100%') {

		$count=count($TABS_DIRS);
		$width=intval((100/$count));

		$return = '';
		$return .= '
			<!-- start tabs -->
			<table class="tabGenerator" ';

		if ($total_width != '100%') {
			$return .= 'style="width:' . $total_width . ';"';
		}
		$return .= ">\n";
		$return .= '<tr>';
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
					<td '.$rowspan.'valign="top" width="10" style="background:url('.$this->imgbaseurl . 'theme-'.$inner.'-end-'.(($issel) ? '' : 'not').'selected.png)">'.
					'<img src="'.$this->imgbaseurl . 'clear.png" height="25" width="10" alt="" /></td>'.
					'<td '.$rowspan.'style="background:url('.$this->imgbaseurl . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink')),true).'</td>';
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
					<td '.$rowspan.'colspan="2" valign="top" width="20" style="background:url('.$this->imgbaseurl . 'theme-'.$inner.'-'.(($wassel) ? '' : 'not').'selected-'.(($issel) ? '' : 'not').'selected.png)">'.
					'<img src="'.$this->imgbaseurl . 'clear.png" height="2" width="20" alt="" /></td>'.
					'<td '.$rowspan.'style="background:url('.$this->imgbaseurl . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink')),true).'</td>';
				//
				//	Last graphic on right-side
				//
				$return .= '
					<td '.$rowspan.'valign="top" width="10" style="background:url('.$this->imgbaseurl . 'theme-'.$inner.'-'.(($issel) ? '' : 'not').'selected-end.png)">'.
					'<img src="'.$this->imgbaseurl . 'clear.png" height="2" width="10" alt="" /></td>';

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
					<td '.$rowspan.'colspan="2" valign="top" width="20" style="background:url('.$this->imgbaseurl . 'theme-'.$inner.'-'.(($wassel) ? '' : 'not').'selected-'.(($issel) ? '' : 'not').'selected.png)">'.
					'<img src="'.$this->imgbaseurl . 'clear.png" height="2" width="20" alt="" /></td>'.
					'<td '.$rowspan.'style="background:url('.$this->imgbaseurl . $bgimg.')" width="'.$width.'%" align="center">'.util_make_link ($TABS_DIRS[$i],$TABS_TITLES[$i],array('class'=>(($issel)?'tabsellink':'tablink')),true).'</td>';

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
			$return .= '<td colspan="'.$beg_cols.'" height="1" class="notSelTab"><img src="'.$this->imgbaseurl.'clear.png" height="1" width="10" alt="" /></td>';
		}
		$return .= '<td colspan="3" height="1" class="selTab"><img src="'.$this->imgbaseurl.'clear.png" height="1" width="10" alt="" /></td>';
		if ($end_cols > 0) {
			$return .= '<td colspan="'.$end_cols.'" height="1" class="notSelTab"><img src="'.$this->imgbaseurl.'clear.png" height="1" width="10" alt="" /></td>';
		}
		$return .= '</tr>';


		return $return.'
			</table> 

			<!-- end tabs -->
			';
	}

	function searchBox() {
		echo $this->navigation->getSearchBox();
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
	 * @param	array	The array of titles.
	 * @param	array	The array of title links.
	 * @return	string	Html to build a submenu.
	 */
	function printSubMenu ($title_arr,$links_arr) {
		$count=count($title_arr);
		$count--;

		$return = '';
		for ($i=0; $i<$count; $i++) {
			$return .= util_make_link($links_arr[$i],$title_arr[$i]).' | ';
		}
		$return .= util_make_link($links_arr[$i],$title_arr[$i]);
		return $return;
	}

	/**
	 * subMenu() - Takes two array of titles and links and build a menu.
	 *
	 * @param	array	The array of titles.
	 * @param	array	The array of title links.
	 * @return	string	Html to build a submenu.
	 */
	function subMenu ($title_arr,$links_arr) {
		$return  = $this->beginSubMenu() ;
		$return .= $this->printSubMenu($title_arr,$links_arr) ;
		$return .= $this->endSubMenu() ;
		return $return;
	}

	/**
	 * multiTableRow() - create a mutlilevel row in a table
	 *
	 * @param	string	the row attributes
	 * @param	array	the array of cell data, each element is an array,
	 *				the first item being the text,
	 *				the subsequent items are attributes (dont include
	 *				the bgcolor for the title here, that will be
	 *				handled by $istitle
	 * @param	boolean	is this row part of the title ?
	 * @return	string	the html code
	 */
	function multiTableRow($row_attr, $cell_data, $istitle) {
		$return= '
			<tr '.$row_attr;
		if ( $istitle ) {
			$return .=' class="align-center multiTableRowTitle"';
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
				<div class="feedback">'.strip_tags($feedback, '<br>').'</div>';
		}
	}
	/**
	 * warning_msg() - returns the htmlized warning string when an action is performed.
	 *
	 * @param string msg string
	 * @return string htmlized warning
	 */
	function warning_msg($msg) {
		if (!$msg) {
			return '';
		} else {
			return '
				<div class="warning_msg">'.strip_tags($msg, '<br>').'</div>';
		}
	}

	/**
	 * error_msg() - returns the htmlized error string when an action is performed.
	 *
	 * @param string msg string
	 * @return string htmlized error
	 */
	function error_msg($msg) {
		if (!$msg) {
			return '';
		} else {
			return '
				<div class="error">'.strip_tags($msg, '<br>').'</div>';
		}
	}


	/**
	 * getThemeIdFromName()
	 *
	 * @param	string  the dirname of the theme
	 * @return	integer the theme id	
	 */
	function getThemeIdFromName($dirname) {
		$res = db_query_params ('SELECT theme_id FROM themes WHERE dirname=$1',
				array ($dirname));
		return db_result($res,0,'theme_id');
	}

	function confirmBox($msg, $params, $buttons, $image='*none*') {
		if ($image == '*none*') {
			$image = html_image('stop.png','48','48',array());
		}

		foreach ($params as $b => $v) {
			$prms[] = '<input type="hidden" name="'.$b.'" value="'.$v.'" />'."\n";
		}
		$prm = join('	 	', $prms);	 

		foreach ($buttons as $b => $v) {
			$btns[] = '<input type="submit" name="'.$b.'" value="'.$v.'" />'."\n";
		}
		$btn = join('	 	&nbsp;&nbsp;&nbsp;'."\n	 	", $btns);

		return '
			<div id="infobox" style="margin-top: 15%; margin-left: 15%; margin-right: 15%; text-align: center;">
			<table align="center">
			<tr>
			<td>'.$image.'</td>
			<td>'.$msg.'<br/></td>
			</tr>
			<tr>
			<td colspan="2" align="center">
			<br />
			<form action="' . getStringFromServer('PHP_SELF') . '" method="get" >
			'.$prm.'
			'.$btn.'
			</form>
			</td>
			</tr>
			</table>
			</div>
			';
	}

	function html_input($name, $id = '', $label = '', $type = 'text', $value = '', $extra_params = '') {
		if (!$id) {
			$id = $name;
		}
		$return = '<div class="field-holder">
			';
		if ($label) {
			$return .= '<label for="' . $id . '">' . $label . '</label>
				';
		}
		$return .= '<input id="' . $id . '" type="' . $type . '"';
		//if input is a submit then name is not present
		if ($name) {
			$return .= ' name="' . $name . '"';
		}
		if ($value) {
			$return .= ' value="' . $value . '"';
		}
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '/>
			</div>';
		return $return;
	}

	function html_checkbox($name, $value, $id = '', $label = '', $checked = '', $extra_params = '') {
		if (!$id) {
			$id = $name;
		}
		$return = '<div class="field-holder">
			';
		$return .= '<input name="' . $name . '" id="' . $id . '" type="checkbox" value="' . $value . '" ';
		if ($checked) {
			$return .= 'checked="checked" ';
		}
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '/>';
		if ($label) {
			$return .= '<label for="' . $id . '">' . $label . '</label>
				';
		}
		$return .= '</div>';
		return $return;
	}

	function html_text_input_img_submit($name, $img_src, $id = '', $label = '', $value = '', $img_title = '', $img_alt = '', $extra_params = '', $img_extra_params = '') {
		if (!$id) {
			$id = $name;
		}
		if (!$img_title) {
			$img_title = $name;
		}
		if (!$img_alt) {
			$img_alt = $img_title;
		}
		$return = '<div class="field-holder">
			';
		if ($label) {
			$return .= '<label for="' . $id . '">' . $label . '</label>
				';
		}
		$return .= '<input id="' . $id . '" type="text" name="' . $name . '"';
		if ($value) {
			$return .= ' value="' . $value . '"';
		}
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '/>
			<input type="image" id="' . $id . '_submit" src="' . $this->imgbaseurl . $img_src . '" alt="' . $img_alt . '" title="' . $img_title . '"';
		if (is_array($img_extra_params)) {
			foreach ($img_extra_params as $key => $img_extra_params_value) {
				$return .= $key . '="' . $img_extra_params_value . '" ';
			}
		}
		$return .= '/>
			</div>';
		return $return;
	}

	function html_select($vals, $name, $label = '', $id = '', $checked_val = '', $text_is_value = false, $extra_params = '') {
		if (!$id) {
			$id = $name;
		}
		$return = '<div class="field-holder">
			';
		if ($label) {
			$return .= '<label for="' . $id . '">' . $label . '</label>
				';
		}
		$return .= '<select name="' . $name . '" id="' . $id . '" ';
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '>';
		$rows = count($vals);
		for ($i = 0; $i < $rows; $i++) {
			if ( $text_is_value ) {
				$return .= '
					<option value="' . $vals[$i] . '"';
				if ($vals[$i] == $checked_val) {
					$return .= ' selected="selected"';
				}
			} else {
				$return .= '
					<option value="' . $i . '"';
				if ($i == $checked_val) {
					$return .= ' selected="selected"';
				}
			}
			$return .= '>' . htmlspecialchars($vals[$i]) . '</option>';
		}
		$return .= '
			</select>
			</div>';
		return $return;
	}

	function html_textarea($name, $id = '', $label = '', $value = '',  $extra_params = '') {
		if (!$id) {
			$id = $name;
		}
		$return = '<div class="field-holder">
			';
		if ($label) {
			$return .= '<label for="' . $id . '">' . $label . '</label>
				';
		}
		$return .= '<textarea id="' . $id . '" name="' . $name . '" ';
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '>';
		if ($value) {
			$return .= $value;
		}
		$return .= '</textarea>
			</div>';
		return $return;
	}

	/**
	 * @todo use listTableTop and make this function deprecated ?
	 */
	function html_table_top($cols, $summary = '', $class = '', $extra_params = '') {
		$return = '<table summary="' . $summary . '" ';
		if ($class) {
			$return .= 'class="' . $class . '" ';
		}
		if (is_array($extra_params)) {
			foreach ($extra_params as $key => $extra_params_value) {
				$return .= $key . '="' . $extra_params_value . '" ';
			}
		}
		$return .= '>';
		$return .= '<thead><tr>';
		$nbCols = count($cols);
		for ($i = 0; $i < $nbCols; $i++) {
			$return .= '<th scope="col">' . $cols[$i] . '</th>';
		}
		$return .= '</tr></thead>';
		return $return;
	}

	function getMonitorPic($title = '', $alt = '') {
		return $this->getPicto('ic/mail16w.png', $title, $alt, '15', '15');
	}

	function getReleaseNotesPic($title = '', $alt = '') {
		return $this->getPicto('ic/manual16c.png', $title, $alt, '15', '15');
	}

	/* no picto for download */
	function getDownloadPic($title = '', $alt = '') {
		return $this->getPicto('ic/save.png', $title, $alt, '15', '15');
	}

	function getHomePic($title = '', $alt = '') {
		return $this->getPicto('ic/home16b.png', $title, $alt);
	}

	function getFollowPic($title = '', $alt = '') {
		return $this->getPicto('ic/tracker20g.png', $title, $alt);
	}

	function getForumPic($title = '', $alt = '') {
		return $this->getPicto('ic/forum20g.png', $title, $alt);;
	}

	function getDocmanPic($title = '', $alt = '') {
		return $this->getPicto('ic/docman16b.png', $title, $alt);
	}

	function getMailPic($title = '', $alt = '') {
		return $this->getPicto('ic/mail16b.png', $title, $alt);
	}

	function getPmPic($title = '', $alt = '') {
		return $this->getPicto('ic/taskman20g.png', $title, $alt);
	}

	function getSurveyPic($title = '', $alt = '') {
		return $this->getPicto('ic/survey16b.png', $title, $alt);
	}

	function getScmPic($title = '', $alt = '') {
		return $this->getPicto('ic/cvs16b.png', $title, $alt);
	}

	function getFtpPic($title = '', $alt = '') {
		return $this->getPicto('ic/ftp16b.png', $title, $alt);
	}

	function getPicto($url, $title, $alt, $width = '20', $height = '20') {
		if (!$alt) {
			$alt = $title;   
		}
		return html_image($url, $width, $height, array('title'=>$title, 'alt'=>$alt));
	}

	/**
	 * toSlug() - protect a string to be used as a link or an anchor
	 *
	 * @param   string $string  the string used as a link or an anchor
	 * @param   string $space   the caracter used as a replacement for a space
	 * @return  a protected string with only alphanumeric caracters
	 */
	function toSlug($string, $space = "-") {
		if (function_exists('iconv')) {
			$string = @iconv('UTF-8', 'ASCII//TRANSLIT', $string);
		}
		$string = preg_replace("/[^a-zA-Z0-9- ]/", "-", $string);
		$string = strtolower($string);
		$string = str_replace(" ", $space, $string);
		return $string;
	}

	function widget(&$widget, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type) {
		$element_id = 'widget_'. $widget->id .'-'. $widget->getInstanceId();
		echo '<div class="widget" id="'. $element_id .'">';
		echo '<div class="widget_titlebar '. ($readonly?'':'widget_titlebar_handle') .'">';
		echo '<div class="widget_titlebar_title">'. $widget->getTitle() .'</div>';
		if (!$readonly) {
			echo '<div class="widget_titlebar_close"><a href="/widgets/updatelayout.php?owner='. $owner_type.$owner_id .'&amp;action=widget&amp;name['. $widget->id .'][remove]='. $widget->getInstanceId() .'&amp;column_id='. $column_id .'&amp;layout_id='. $layout_id .'">'. $this->getPicto('ic/close.png', 'Close','Close') .'</a></div>';
			if ($is_minimized) {
				echo '<div class="widget_titlebar_maximize"><a href="/widgets/updatelayout.php?owner='. $owner_type.$owner_id .'&amp;action=maximize&amp;name['. $widget->id .']='. $widget->getInstanceId() .'&amp;column_id='. $column_id .'&amp;layout_id='. $layout_id .'">'. $this->getPicto($this->_getTogglePlusForWidgets(),  'Maximize', 'Maximize') .'</a></div>';
			} else {
				echo '<div class="widget_titlebar_minimize"><a href="/widgets/updatelayout.php?owner='. $owner_type.$owner_id .'&amp;action=minimize&amp;name['. $widget->id .']='. $widget->getInstanceId() .'&amp;column_id='. $column_id .'&amp;layout_id='. $layout_id .'">'. $this->getPicto($this->_getToggleMinusForWidgets(),  'Minimize', 'Minimize') .'</a></div>';
			}
			if (strlen($widget->hasPreferences())) {
				echo '<div class="widget_titlebar_prefs"><a href="/widgets/updatelayout.php?owner='. $owner_type.$owner_id .'&amp;action=preferences&amp;name['. $widget->id .']='. $widget->getInstanceId() .'&amp;layout_id='. $layout_id .'">'. _('Preferences') .'</a></div>';
			}
		}
		if ($widget->hasRss()) {
			echo '<div class="widget_titlebar_rss"><a href="'.$widget->getRssUrl($owner_id, $owner_type).'">rss</a></div>';
		}
		echo '</div>';
		$style = '';
		if ($is_minimized) {
			$style = 'display:none;';
		}
		echo '<div class="widget_content" style="'. $style .'">';
		if (!$readonly && $display_preferences) {
			echo '<div class="widget_preferences">'. $widget->getPreferencesForm($layout_id, $owner_id, $owner_type) .'</div>';
		}
		if ($widget->isAjax()) {
			echo '<div id="'. $element_id .'-ajax">';
			echo '<noscript><iframe width="99%" frameborder="0" src="'. $widget->getIframeUrl($owner_id, $owner_type) .'"></iframe></noscript>';
			echo '</div>';
		} else {
			echo $widget->getContent();
		}
		echo '</div>';
		if ($widget->isAjax()) {
			echo '<script type="text/javascript">'."
				document.observe('dom:loaded', function () {
						$('$element_id-ajax').update('<div style=\"text-align:center\">". $this->getPicto('ic/spinner.gif','spinner','spinner') ."</div>');
						new Ajax.Updater('$element_id-ajax', 
							'". $widget->getAjaxUrl($owner_id, $owner_type) ."'
							);
						});
			</script>";
		}
		echo '</div>';
	}

	function _getTogglePlusForWidgets() {
		return 'ic/toggle_plus.png';
	}

	function _getToggleMinusForWidgets() {
		return 'ic/toggle_minus.png';
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
