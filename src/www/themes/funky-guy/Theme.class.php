<?php
/**
 * FusionForge Funky Theme
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010, Marc-Etienne Vargenau, Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2017, Franck Villaume - TrivialDev
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

class Theme_Funky_Guy extends Layout {

	function __construct() {
		parent::__construct();
		$this->themeurl = util_make_uri('themes/funky-guy/');
		$this->imgbaseurl = $this->themeurl . 'images/';
		$this->addStylesheet('/themes/funky-guy/css/theme.css');
		$this->addStylesheet('/themes/funky-guy/css/theme-pages.css');
		$this->addStylesheet('/scripts/jquery-ui/css/overcast/jquery-ui-1.12.1.css');
		$this->addStylesheet('/scripts/jquery-ui/css/overcast/jquery-ui.structure-1.12.1.css');
		$this->addStylesheet('/scripts/jquery-ui/css/overcast/jquery-ui.theme-1.12.1.css');
	}

	/**
	 * headerJS() - creates the JS headers and calls the plugin javascript hook
	 * @todo generalize this
	 */
	function headerJS() {
		global $use_tooltips;

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
				jQuery("button").button();
				jQuery(":submit").button();
				jQuery(":reset").button();
				jQuery(":button").button();
				jQuery("#tabber").tabs();
				jQuery("input").filter(\'[type="number"]\').spinner();
			});
			//]]>'."\n";
		if ($use_tooltips) {
			echo '	jQuery(document).ready(
					function() {
						jQuery(document).tooltip({
								show: {
									effect: \'slideDown\'
									},
								track: true,
								open: function (event, ui) {
									setTimeout(function () {
										jQuery(ui.tooltip).hide(\'slideUp\');
										}, 5000);
									}
								});
					}
				);'."\n";
		}
		echo html_ac(html_ap() -1);
	}

	function hamburgerButton() {
		$hamburgerIcon = '<svg viewBox="0 0 50 40" width="20" height="20" fill="#eee">
				<rect width="50" height="7"></rect>
				<rect y="15" width="50" height="7"></rect>
				<rect y="30" width="50" height="7"></rect>
			      </svg>';
		echo html_ao('label', array('for' => 'hamburgerButton'));
		echo $hamburgerIcon;
		echo html_ac(html_ap() -1); // </label>
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

		echo html_ao('header', array('role' => 'banner'));
		echo html_ao('nav', array('role' => 'navigation'));
		echo $this->hamburgerButton();
		echo html_ao('logo');
		echo util_make_link('/', html_image('header/header-logo.png', null, null, array('alt'=>'FusionForge Home', 'height'=>'40')));
		echo html_ac(html_ap() -1); // </logo>
		echo $this->searchBox();
		echo html_ao('div', array('id' => 'userlinksdiv'));
		echo html_ao('ul');
		$items = $this->navigation->getUserLinks();
		for ($j = 0; $j < count($items['titles']); $j++) {
			$links[] = html_ao('li')
				.util_make_link($items['urls'][$j], $items['titles'][$j], array('class' => 'userlink'), true)
				.html_ac(html_ap() -1);
		}
		$params['links'] = &$links;
		plugin_hook('headermenu', $params);
		$template = isset($params['template']) ? $params['template'] : null;
		echo implode($template, $links);
		echo html_ac(html_ap() -1); // </ul>
		echo html_ac(html_ap() -1); // </div> #userlinkdiv

		echo html_ao('div', array('id' => 'menudiv'));
		echo html_ao('input', array('id' => 'hamburgerButton', 'type' => 'checkbox'));
		echo html_ac(html_ap() -1); // </input>
		echo html_ao('div', array('id' => 'hamburgermenudiv'));
		$this->outerTabs($params);
		echo html_ac(html_ap() -1); // </div> #hamburgermenudiv
		echo html_ao('div', array('id' => 'userlinkshamburgerdiv'));
		echo html_ao('ul');
		echo implode($template, $links);
		echo html_ac(html_ap() -1); // </ul>
		echo html_ac(html_ap() -1); // </div> #userlinkhamburgerdiv
		echo html_ac(html_ap() -1); // </div> #menudiv
		echo $this->quickNav();
		echo '<!-- inner tabs -->' . "\n";
		echo html_ao('div', array('class' => 'innertabs'));
		if (isset($params['group']) && $params['group']) {
			$this->projectTabs($params['toptab'], $params['group']);
		}
		echo html_ac(html_ap() -1); // </div> #tabGenerator
		echo html_ac(html_ap() -1); // </div> #innertabs
		echo html_ac(html_ap() -1); // </nav>
		echo html_ac(html_ap() -2); // </header>

		echo html_ao('main', array('id' => 'maindiv', 'role' => 'main'));
		plugin_hook('message');

		if (isset($GLOBALS['error_msg']) && $GLOBALS['error_msg']) {
			echo $this->error_msg($GLOBALS['error_msg']);
		}
		if (isset($GLOBALS['warning_msg']) && $GLOBALS['warning_msg']) {
			echo $this->warning_msg($GLOBALS['warning_msg']);
		}
		if (isset($GLOBALS['feedback']) && $GLOBALS['feedback']) {
			echo $this->feedback($GLOBALS['feedback']);
		}

		if (!empty($params['h1'])) {
			echo html_e('h1', array(), $params['h1'], false);
		} elseif (!empty($params['title'])) {
			echo html_e('h1', array('class' => 'hide'), $params['title'], false);
		}
		if (isset($params['submenu'])) {
			echo $params['submenu'];
		}
	}

	function bodyFooter() {
		echo html_ac(html_ap() -1).'<!-- id="maindiv" -->' . "\n";
	}

	function footer() {
		$this->bodyFooter();
		echo html_ao('footer', array('role' => 'contentinfo'));
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
			$idtitle = $id.'-title';
			$idtcont = $id.'-title-content';
		} else {
			$idid = rand();
			$idtitle = rand();
			$idtcont = rand();
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
			$idtitle = rand();
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
			$idcont = rand();
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

	function tabGenerator($tabs_dirs, $tabs_titles, $tabs_tooltips, $nested=false, $selected=false, $sel_tab_bgcolor='white', $total_width='100%') {
		$count = count($tabs_dirs);

		if ($count < 1) {
			return '';
		}

		$return = '<!-- start tabs -->'."\n";
		$attrs = array('class' => 'tabGenerator fullwidth');

		if ($total_width != '100%')
			$attrs['style'] = 'width:' . $total_width;

		$return .= html_ao('div', $attrs);
		$return .= html_ao('ul');

		$accumulated_width = 0;

		for ($i = 0; $i < $count; $i++) {
			$tabwidth = intval(ceil(($i+1)*100/$count)) - $accumulated_width;
			$accumulated_width += $tabwidth;

			// middle part
			$attrs = array();
			$attrs['class'] = 'tg-middle';
			$return .= html_ao('li', $attrs);
			$attrs = array();
			$attrs['id'] = md5($tabs_dirs[$i]).rand();
			$attrs['href'] = $tabs_dirs[$i];
			if (preg_match('/^https?:\/\//', $tabs_dirs[$i])) {
				$attrs['target'] = '_blank';
			}
			$return .= html_ao('a', $attrs);

			$attrs = array('title' => $tabs_tooltips[$i]);
			if ($selected == $i)
				$attrs['class'] = 'selected';
			elseif ($nested)
				$attrs['class'] = 'nested';

			$return .= html_e('span', $attrs, $tabs_titles[$i], false);
			$return .= html_ac(html_ap() - 2);
		}

		$return .= html_ac(html_ap() -1).'<!-- end tabs -->'."\n";
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
		return html_ac(html_ap() -1);
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
		$count = count($title_arr) - 1;
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
		$return = $this->beginSubMenu();
		$return .= $this->printSubMenu($title_arr, $links_arr, $attr_arr);
		$return .= $this->endSubMenu();
		return $return;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
