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

	function tabGenerator($TABS_DIRS, $TABS_TITLES, $TABS_TOOLTIPS, $nested=false,  $selected=false, $sel_tab_bgcolor='WHITE',  $total_width='100%') {
		global $use_tooltips;

		$template = $this->twig->loadTemplate('Tabs.html');

		$vars = array('use_tooltips' => $use_tooltips,
			      'nested' => $nested,
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

		return $template->render($vars);
	}

	function makeLink($path, $text, $extra_params = false, $absolute = false) {
		global $use_tooltips;

		$template = $this->twig->loadTemplate('Link.html');

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
			      'extra_params' => $extra_params,
			      'use_tooltips' => $use_tooltips);

		return $template->render($vars);
	}

	function html_list($elements, $attrs = array(), $type = 'ul') {
		if ($type == 'ol') {
			$template = $this->twig->loadTemplate('OrderedList.html');
		} else {
			$template = $this->twig->loadTemplate('UnorderedList.html');
		}

		$items = array();
		for ($i = 0; $i < count($elements); $i++) {
			$items[$i]['element'] = $elements[$i];
			if ($i <= count($attrs)) {
				$items[$i]['attr'] = $attrs[$i];
			} else {
				$items[$i]['attr'] = '';
			}				
		}

		$vars = array('items' => $items);

		return $template->render($vars);
	}

	function html_chartid($id = 0, $title = '') {
		$template = $this->twig->loadTemplate('ChartId.html');

		$vars = array('chart_id' => $id,
					  'title' => $title);
		
		return $template->render($vars);
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
		// TODO
		return parent::getJavascripts();
	}
	function getStylesheets() {
		// TODO
		return parent::getStylesheets();
	}
	function header($params) {
		// TODO
		return parent::header($params);
	}
	function headerStart($params) {
		// TODO
		return parent::headerStart($params);
	}
	function headerHTMLDeclaration() {
		// TODO
		return parent::headerHTMLDeclaration();
	}
	function headerTitle($params) {
		// TODO
		return parent::headerTitle($params);
	}
	function headerFavIcon() {
		// TODO
		return parent::headerFavIcon();
	}
	function headerRSS() {
		// TODO
		return parent::headerRSS();
	}
	function headerSearch() {
		// TODO
		return parent::headerSearch();
	}
	function headerCSS() {
		// TODO
		return parent::headerCSS();
	}
	function headerJS() {
		// TODO
		return parent::headerJS();
	}
	function headerLinkedDataAutodiscovery() {
		// TODO
		return parent::headerLinkedDataAutodiscovery();
	}
	function headerForgepluckerMeta() {
		// TODO
		return parent::headerForgepluckerMeta();
	}
	function bodyHeader($params){
		// TODO
		return parent::bodyHeader($params);
		}
	function footer($params = array()) {
			// TODO
			return parent::footer($params);
		}
	function footerEnd() {
		// TODO
		return parent::footerEnd() { ;
		}
	function getRootIndex() {
		// TODO
		return parent::getRootIndex();
	}
	function boxTop($title) {
		// TODO
		return parent::boxTop($title);
	}
	function boxMiddle($title) {
		// TODO
		return parent::boxMiddle($title);
	}
	function boxBottom() {
		// TODO
		return parent::boxBottom();
	}
	function boxGetAltRowStyle($i, $classonly = false) {
		// TODO
		return parent::boxGetAltRowStyle($i, $classonly);
	}
	function listTableTop($titleArray = array(), $linksArray = array(), $class = '', $id = '', $thClassArray = array(), $thTitleArray = array(), $thOtherAttrsArray = array()) {
		// TODO
		return parent::listTableTop($titleArray, $linksArray, $class, $id, $thClassArray, $thTitleArray, $thOtherAttrsArray);
	}
	function listTableBottom() {
		// TODO
		return parent::listTableBottom();
	}
	function outerTabs($params) {
		// TODO
		return parent::outerTabs($params);
	}
	function quickNav() {
		// TODO
		return parent::quickNav();
	}
	function projectTabs($toptab, $group_id) {
		// TODO
		return parent::projectTabs($toptab, $group_id);
	}
	function tabGenerator($TABS_DIRS, $TABS_TITLES, $TABS_TOOLTIPS, $nested=false, $selected=false, $sel_tab_bgcolor='white', $total_width='100%') {
		// TODO
		return parent::tabGenerator($TABS_DIRS, $TABS_TITLES, $TABS_TOOLTIPS, $nested, $selected, $sel_tab_bgcolor, $total_width);
	}
	function searchBox() {
		// TODO
		return parent::searchBox();
	}
	function beginSubMenu() {
		// TODO
		return parent::beginSubMenu();
	}
	function endSubMenu() {
		// TODO
		return parent::endSubMenu();
	}
	function printSubMenu($title_arr, $links_arr, $attr_arr) {
		// TODO
		return parent::printSubMenu($title_arr, $links_arr, $attr_arr);
	}
	function subMenuSeparator() {
		// TODO
		return parent::subMenuSeparator();
	}
	function subMenu($title_arr, $links_arr, $attr_arr = array()) {
		// TODO
		return parent::subMenu($title_arr, $links_arr, $attr_arr);
	}
	function multiTableRow($row_attrs, $cell_data, $istitle = false) {
		// TODO
		return parent::multiTableRow($row_attrs, $cell_data, $istitle);
	}
	function feedback($feedback) {
		// TODO
		return parent::feedback($feedback);
	}
	function warning_msg($msg) {
		// TODO
		return parent::warning_msg($msg);
	}
	function error_msg($msg) {
		// TODO
		return parent::error_msg($msg);
	}
	function information($msg) {
		// TODO
		return parent::information($msg);
	}
	function confirmBox($msg, $params, $buttons, $image='*none*') {
		// TODO
		return parent::confirmBox($msg, $params, $buttons, $image);
	}
	function jQueryUIconfirmBox($id = 'dialog-confirm', $title = 'Confirm your action', $message = 'Do you confirm your action?') {
		// TODO
		return parent::jQueryUIconfirmBox($id, $title, $message);
	}
	function html_input($name, $id = '', $label = '', $type = 'text', $value = '', $extra_params = '') {
		// TODO
		return parent::html_input($name, $id, $label, $type, $value, $extra_params);
	}
	function html_checkbox($name, $value, $id = '', $label = '', $checked = '', $extra_params = array()) {
		// TODO
		return parent::html_checkbox($name, $value, $id, $label, $checked, $extra_params) {
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
		// TODO
		return parent::html_table_top($cols, $summary, $class, $extra_params);
	}
	function getMonitorPic($title = '', $alt = '') {
		// TODO
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
		// TODO
		return parent::getFolderPic($title, $alt, $otherAttr);
	}
	function getPicto($url, $title, $alt, $width = '20', $height = '20', $otherAttr = array()) {
		// TODO
		return parent::getPicto($url, $title, $alt, $width, $height, $otherAttr);
	}
	function toSlug($string, $space = "-") {
		// TODO
		return parent::toSlug($string, $space);
	}
	function widget(&$widget, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type) {
		// TODO
		return parent::widget(&$widget, $layout_id, $readonly, $column_id, $is_minimized, $display_preferences, $owner_id, $owner_type);
	}
	function _getTogglePlusForWidgets() {
		// TODO
		return parent::_getTogglePlusForWidgets();
	}
	function _getToggleMinusForWidgets() {
		// TODO
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
