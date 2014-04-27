<?php
/**
 * FusionForge Funky Theme
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010, Marc-Etienne Vargenau, Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfwww.'include/Layout.class.php';

define('TOP_TAB_HEIGHT', 30);
define('BOTTOM_TAB_HEIGHT', 22);

class Theme extends Layout {

	function Theme() {
		// Parent constructor
		$this->Layout();
		$this->themeurl = util_make_uri('themes/funky-wOw/');
		$this->imgbaseurl = $this->themeurl . 'images/';
		$this->imgroot = $this->imgbaseurl;
		$this->addStylesheet('/themes/funky-wOw/css/theme.css');
		$this->addStylesheet('/themes/funky-wOw/css/theme-pages.css');
	}

	function bodyHeader($params) {

		if (!isset($params['h1']) && isset($params['title'])) {
			$params['h1'] = $params['title'];
		}

		if (!isset($params['title'])) {
			$params['title'] = forge_get_config('forge_name');
		} else {
			$params['title'] = $params['title'] . " - ".forge_get_config('forge_name');
		}

		echo html_ao('table', array('id' => 'header', 'class' => 'fullwidth'));
		echo html_ao('tr');
		echo html_ao('td', array('id' => 'header-col1'));
		echo util_make_link('/', html_image('/header/top-logo.png', null, null, array('alt'=>'FusionForge Home')));
		echo html_ac(html_ap() -1);
		echo html_ao('td', array('id' => 'header-col2'));

		$items = $this->navigation->getUserLinks();
		for ($j = 0; $j < count($items['titles']); $j++) {
			$links[] = util_make_link($items['urls'][$j], $items['titles'][$j], array('class' => 'userlink'), true);
		}
		echo implode(' | ', $links);
		plugin_hook('headermenu', $params);

		echo html_ac(html_ap() -2);
		echo html_ao('tr');
		echo html_ao('td', array('id' => 'header-line2', 'colspan' => 2));
		$this->quickNav();
		$this->searchBox();
		echo html_ac(html_ap() -3);
		$this->outerTabs($params);
		echo '<!-- inner tabs -->' . "\n";
		echo html_ao('div', array('class' => 'innertabs'));
		if (isset($params['group']) && $params['group']) {
			$this->projectTabs($params['toptab'], $params['group']);
		}

		echo html_ac(html_ap() -1);
		echo html_ao('div', array('id' => 'maindiv'));

		plugin_hook('message');

		if(isset($GLOBALS['error_msg']) && $GLOBALS['error_msg']) {
			echo $this->error_msg($GLOBALS['error_msg']);
		}
		if(isset($GLOBALS['warning_msg']) && $GLOBALS['warning_msg']) {
			echo $this->warning_msg($GLOBALS['warning_msg']);
		}
		if(isset($GLOBALS['feedback']) && $GLOBALS['feedback']) {
			echo $this->feedback($GLOBALS['feedback']);
		}

		if (isset($params['h1'])) {
			echo html_e('h1', array(), $params['h1'], false);
		} elseif (isset($params['title'])) {
			echo html_e('h1', array('class' => 'hide'), $params['title'], false);
		}
		if (isset($params['submenu']))
			echo $params['submenu'];
	}

	function bodyFooter($params) {
		echo html_ac(html_ap() -1).'<!-- id="maindiv" -->' . "\n";
	}

	function footer($params = array()) {
		$this->bodyFooter($params);
		echo html_ao('div', array('class' => 'footer'));
		echo $this->navigation->getPoweredBy();
		echo $this->navigation->getShowSource();
		echo html_e('div', array('style' => 'clear:both'), '', false);
		echo html_ac(html_ap() -1);
		plugin_hook('webanalytics_url');
		echo html_ac(html_ap() -1);
		echo '</html>' . "\n";
	}

	/**
	 * boxTop() - Top HTML box
	 *
	 * @param	string	$title	Box title
	 * @param	string	$id
	 * @return	string
	 */
	function boxTop($title, $id = '') {
		if ($id) {
			$id = $this->toSlug($id);
			$idid = $id;
			$idtitle = $id.'-title"';
			$idtcont = $id.'-title-content"';
		} else {
			$idid = "";
			$idtitle = "";
			$idtcont = "";
		}

		$t_result = '';
		$t_result .= html_ao('div', array('id' => $idid, 'class' => 'box-surround'));
		$t_result .= html_ao('div', array('id' => $idtitle, 'class' => 'box-title'));
		$t_result .= html_e('div', array('id' => $idtcont, 'class' => 'box-title-content'), $title, false);
		$t_result .= html_ac(html_ap() -1);
		return $t_result;
	}

	/**
	 * boxMiddle() - Middle HTML box
	 *
	 * @param	string	$title	Box title
	 * @param	string	$id
	 * @return	string
	 */
	function boxMiddle($title, $id = '') {
		if ($id) {
			$id = $this->toSlug($id);
			$idtitle = $id.'-title"';
		} else {
			$idtitle = "";
		}

		return html_e('div', array('id' => $idtitle, 'class' => 'box-middle'), $title, false);
	}

	/**
	 * boxContent() - Content HTML box
	 *
	 * @param	string	$content	Box content
	 * @param	string	$id
	 * @return	string
	 */
	function boxContent($content, $id = '') {
		if ($id) {
			$id = $this->toSlug($id);
			$idcont = $id.'-content"';
		} else {
			$idcont = "";
		}

		return html_e('div', array('id' => $idcont, 'class' => 'box-content'), $content, false);
	}

	/**
	 * boxBottom() - Bottom HTML box
	 *
	 * @return	string
	 */
	function boxBottom() {
		return html_ac(html_ap() -1).'<!-- class="box-surround" -->'."\n";
	}

	/**
	 * boxGetAltRowStyle() - Get an alternating row style for tables
	 *
	 * @param	int	$i	Row number
	 * @param	bool $classonly Return class name only
	 * @return	string
	 */
	function boxGetAltRowStyle($i, $classonly = false) {
		if ($i % 2 == 0)
			$ret = 'bgcolor-white';
		else
			$ret = 'bgcolor-grey';
		if ($classonly)
			return $ret;
		else
			return 'class="'.$ret.'"';
	}

	function tabGenerator($TABS_DIRS, $TABS_TITLES, $TABS_TOOLTIPS, $nested=false,  $selected=false, $sel_tab_bgcolor='WHITE',  $total_width='100%') {
		$count = count($TABS_DIRS);

		if ($count < 1) {
			return '';
		}

		global $use_tooltips;

		if ($use_tooltips) {
			echo html_ao('script', array('type' => 'text/javascript'));
			echo '	//<![CDATA[
				jQuery(document).ready(
					function() {
						jQuery(document).tooltip();
					}
				);
			//]]>'."\n";
			echo html_ac(html_ap() -1);
		}

		$return = '<!-- start tabs -->'."\n";
		$attrs = array('class' => 'tabGenerator fullwidth');

		if ($total_width != '100%')
			$attrs['style'] = 'width:' . $total_width;

		$return .= html_ao('table', $attrs);
		$return .= html_ao('tr');

		$accumulated_width = 0;

		for ($i = 0; $i < $count; $i++) {
			$tabwidth = intval(ceil(($i+1)*100/$count)) - $accumulated_width;
			$accumulated_width += $tabwidth;

			// middle part
			$attrs = array();
			$attrs['class'] = 'tg-middle';
			$attrs['style'] = 'width:'.$tabwidth.'%';
			$return .= html_ao('td', $attrs);
			$return .= html_ao('a', array('href' => $TABS_DIRS[$i], 'id' => md5($TABS_DIRS[$i])));
			$attrs = array();
			if ($selected == $i)
				$attrs['class'] = 'selected';

			$return .= html_ao('span', $attrs);
			$attrs = array('title' => $TABS_TOOLTIPS[$i]);
			if ($nested)
				$attrs['class'] = 'nested';

			$return .= html_e('span', $attrs, $TABS_TITLES[$i], false);
			$return .= html_ac(html_ap() - 3);
		}

		$return .= html_ac(html_ap() -2).'<!-- end tabs -->'."\n";
		return $return;
	}

	/**
	 * beginSubMenu() - Opening a submenu.
	 *
	 * @return	string	Html to start a submenu.
	 */
	function beginSubMenu() {
		return html_ao('ul', array('class' => 'submenu'));
	}

	/**
	 * endSubMenu() - Closing a submenu.
	 *
	 * @return	string	Html to end a submenu.
	 */
	function endSubMenu() {
		return html_ac(html_ap() - 1);
	}

	/**
	 * printSubMenu() - Takes two array of titles and links and builds the contents of a menu.
	 *
	 * @param	array	$title_arr	The array of titles.
	 * @param	array	$links_arr	The array of title links.
	 * @param	array	$attr_arr	The array of attributs by link
	 * @return	string	Html to build a submenu.
	 */
	function printSubMenu($title_arr, $links_arr, $attr_arr) {
		$count  = count($title_arr) - 1;
		$return = '';

		if (!count($attr_arr)) {
			for ($i=0; $i<count($title_arr); $i++) {
				$attr_arr[] = NULL;
			}
		}
		for ($i = 0; $i < $count; $i++) {
			$return .= html_ao('li');
			$return .= html_e('span', array(), util_make_link($links_arr[$i], $title_arr[$i], $attr_arr[$i]), false);
			$return .= html_ac(html_ap() -1);
		}

		$return .= html_ao('li');
		$return .= html_e('span', array(), util_make_link($links_arr[$i], $title_arr[$i], $attr_arr[$i]), false);
		$return .= html_ac(html_ap() -1);
		return $return;
	}

	/**
	 * subMenu() - Takes two array of titles and links and build a menu.
	 *
	 * @param	array	$title_arr	The array of titles.
	 * @param	array	$links_arr	The array of title links.
	 * @param	array	$attr_arr	The array of attributes by link
	 * @return	string	Html to build a submenu.
	 */
	function subMenu($title_arr, $links_arr, $attr_arr = array()) {
		$return  = $this->beginSubMenu();
		$return .= $this->printSubMenu($title_arr, $links_arr, $attr_arr);
		$return .= $this->endSubMenu();
		return $return;
	}

	/**
	 * multiTableRow() - create a multilevel row in a table
	 *
	 * @param	array	$row_attrs	the row attributes
	 * @param	array	$cell_data	the array of cell data, each element is an array,
	 *					the first item being the text,
	 *					the subsequent items are attributes (dont include
	 *					the bgcolor for the title here, that will be
	 *					handled by $istitle
	 * @param	bool	$istitle	is this row part of the title ?
	 *
	 * @return string
	 */
	function multiTableRow($row_attrs, $cell_data, $istitle = false) {
		$ap = html_ap();
		(isset($row_attrs['class'])) ? $row_attrs['class'] .= ' ff' : $row_attrs['class'] = 'ff';
		if ( $istitle ) {
			$row_attrs['class'] .= ' align-center';
		}
		$return = html_ao('tr', $row_attrs);
		for ( $c = 0; $c < count($cell_data); $c++ ) {
			$locAp = html_ap();
			$cellAttrs = array();
			foreach (array_slice($cell_data[$c],1) as $k => $v) {
				$cellAttrs[$k] = $v;
			}
			(isset($cellAttrs['class'])) ? $cellAttrs['class'] .= ' ff' : $cellAttrs['class'] = 'ff';
			$return .= html_ao('td', $cellAttrs);
			if ( $istitle ) {
				$return .= html_ao('strong');
			}
			$return .= $cell_data[$c][0];
			if ( $istitle ) {
				$return .= html_ac(html_ap() -1);
			}
			$return .= html_ac($locAp);
		}
		$return .= html_ac($ap);
		return $return;
	}

	/**
	 * headerJS() - creates the JS headers and calls the plugin javascript hook
	 * @todo generalize this
	 */
	function headerJS() {
		echo html_e('script', array('type' => 'text/javascript', 'src' => util_make_uri('/js/common.js')), '', false);

		plugin_hook("javascript_file");

		// invoke the 'javascript' hook for custom javascript addition
		$params = array('return' => false);
		plugin_hook("javascript", $params);
		$javascript = $params['return'];
		if($javascript) {
			echo html_ao('script', array('type' => 'text/javascript')).'//<![CDATA['."\n";
			echo $javascript;
			echo "\n".'//]]'."\n";
			echo html_ac(html_ap() -1);
		}
		html_use_storage();
		html_use_coolfieldset();
		html_use_jqueryui();
		echo $this->getJavascripts();
		echo html_ao('script', array('type' => 'text/javascript'));
		echo '	//<![CDATA[
			jQuery(window).load(function(){
				setTimeout("jQuery(\'.feedback\').hide(\'slow\')", 5000);
				setInterval(function() {
						setTimeout("jQuery(\'.feedback\').hide(\'slow\')", 5000);
					}, 5000);
			});
			//]]>'."\n";
		echo html_ac(html_ap() -1);
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
