<?php

/**
 * phpcaptchaPlugin Class
 *
 * Copyright 2010, Luis Daniel Ibáñez
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

class phpcaptchaPlugin extends Plugin {

	var $phpcaptcha_path;

	function __construct() {
		$this->Plugin();
		$this->name = 'phpcaptcha';
		$this->text = _('Enable use of phpcaptcha (more information www.phpcaptcha.org)');
		$this->_addHook('captcha_check');
		$this->_addHook('captcha_form');
		$this->phpcaptcha_path = forge_get_config('phpcaptcha_path', 'phpcaptcha');
	}

	function captcha_check($params) {
		$captcha_code = getStringFromRequest('captcha_code');
		require_once $this->phpcaptcha_path.'/securimage.php';
		$securimage = new Securimage();
		if (!$securimage->check($captcha_code)) {
			$params['valide'] = 0;
			$params['warning_msg'] = _('Wrong captcha code');
		}
	}

	function captcha_form() {
		global $HTML;
		if ($this->checkConfig()) {
			echo '<p>
				<img id="captcha" src="/plugins/'.$this->name.'/securimage_show.php" alt="CAPTCHA Image" />
				<a href="#" onclick="document.getElementById(\'captcha\').src = \'/plugins/'.$this->name.'/securimage_show.php?\' + Math.random(); return false">';
			echo _('Reload image.').'</a>';
			echo '</p><p>';
			echo _('Write captcha here:').'<br />';
			echo '<input type="text" name="captcha_code" size="10" maxlength="6" />';
			echo '</p>';
		} else {
			echo $HTML->information(_('phpcaptcha seems not installed. Contact your administrator for more informations.'));
		}
	}

	function checkConfig() {
		if(!is_file($this->phpcaptcha_path.'/securimage.php') || !extension_loaded('gd'))
			return false;

		return true;
	}
}
