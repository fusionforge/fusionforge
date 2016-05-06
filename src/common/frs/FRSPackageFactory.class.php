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
require_once $gfcommon.'frs/FRSPackage.class.php';

class FRSPackageFactory extends FFError {

	/**
	 * The Group object.
	 *
	 * @var	 object	$Group.
	 */
	var $Group;

	/**
	 * The FRSs array.
	 *
	 * @var	 array	$FRSs.
	 */
	var $FRSs;

	/**
	 * Constructor.
	 *
	 * @param	Group	$Group The Group object to which these FRSs are associated.
	 */
	function __construct(& $Group) {
		parent::__construct();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('FRSPackageFactory'._(': ').$Group->getErrorMessage());
			return;
		}
		if (!$Group->usesFRS()) {
			$this->setError(sprintf(_('%s does not use the FRS tool'), $Group->getPublicName()));
			return;
		}
		$this->Group =& $Group;
	}

	/**
	 * getGroup - get the Group object this FRSPackageFactory is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getFRSs - get an array of FRS objects for this Group.
	 *
	 * @param	bool	$status	limite the search to active packages. Default is false.
	 * @return	array	The array of FRS objects.
	 */
	function &getFRSs($status = false) {
		if (isset($this->FRSs) && is_array($this->FRSs)) {
			return $this->FRSs;
		}

		$this->FRSs = array();
		$ids = $this->getAllPackagesIds($status);

		foreach ($ids as $id) {
			if (forge_check_perm('frs', $id, 'read')) {
				$this->FRSs[] =& frspackage_get_object($id);
			}
		}
		return $this->FRSs;
	}

	/**
	 * getAllPackagesIds - return a list of package ids.
	 *
	 * @param	bool	$status	limite the search to active packages. Default is false.
	 * @return	array	The array of package object ids.
	 */
	function &getAllPackagesIds($status = false) {
		$result = array();
		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'SELECT package_id FROM frs_package WHERE group_id=$1 ',
					array($this->Group->getID()));
		if ($status)
			$qpa = db_construct_qpa($qpa, 'AND status_id=$1', array(1));

		$qpa = db_construct_qpa($qpa, 'ORDER BY package_id DESC');
		$res = db_query_qpa($qpa);
		if ($res) {
			while ($arr = db_fetch_array($res)) {
				$result[] = $arr['package_id'];
			}
		}
		return $result;
	}

	/**
	 * getPermissionOfASpecificUser - get the max level of permission of the current user
	 *
	 * @return	integer	the value of permission
	 *			0 = none
	 *			1 = read
	 *			2 = file
	 *			3 = release
	 *			4 = admin
	 */
	function getPermissionOfASpecificUser() {
		$admin = false;
		$release = false;
		$file = false;
		$read = false;
		$pkgids = $this->getAllPackagesIds();
		foreach ($pkgids as $pkgid) {
			if (forge_check_perm('frs', $pkgid, 'read')) {
				$read = true;
			}
			if (forge_check_perm('frs', $pkgid, 'file')) {
				$file = true;
			}
			if (forge_check_perm('frs', $pkgid, 'release')) {
				$release = true;
			}
			if (forge_check_perm('frs', $pkgid, 'admin')) {
				$admin = true;
			}
		}
		if (forge_check_perm('frs_admin', $this->Group->getID(), 'admin')) {
			$admin = true;
		}
		if ($admin) {
			return 4; // admin
		} elseif ($release) {
			return 3; // release
		} elseif ($file) {
			return 2; // file
		} elseif ($read) {
			return 1; // read
		}
		return 0;
	}

}
