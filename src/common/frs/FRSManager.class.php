<?php
/**
 * FusionForge file release system
 *
 * Copyright 2017, Franck Villaume - TrivialDev
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

class FRSManager extends FFError {
	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * @param	$Group
	 */
	function __construct(&$Group) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('FRSManager: '.$Group->getErrorMessage());
			return;
		}
		if (!$Group->usesFRS()) {
			$this->setError(_('This Group does not use FRS'));
			return;
		}
		$this->Group =& $Group;
	}

	function getSettings() {
		$settings = array();
		$settings['send_all_frs'] = $this->Group->frsEmailAll();
		$settings['new_frs_address'] = $this->Group->getFRSEmailAddress();
		return $settings;
	}
}
