<?php
/**
 * FusionForge trackers
 *
 * Copyright 2011, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Roadmap ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'tracker/Roadmap.class.php';

class RoadmapFactory extends Error {

	var $group;
	var $group_id;

	var $is_admin;

	var $roadmaps;

	function __construct($group) {
		$this->Error();

		if (is_object($group)) {
			if ($group->isError()) {
				$this->setError('in RoadmapFactory, '.$group->getErrorMessage());
				return;
			}
			$this->group = $group;
			$this->group_id = $group->getID();
		} else {
			$this->setError(_('Invalid Group'));
		}
	}

	public function getRoadmaps($enable_only=false) {
		if ($this->roadmaps) {
			return $this->roadmaps;
		}

		$enable_filter = '';
		if ($enable_only) {
			$enable_filter = 'AND enable=1';
		}

		$result = db_query_params ('SELECT * FROM roadmap WHERE group_id=$1 '.$enable_filter.' ORDER BY name',
					array ($this->group_id));
		if (! $result /* || ! db_numrows($result) */) {
			$this->setError('in getRoadmaps, '.db_error());
			return false;
		}
		$this->roadmaps = array();
		while($entry = db_fetch_array($result)) {
			$roadmap = new Roadmap($this->group, $entry['roadmap_id'], $entry);
			if($roadmap->isError()) {
				$this->setError($roadmap->getErrorMessage());
			} else {
				$this->roadmaps[] = $roadmap;
			}
		}

		return $this->roadmaps;
	}

	public function getRoadmapByID($roadmap_id, $enable_only=false) {
		$roadmaps = $this->getRoadmaps($enable_only);
		foreach ($roadmaps as $roadmap) {
			if($roadmap->isError()) {
				$this->setError($roadmap->getErrorMessage());
			} else {
				if ($roadmap->getID() == $roadmap_id) {
					return $roadmap;
				}
			}
		}

		return false;
	}

	public function getRoadmapByName($name, $enable_only=false) {
		$roadmaps = $this->getRoadmaps($enable_only);
		foreach ($roadmaps as $roadmap) {
			if($roadmap->isError()) {
				$this->setError($roadmap->getErrorMessage());
			} else {
				if ($roadmap->getName() == $name) {
					return $roadmap;
				}
			}
		}

		return false;
	}

	public function getDefault() {
		$roadmaps = $this->getRoadmaps();
		foreach ($roadmaps as $roadmap) {
			if($roadmap->isError()) {
				$this->setError($roadmap->getErrorMessage());
			} else {
				if ($roadmap->isDefault()) {
					return $roadmap->getID();
				}
			}
		}

		return false;
	}

}
