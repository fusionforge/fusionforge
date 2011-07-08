<?php
/**
 * scmhook Class
 * Copyright 2011, Franck Villaume - Capgemini
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

class scmhook {
	var $name;
	var $description;
	var $classname;
	var $command;
	var $hooktype;
	var $label;
	var $unixname;
	var $needcopy;
	var $files = array();

	function getHookCmd() {
		return $this->command;
	}

	function getClassname() {
		return $this->classname;
	}

	function getName() {
		return $this->name;
	}

	function getDescription() {
		return $this->description;
	}

	function getHookType() {
		return $this->hooktype;
	}

	function getLabel() {
		return $this->label;
	}

	function getUnixname() {
		return $this->unixname;
	}

	function needCopy() {
		return $this->needcopy;
	}

	function getFiles() {
		return $this->files;
	}
}
?>
