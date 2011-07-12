<?php
/**
 * Gravatar Plugin
 *
 * Copyright 2010 (c) Alain Peyrat <aljeux@free.fr>
 *
 * This file is part of FusionForge
 *
 * FusionForge is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class gravatarPlugin extends Plugin {

	function gravatarPlugin () {
		$this->Plugin() ;
		$this->name = "gravatar" ;
		$this->text = _('Gravatar Plugin');
		$this->hooks[] = 'user_logo';
	}

	function user_logo (&$params) {
		$user_id = $params['user_id'];
		$size = isset($params['size'])? $params['size']: 'm';
		$code = $this->getHtml($user_id, $size);
		if (isset($params['content'])) {
			$params['content'] = $code;
		} else {
			echo $code;
		}
	}

	private function getEmail($user_id) {
		$res = db_query_params('SELECT email FROM users WHERE user_id=$1', array($user_id));
		if ($res) {
			return db_result($res, 0, 'email');
		}
		return false;
	}

	private function getHtml($user_id, $size) {
		if ($email = $this->getEmail($user_id)) {
			$hash = md5( strtolower( trim( $email ) ) );

			$url = 'http://www.gravatar.com/avatar/';
			if (isset($_SERVER['HTTPS']))
				$url = 'https://secure.gravatar.com/avatar/';

			$usize = 28;

			if ($size == 'l')
				$usize = '130';
			if ($size == 'm')
				$usize = 48;
			if ($size == 's')
				$usize = 28;
			if ($size == 'xs')
				$usize = 16;

			$url .= $hash.'?s='. $usize;
			$class = 'img-shadow-'.$size;
			return '<div class="'.$class.'"><img src="'.$url.'" class="gravatar" alt="" /></div>';
		}
		return '';
	}
}
