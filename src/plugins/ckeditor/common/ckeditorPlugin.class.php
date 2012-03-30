<?php
/**
 * FusionForge Plugin CKeditor Plugin Class
 *
 * Copyright 2011 (c) Alcatel-Lucent
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge-plugin-ckeditor
 *
 * FusionForge-plugin-ckeditor is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge-plugin-ckeditor is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge-plugin-ckeditor; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * The ckeditorPlugin class. It implements the Hooks for the presentation
 *  of the text editor whenever needed
 *
 */

class ckeditorPlugin extends Plugin {

	var $toolBar = array();

	function __construct() {
		$this->Plugin() ;
		$this->name = "ckeditor" ;
		$this->text = _("HTML editor (ckeditor)");
		$this->hooks[] = "user_create";
		$this->hooks[] = "userisactivecheckbox";
		$this->hooks[] = "userisactivecheckboxpost";
		$this->hooks[] = "text_editor";

		// Toolbar
		$this->toolBar['complete'] = array(
			array('Source','-','Cut','Copy','Paste','-','SpellChecker','Scayt'),
			array('Undo','Redo','-','Find','Replace'),
			array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
			array('Outdent','Indent'),
			array('Maximize','-','About'),
			'/',
			array('Bold','Italic','Underline','Strike','-','Subscript','Superscript'),
			array('Format','FontSize'),
			array('TextColor','BGColor'),
			array('NumberedList','BulletedList'),
			array('Link','Unlink','Anchor'),
			array('Table','HorizontalRule','Smiley','SpecialChar','PageBreak')
		);
		$this->toolBar['fusionforge-basic'] = array(
			array('Source'),
			array('JustifyLeft','JustifyCenter'),
			array('Bold','Italic','Underline','Strike'),
			array('Format'),
			array('TextColor','BGColor'),
			array('NumberedList','BulletedList'),
			array('Link','Unlink'),
			array('Table','HorizontalRule')
		);
	}

	/**
	 * The function to be called for a Hook
	 *
	 * @param    String  $hookname  The name of the hookname that has been happened
	 * @param    String  $params    The params of the Hook
	 *
	 */
	function CallHook ($hookname, &$params) {

		if ($hookname == "user_create") {
			// Activate the plugin by default for new user.
			$params['user']->setPluginUse ( $this->name );
		} elseif ($hookname == "text_editor") {
			// Check if activated as user side.
			if (session_loggedin()) {
				$user = session_get_user();
				if ($user->usesPlugin ( $this->name )) {
					return $this->displayCKeditorArea($params);
				}
			}
		}
	}

	private function displayCKeditorArea(&$params) {
		$name = isset($params['name'])? $params['name'] : 'body';
		if (file_exists ("/usr/share/ckeditor/ckeditor.php")) {
			require_once("/usr/share/ckeditor/ckeditor.php");
			$editor = new CKeditor($name) ;
			$editor->basePath = util_make_uri('/ckeditor/');
		} else {
			include_once $GLOBALS['gfplugins'].'ckeditor/www/ckeditor.php';
			if (class_exists('CKeditor')) {
				$editor = new CKeditor($name) ;
				$editor->basePath = util_make_uri('/plugins/' . $this->name . '/');
			} else {
				$this->setError("Unable to activate ckeditor plugin, package ckeditor not found.");
				return false;
			}
		}
		if (isset($params['width'])) $editor->config['width'] = $params['width'];
		if (isset($params['height'])) $editor->config['height'] = $params['height'];
		if (isset($params['toolbar']) && array_key_exists(strtolower($params['toolbar']), $this->toolBar)) {
			$editor->config['toolbar'] = $this->toolBar[strtolower($params['toolbar'])];
		} else {
			$editor->config['toolbar'] = $this->toolBar['complete'];
		}
		$editor->returnOutput = true;
		$content = '<input type="hidden" name="_'.$name.'_content_type" value="html" />'."\n";
		$content .= $editor->editor($name, $params['body']) ;

		// If content is present, return the html code in content.
		if (isset($params['content'])) {
			$params['content'] = $content;
		} else {
			$GLOBALS['editor_was_set_up'] = true;
			echo $content ;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
