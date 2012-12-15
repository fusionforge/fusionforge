<?php
/**
 * FusionForge Generic Storage Class
 *
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Franck Villaume - TrivialDev
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
 * "The Artifact ("Contribution") has not been tested and/or
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

class Storage extends Error {
    protected static $_instance;
	var $pending_store = array();
	var $pending_delete = array();

	function store($key, $file) {
		$storage = $this->get_storage($key);
		$dir     = dirname($storage);
		if (!is_dir($dir)) {
			if (!mkdir( $dir, 0755, true)) {
				$this->setError(_('Cannot create directory:').' '.$dir);
				return false;
			}
		}

		$this->pending_store[] = $storage;

		if (is_file($file) && is_dir($dir)) {
			$ret = rename($file, $storage);
			if (!$ret) {
				$this->setError(sprintf(_('File %1$s cannot be moved to the permanent location: %2$s.'), $file, $storage));
				return false;
			}
		} else {
			$this->setError(sprintf(_('Not a File %1$s or not a directory %2$s.'), $file, $dir));
			return false;
		}
		return $this;
	}

	function get($key) {
		return $this->get_storage($key);
	}

	function delete($key) {
		$this->pending_delete[] = $this->get_storage($key);
		return self::$_instance;
	}

	function deleteFromQuery($query, $params) {
		$res = db_query_params($query, $params);
		while($row = db_fetch_array($res)) {
			$this->delete($row['id']);
		}
	}

	function commit() {
		foreach ($this->pending_delete as $f) {
			rename($f, "$f.removed");
			touch("$f.removed");
		}
		$this->pending_store = array();
		$this->pending_delete = array();
	}

	function rollback() {
		foreach ($this->pending_store as $f) {
			unlink($f);
		}
		$this->pending_store = array();
		$this->pending_delete = array();
	}

	function get_storage($key) {
		$key = dechex($key);
		$pre = substr($key, strlen($key)-2);
		$last = substr($key, 0, strlen($key)-2);
		if (!$last) $last = '0';
		return $this->get_storage_path().'/'.$pre.'/'.$last;
	}
}
