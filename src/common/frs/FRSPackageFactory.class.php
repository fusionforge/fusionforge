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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'frs/FRSPackage.class.php';

class FRSPackageFactory extends Error {

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
		$this->Error();

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
	 * @return	array	The array of FRS objects.
	 */
	function getFRSs() {
		if (isset($this->FRSs) && is_array($this->FRSs)) {
			return $this->FRSs;
		}

		if (session_loggedin()) {
			if (user_ismember($this->Group->getID()) || forge_check_global_perm('forge_admin')) {
				$pub_sql='';
			} else {
				$pub_sql=' AND is_public=1 ';
			}
		} else {
			$pub_sql=' AND is_public=1 ';
		}

		$sql = "SELECT * FROM frs_package WHERE group_id=$1 AND status_id='1' $pub_sql ORDER BY name";
		$result = db_query_params($sql, array($this->Group->getID()));

		if (!$result) {
			$this->setError(_('Error Getting FRS')._(': ').db_error());
			return false;
		} else {
			$this->FRSs = array();
			while ($arr = db_fetch_array($result)) {
				$this->FRSs[] = new FRSPackage($this->getGroup(), $arr['package_id'], $arr);
			}
		}
		return $this->FRSs;
	}

}
