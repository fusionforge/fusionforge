<?php
/**
 * phptextcaptchaPlugin Class
 *
 * Copyright 2017, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

class phptextcaptchaPlugin extends Plugin {

	var $pngdir = '';

	function __construct() {
		parent::__construct();
		$this->name = 'phptextcaptcha';
		$this->text = _('Enable use of php-text-captcha (more information http://pear.php.net/manual/en/package.text.text-captcha.php)');
		$this->_addHook('captcha_check');
		$this->_addHook('captcha_form');
		$this->pngdir = forge_get_config('data_path').'/plugins/'.$this->name;
	}

	function captcha_check($params) {
		session_start();
		if (isset($_POST['captcha_code']) && is_string($_POST['captcha_code'])
			&& isset($_SESSION['captcha_code'])
			&& strlen($_POST['captcha_code']) > 0 && strlen($_SESSION['captcha_code']) > 0
			&& $_POST['captcha_code'] == $_SESSION['captcha_code']
			) {
			unset($_SESSION['captcha_code']);
		} else {
			$params['valide'] = 0;
			$params['warning_msg'] = _('Wrong captcha code');
		}
		@unlink($this->pngdir.'/'.sha1(session_id()) . '.png');
	}

	function captcha_form(&$html) {
		require_once 'Text/CAPTCHA.php';

		// Set CAPTCHA image options (font must exist!)
		if (is_file('/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf')) {
			//Debian-like
			$font_path = '/usr/share/fonts/truetype/dejavu/';
		} else {
			//CentOS
			$font_path = '/usr/share/fonts/dejavu/';
		}
		$imageOptions = array(
			'font_size' => 24,
			'font_path' => $font_path,
			'font_file' => 'DejaVuSansMono.ttf',
			'text_color' => '#DDFF99',
			'lines_color' => '#CCEEDD',
			'background_color' => '#555555'
		);

		// Set CAPTCHA options
		$options = array(
			'width' => 200,
			'height' => 80,
			'output' => 'png',
			'imageOptions' => $imageOptions
		);

		// Generate a new Text_CAPTCHA object, Image driver
		$c = Text_CAPTCHA::factory('Image');
		$c->init($options);

		// Get CAPTCHA secret passphrase
		$_SESSION['captcha_code'] = $c->getPhrase();

		// Get CAPTCHA image (as PNG)
		$png = $c->getCAPTCHA();

		$pngfile = sha1(session_id()).'.png';
		if (is_file($this->pngdir.'/'.$pngfile)) {
			unlink($this->pngdir.'/'.$pngfile);
		}	
		file_put_contents($this->pngdir.'/'.$pngfile, $png);
		$html = '<p>';
		$html .= '<img src="/plugins/'.$this->name.'/'.$pngfile.'?'.time().'" />';
		$html .= '</p><p>';
		$html .= _('Write captcha here')._(': ').'<br />';
		$html .= '<input type="text" name="captcha_code" size="10" maxlength="8" required="required" />';
		$html .= '</p>';
		return $html;
	}
}
