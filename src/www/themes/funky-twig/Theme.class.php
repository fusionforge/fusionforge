<?php
/**
 * FusionForge Funky-Twig Theme
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
require_once 'Twig/Autoloader.php';
Twig_Autoloader::register();

class Theme extends Layout {

	function Theme() {
		// Parent constructor
		$this->Layout();
		$this->themeurl = util_make_uri('themes/funky/');
		$this->imgbaseurl = $this->themeurl . 'images/';
		$this->imgroot = $this->imgbaseurl;
		$this->addStylesheet('/themes/funky/css/theme.css');
		$this->addStylesheet('/themes/funky/css/theme-pages.css');
		$this->addStylesheet('/scripts/jquery-ui/css/overcast/jquery-ui-1.10.4.custom.css');

		$this->twig_loader = new Twig_Loader_Filesystem(forge_get_config('themes_root').'/funky-twig/templates');
		$this->twig = new Twig_Environment($this->twig_loader);
	}

	function renderTemplate($file, $vars = array()) {
		global $use_tooltips;

		$vars['use_tooltips'] = $use_tooltips;
		$vars['session_loggedin'] = session_loggedin();
		
		$t = $this->twig->loadTemplate($file);

		return $t->render($vars);
	}

	function tabGenerator($TABS_DIRS, $TABS_TITLES, $TABS_TOOLTIPS, $nested=false,  $selected=false, $sel_tab_bgcolor='WHITE',  $total_width='100%') {
		$vars = array('nested' => $nested,
					  'total_width' => $total_width);
		
		$tabs = array();
		for ($i = 0; $i < count($TABS_DIRS); $i++) {
			$tab = array('href' => $TABS_DIRS[$i],
						 'id' => md5($TABS_DIRS[$i]),
						 'title' => $TABS_TITLES[$i],
						 'tooltip' => $TABS_TOOLTIPS[$i]
				);
			if ($i == $selected) {
				$tab['selected'] = true;
			} else {
				$tab['selected'] = false;
			}
			$tabs[] = $tab;
		}
		$vars['tabs'] = $tabs;

		return $this->renderTemplate('Tabs.html', $vars);
	}

	function makeLink($path, $text, $extra_params = false, $absolute = false) {
		if (!is_array($extra_params)) {
			$extra_params = array();
		}

		if ($absolute) {
			$href = $path;
		} else {
			$href = util_make_uri($path);
		}

		$vars = array('href' => $href,
					  'text' => $text,
					  'extra_params' => $extra_params);

		return $this->renderTemplate('Link.html', $vars);
	}

	function html_list($elements, $attrs = array(), $type = 'ul') {
		$items = array();
		for ($i = 0; $i < count($elements); $i++) {
			$items[$i]['element'] = $elements[$i]['content'];
			$items[$i]['attrs'] = $elements[$i]['attrs'];
		}

		$vars = array('items' => $items,
					  'attrs' => $attrs);

		if ($type == 'ol') {
			return $this->renderTemplate('OrderedList.html', $vars);
		} else {
			return $this->renderTemplate('UnorderedList.html', $vars);
		}
	}

	function html_chartid($id = 0, $title = '') {
		$vars = array('chart_id' => $id,
					  'title' => $title);
		
		return $this->renderTemplate('ChartId.html', $vars);
	}

	// Methods to reimplement (if relevant)
	function addJavascript($js) {
		// TODO
		return parent::addJavascript($js);
	}
	function addStylesheet($css, $media='') {
		// TODO
		return parent::addStylesheet($css, $media);
	}
	function getJavascripts() {
		$scripts = array();
		foreach ($this->javascripts as $js) {
			$scripts[] = util_make_uri($js);
		}
		$this->javascripts = array();

		$vars = array('js' => $scripts);
		
		return $this->renderTemplate('JavaScripts.html', $vars);
	}
	function getStylesheets() {
		$sheets = array();
		foreach ($this->stylesheets as $c) {
			$sheet = array('css' => util_make_uri($c['css']));
			if ($c['media']) {
				$sheet['media'] = $c['media'];
			} else {
				$sheet['media'] = '';
			}
			$sheets[] = $sheet;
		}
		$this->stylesheets = array();

		$vars = array('sheets' => $sheets);
		
		return $this->renderTemplate('StyleSheets.html', $vars);
	}
	function header($params) {
		$this->headerHTMLDeclaration();

		$vars = array();

		$vars['forge_name'] = forge_get_config('forge_name');
		if (isset($params['title'])) {
			$vars['title'] = $params['title'];
		}
		if (isset($params['meta-description'])) {
			$vars['meta_description'] = $params['meta-description'];
		}
		if (isset($params['meta-keywords'])) {
			$vars['meta keywords'] = $params['meta-keywords'];
		}
		$vars['favicon_url'] = util_make_uri('/images/icon.png');
		$vars['search_url'] = util_make_uri('/export/search_plugin.php');

		$vars['rssfeeds'] = array(
			array('title' => forge_get_config ('forge_name').' - Project News Highlights RSS',
				  'url' => util_make_uri('/export/rss_sfnews.php')),
			array('title' => forge_get_config ('forge_name').' - Project News Highlights RSS 2.0',
				  'url' => util_make_uri('/export/rss20_news.php')),
			array('title' => forge_get_config ('forge_name').' - New Projects RSS',
				  'url' => util_make_uri('/export/rss_sfprojects.php')),
			);
		if (isset($GLOBALS['group_id'])) {
			$vars['rssfeeds'][] = array('title' => forge_get_config ('forge_name') . ' - New Activity RSS',
										'url' => util_make_uri('/export/rss20_activity.php?group_id='.$GLOBALS['group_id']));
		}

		plugin_hook("javascript_file");
		plugin_hook("css_file");
		$vars['javascripts'] = array();
		foreach ($this->javascripts as $js) {
			$vars['javascripts'][] = util_make_uri($js);
		}

		$sheets = array();
		foreach ($this->stylesheets as $c) {
			$sheet = array('url' => util_make_uri($c['css']));
			if ($c['media']) {
				$sheet['media'] = $c['media'];
			} else {
				$sheet['media'] = '';
			}
			$sheets[] = $sheet;
		}
		$vars['stylesheets'] = $sheets;

		$ff = new FusionForge();
		$vars['software_name'] = $ff->software_name;
		$vars['software_version'] = $ff->software_version;



		$script_name = getStringFromServer('SCRIPT_NAME');
		$end = strpos($script_name,'/',1);
		if($end) {
			$script_name = substr($script_name,0,$end);
		}

		// Only activated for /projects, /users or /softwaremap for the moment
		$vars['alt_representations'] = array();
		if ($script_name == '/projects' || $script_name == '/users' || $script_name == '/softwaremap') {

			$php_self = getStringFromServer('PHP_SELF');

			// invoke the 'alt_representations' hook to add potential 'alternate' links (useful for Linked Data)
			// cf. http://www.w3.org/TR/cooluris/#linking
			$p2 = array('script_name' => $script_name,
						'php_self' => $php_self,
						'return' => array());
			
			plugin_hook_by_reference('alt_representations', $p2);

			$vars['alt_representations'] = $p2['return'];
		}
		
		print $this->renderTemplate('headerStart.html', $vars);

		$this->bodyHeader($params);
	}
	function headerHTMLDeclaration() {
		global $sysDTDs, $sysXMLNSs;

		$vars = array();
		
		if (!util_ifsetor($this->doctype) || !util_ifsetor($sysDTDs[$this->doctype])) {
			$this->doctype = 'transitional';
		}
		$vars['dtd'] = $sysDTDs[$this->doctype]['doctype'];
		$vars['lang'] = _('en');
		$vars['ns'] = $sysXMLNSs;

		print $this->renderTemplate('HTMLDeclaration.html', $vars);
	}
	function bodyHeader($params){
		$vars = array();

		if (isset($params['h1'])) {
			$vars['h1'] = $params['h1'];
		} elseif (isset($params['title'])) {
			$vars['h1'] = $params['title'];
		} else {
			$vars['h1'] = '';
		}

		if (isset($params['title'])) {
			$vars['title'] = $params['title'] . " - ".forge_get_config('forge_name');
		} else {
			$vars['title'] = forge_get_config('forge_name');
		}

		$cells = array();
		$cells[] = array('text' => util_make_link('/', html_image('/header/top-logo.png', null, null, array('alt'=>'FusionForge Home'))),
						 'attrs' => array('id' => 'header-col1'));
		$items = $this->navigation->getUserLinks();
		for ($j = 0; $j < count($items['titles']); $j++) {
			$links[] = util_make_link($items['urls'][$j], $items['titles'][$j], array('class' => 'userlink'), true);
		}
		
		$params['links'] = &$links;
		plugin_hook('headermenu', $params);

		$sep = isset($params['template']) ?  $params['template'] : ' | ';
		$cells[] = array('text' => implode($sep, $links),
						 'attrs' => array('id' => 'header-col2'));

		$vars['rowattrs'] = array();
		$vars['siteheader_celldata'] = $cells;

		if (session_loggedin()) {
			$groups = session_get_user()->getGroups();
		} else {
			$groups = array();
		}
		sortProjectList($groups);
		
		$naventries = array();

		foreach ($groups as $g) {
			$menu = $this->navigation->getProjectMenu($g->getID());
			$naventries[] = array('url' => $menu['starturl'],
								'title' => $menu['name'],
								'indent' => 0);
			
			for ($j = 0; $j < count($menu['urls']); $j++) {
				$naventries[] = array('url' => $menu['urls'][$j],
									'title' => $menu['titles'][$j],
									'indent' => 1);
				if (@$menu['adminurls'][$j]) {
					$naventries[] = array('url' => $menu['adminurls'][$j],
										'title' => _('Admin'),
										'indent' => 2);
				}
			}
		}

		$vars['naventries'] = $naventries;


		$outertabsdata = array();
		$menu = $this->navigation->getSiteMenu();
		for ($i = 0; $i < count($menu['urls']); $i++) {
			$d = array('href' => $menu['urls'][$i],
					   'id' => md5($menu['urls'][$i]),
					   'title' => $menu['titles'][$i],
					   'tooltip' => $menu['tooltips'][$i]);
			if ($i == $menu['selected']) {
				$d['selected'] = true;
			} else {
				$d['selected'] = false;
			}
			$outertabsdata[] = $d;
		}
		$vars['outertabsdata'] = $outertabsdata;



		$projecttabsdata = array();
		if (isset($params['group']) && $params['group']) {
			$menu = $this->navigation->getProjectMenu($params['group'], $params['toptab']);
			for ($i = 0; $i < count($menu['urls']); $i++) {
				$d = array('href' => $menu['urls'][$i],
						   'id' => md5($menu['urls'][$i]),
						   'title' => $menu['titles'][$i],
						   'tooltip' => $menu['tooltips'][$i]);
				if ($i == $menu['selected']) {
					$d['selected'] = true;
				} else {
					$d['selected'] = false;
				}
				$projecttabsdata[] = $d;
			}
		}
		$vars['projecttabsdata'] = $projecttabsdata;

		$vars['error_msg'] = $GLOBALS['error_msg'];
		$vars['warning_msg'] = $GLOBALS['warning_msg'];
		$vars['feedback'] = $GLOBALS['feedback'];

		$vars['submenu'] = $params['submenu'];

		print $this->renderTemplate('bodyHeader.html', $vars);
	}
	function footer($params = array()) {
		$vars = array('imgurl' => util_make_uri('/images/pow-fusionforge.png'));

		print $this->renderTemplate('footer.html', $vars);
	}
	function footerEnd() {
		// TODO
		return parent::footerEnd();
	}
	function getRootIndex() {
		// TODO
		return parent::getRootIndex();
	}
	function boxTop($title, $id='') {
		$vars = array('id' => $id,
					  'title' => $title);

		return $this->renderTemplate('BoxTop.html', $vars);
	}
	function boxMiddle($title, $id='') {
		$vars = array('id' => $id,
					  'title' => $title);

		return $this->renderTemplate('BoxMiddle.html', $vars);
	}
	function boxBottom() {
		return $this->renderTemplate('BoxBottom.html');
	}
	function boxGetAltRowStyle($i, $classonly = false) {
		// TODO
		return parent::boxGetAltRowStyle($i, $classonly);
	}
	function listTableTop($titleArray = array(), $linksArray = array(), $class = '', $id = '', $thClassArray = array(), $thTitleArray = array(), $thOtherAttrsArray = array()) {
		$vars = array('id' => $id,
					  'class' => $class);

		$data = array();

		if (count($titleArray)) {
			$count = count($titleArray);
			for ($i = 0; $i < $count; $i++) {
				$item = array('text' => $titleArray[$i]);
				if ($thOtherAttrsArray && isset($thOtherAttrsArray[$i])) {
					$item['attrs'] = $thOtherAttrsArray[$i];
				}
				if ($thClassArray && isset($thClassArray[$i])) {
					$item['attrs']['class'] = $thClassArray[$i];
				}
				if ($thTitleArray && isset($thTitleArray[$i])) {
					$item['attrs']['title'] = $thTitleArray[$i];
				}
				if ($linksArray && isset($linksArray[$i])) {
					$item['attrs']['url'] = util_make_uri($linksArray[$i]);
				}
				$data[] = $item;
			}
		}

		$vars['data'] = $data;

		return $this->renderTemplate('ListTableTop.html', $vars);
	}
	function listTableBottom() {
		return $this->renderTemplate('ListTableBottom.html');
	}
	function outerTabs($params) {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::outerTabs($params);
	}
	function projectTabs($toptab, $group_id) {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::projectTabs($toptab, $group_id);
	}
	function searchBox() {
		// TODO
		return parent::searchBox();
	}
	function beginSubMenu() {
		return $this->renderTemplate('beginSubMenu.html');
	}
	function endSubMenu() {
		return $this->renderTemplate('endSubMenu.html');
	}
	function printSubMenu($title_arr, $links_arr, $attr_arr) {
		$submenuentries = array();

		for ($i=0; $i < count($title_arr); $i++) {
			$submenuentries[] = array('title' => $title_arr[$i],
									  'url' => util_make_uri($links_arr[$i]),
									  'attrs' => $attr_arr[$i]);
		}

		$vars = array('submenuentries' => $submenuentries);

		return $this->renderTemplate('printSubMenu.html', $vars);
	}
	function subMenuSeparator() {
		return $this->renderTemplate('subMenuSeparator.html');
	}
	function subMenu($title_arr, $links_arr, $attr_arr = array()) {
		$return  = $this->beginSubMenu();
		$return .= $this->printSubMenu($title_arr, $links_arr, $attr_arr);
		$return .= $this->endSubMenu();
		return $return;
	}
	function multiTableRow($row_attrs, $cell_data, $istitle = false) {
		if (!isset($row_attrs['class'])) {
			$row_attrs['class'] = '';
		}
		
		if ($istitle) {
			$row_attrs['class'] .= ' align-center';
		}
		
		$cells = array();
		for ( $c = 0; $c < count($cell_data); $c++ ) {
			$cell = array('text' => $cell_data[$c][0]);
			$cell['attrs'] = array('class' => '');
			foreach (array_slice($cell_data[$c],1) as $k => $v) {
				$cell['attrs'][$k] = $v;
			}
			$cells[] = $cell;
		}
		
		$vars = array('rowattrs' => $row_attrs,
					  'cells' => $cells,
					  'istitle' => $istitle);
		
		return $this->renderTemplate('multiTableRow.html', $vars);
	}
	function feedback($msg) {
		$vars = array('message' => strip_tags($msg, '<br>'));

		return $this->renderTemplate('feedback.html', $vars);
	}
	function warning_msg($msg) {
		$vars = array('message' => strip_tags($msg, '<br>'));

		return $this->renderTemplate('warningMessage.html', $vars);
	}
	function error_msg($msg) {
		$vars = array('message' => strip_tags($msg, '<br>'));

		return $this->renderTemplate('errorMessage.html', $vars);
	}
	function information($msg) {
		$vars = array('message' => strip_tags($msg, '<br>'));

		return $this->renderTemplate('information.html', $vars);
	}
	function confirmBox($msg, $params, $buttons, $image='*none*') {
		if ($image == '*none*') {
			$image = html_image('stop.png','48','48',array());
		}

		$vars = array('params' => $params,
					  'buttons' => $buttons,
					  'image' => $image,
					  'msg' => $msg,
					  'action' => getStringFromServer('PHP_SELF'));
		
		return $this->renderTemplate('ConfirmBox.html', $vars);
	}
	function jQueryUIconfirmBox($id = 'dialog-confirm', $title = 'Confirm your action', $message = 'Do you confirm your action?') {
		$vars = array('id' => $id,
					  'title' => $title,
					  'message' => $message);

		return $this->renderTemplate('jQueryUIConfirmBox.html', $vars);
	}
	function html_input($name, $id = '', $label = '', $type = 'text', $value = '', $extra_params = '') {
		// TODO
		return parent::html_input($name, $id, $label, $type, $value, $extra_params);
	}
	function html_checkbox($name, $value, $id = '', $label = '', $checked = '', $extra_params = array()) {
		// TODO
		return parent::html_checkbox($name, $value, $id, $label, $checked, $extra_params);
	}
	function html_text_input_img_submit($name, $img_src, $id = '', $label = '', $value = '', $img_title = '', $img_alt = '', $extra_params = array(), $img_extra_params = '') {
		// TODO
		return parent::html_text_input_img_submit($name, $img_src, $id, $label, $value, $img_title, $img_alt, $extra_params, $img_extra_params);
	}
	function html_select($vals, $name, $label = '', $id = '', $checked_val = '', $text_is_value = false, $extra_params = '') {
		// TODO
		return parent::html_select($vals, $name, $label, $id, $checked_val, $text_is_value, $extra_params);
	}
	function html_textarea($name, $id = '', $label = '', $value = '',  $extra_params = '') {
		// TODO
		return parent::html_textarea($name, $id, $label, $value, $extra_params);
	}
	function html_table_top($cols, $summary = '', $class = '', $extra_params = '') {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::html_table_top($cols, $summary, $class, $extra_params);
	}
	function getMonitorPic($title = '', $alt = '') {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::getMonitorPic($title, $alt);
	}
	function getStartMonitoringPic($title = '', $alt = '') {
		// TODO
		return parent::getStartMonitoringPic($title, $alt);
	}
	function getStopMonitoringPic($title = '', $alt = '') {
		// TODO
		return parent::getStopMonitoringPic($title, $alt);
	}
	function getReleaseNotesPic($title = '', $alt = '') {
		// TODO
		return parent::getReleaseNotesPic($title, $alt);
	}
	function getDownloadPic($title = '', $alt = '') {
		// TODO
		return parent::getDownloadPic($title, $alt);
	}
	function getHomePic($title = '', $alt = '') {
		// TODO
		return parent::getHomePic($title, $alt);
	}
	function getFollowPic($title = '', $alt = '') {
		// TODO
		return parent::getFollowPic($title, $alt);
	}
	function getForumPic($title = '', $alt = '') {
		// TODO
		return parent::getForumPic($title, $alt);
	}
	function getDocmanPic($title = '', $alt = '') {
		// TODO
		return parent::getDocmanPic($title, $alt);
	}
	function getMailPic($title = '', $alt = '') {
		// TODO
		return parent::getMailPic($title, $alt);
	}
	function getPmPic($title = '', $alt = '') {
		// TODO
		return parent::getPmPic($title, $alt);
	}
	function getSurveyPic($title = '', $alt = '') {
		// TODO
		return parent::getSurveyPic($title, $alt);
	}
	function getScmPic($title = '', $alt = '') {
		// TODO
		return parent::getScmPic($title, $alt);
	}
	function getFtpPic($title = '', $alt = '') {
		// TODO
		return parent::getFtpPic($title, $alt);
	}
	function getDeletePic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getDeletePic($title, $alt, $otherAttr);
	}
	function getRemovePic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getRemovePic($title, $alt, $otherAttr);
	}
	function getConfigurePic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getConfigurePic($title, $alt, $otherAttr);
	}
	function getZipPic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getZipPic($title, $alt, $otherAttr);
	}
	function getAddDirPic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getAddDirPic($title, $alt, $otherAttr);
	}
	function getNewPic($title = '', $alt = '', $otherAttr = array()) {
		// TODO
		return parent::getNewPic($title, $alt, $otherAttr);
	}
	function getFolderPic($title = '', $alt = '', $otherAttr = array()) {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::getFolderPic($title, $alt, $otherAttr);
	}
	function getPicto($url, $title, $alt, $width = '20', $height = '20', $otherAttr = array()) {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::getPicto($url, $title, $alt, $width, $height, $otherAttr);
	}
	function toSlug($string, $space = "-") {
		// TODO
		return parent::toSlug($string, $space);
	}
	function widget(&$widget, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type) {
		// TODO
		return parent::widget($widget, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type);
	}
	function _getTogglePlusForWidgets() {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::_getTogglePlusForWidgets();
	}
	function _getToggleMinusForWidgets() {
		// TODO -- not used directly by pages, can be inlined in this file (unless used in several places)
		return parent::_getToggleMinusForWidgets();
	}
	function printSoftwareMapLinks() {
		// TODO
		return parent::printSoftwareMapLinks();
	}
	function displayStylesheetElements() {
		// TODO
		return parent::displayStylesheetElements();
	}
	function openForm($args) {
		// TODO
		return parent::openForm($args);
	}
	function closeForm() {
		// TODO
		return parent::closeForm();
	}
	function addRequiredFieldsInfoBox() {
		// TODO
		return parent::addRequiredFieldsInfoBox();
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
