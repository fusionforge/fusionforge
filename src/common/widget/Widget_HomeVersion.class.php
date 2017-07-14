<?php
/**
 * Copyright 2016, Franck Villaume - TrivialDev
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

class Widget_HomeVersion extends Widget {
	function __construct() {
		parent::__construct('homeversion');
	}

	function getTitle() {
		return _('System Information');
	}

	function getContent() {
		$ff = FusionForge::getInstance();
		return sprintf(_('%s is running %s version %s'), forge_get_config('forge_name'), $ff->software_name, $ff->software_version);
	}

	function getDescription() {
		return _('Display version of Fusionforge used.');
	}
}
