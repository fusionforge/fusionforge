<?php
/**
 * FusionForge FRS
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfcommon.'frs/FRSPackageFactory.class.php';

class FRSReleaseFactory extends FFError {

	/**
	 * The Group object.
	 *
	 * @var	 object	$Group.
	 */
	var $Group;

	/**
	 * The FRSRs array.
	 *
	 * @var	 array	$FRSRs.
	 */
	var $FRSRs;

	/**
	 * The FRSNRs array.
	 *
	 * @var	 array	$FRSNRs.
	 */
	var $FRSNRs;

	/**
	 * Constructor.
	 *
	 * @param	Group	$Group The Group object to which these FRSRs are associated.
	 */
	function __construct(& $Group) {
		parent::__construct();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('FRSReleaseFactory'._(': ').$Group->getErrorMessage());
			return;
		}
		if (!$Group->usesFRS()) {
			$this->setError(sprintf(_('%s does not use the FRS tool'), $Group->getPublicName()));
			return;
		}
		$this->Group =& $Group;
	}

	/**
	 * getGroup - get the Group object this FRSReleaseFactory is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getFRSRs - get an array of FRSR objects for this Group.
	 *
	 * @param	bool	$status	limite the search to active packages. Default is false.
	 * @return	array	The array of FRS objects.
	 */
	function getFRSRs($status = false) {
		if (isset($this->FRSRs) && is_array($this->FRSRs)) {
			return $this->FRSRs;
		}

		$this->FRSRs = array();
		$frspf = new FRSPackageFactory($this->Group);
		$ids = $frspf->getAllPackagesIds($status);

		foreach ($ids as $id) {
			if (forge_check_perm('frs', $id, 'read')) {
				$frsp = frspackage_get_object($id);
				$frspr = $frsp->getReleases();
				$this->FRSRs = array_merge($frspr, $this->FRSRs);
			}
		}
		return $this->FRSRs;
	}

	/**
	 * getFRSRNewReleases - get an array of FRS Newest Release objects for this Group.
	 *
	 * @param	bool	$status	limite the search to active packages. Default is false.
	 * @return	array	The array of FRS objects.
	 */
	function getFRSRNewReleases($status = false) {
		if (isset($this->FRSNRs) && is_array($this->FRSNRs)) {
			return $this->FRSNRs;
		}

		$this->FRSNRs = array();
		$frspf = new FRSPackageFactory($this->Group);
		$ids = $frspf->getAllPackagesIds($status);

		foreach ($ids as $id) {
			if (forge_check_perm('frs', $id, 'read')) {
				$frspnr = false;
				$frsp = frspackage_get_object($id);
				$frspnr_id = $frsp->getNewestReleaseID();
				if ($frspnr_id) {
					$frspnr = frsrelease_get_object($frspnr_id);
				}
			}
			if (isset($frspnr) && $frspnr) {
				$this->FRSNRs[] = $frspnr;
			}
		}
		return $this->FRSNRs;
	}
}
