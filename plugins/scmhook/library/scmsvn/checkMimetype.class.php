<?php
/**
 * scmhook checkMimetype Plugin Class
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

global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class checkMimetype extends scmhook {
	function checkMimetype() {
		$this->name = "Check Mimetype";
		$this->description = _('Verify if commited files have svn:mimetype set up correctly.');
		$this->classname = "checkMimetype";
		$this->command = 'perl $SCRIPTPATH/check-mime-type.pl "$1" "$2"';
		$this->hooktype = "pre-commit";
		$this->label = "scmsvn";
		$this->unixname = "checkmimetype";
		$this->needcopy = 1;
		$this->files = array(dirname(__FILE__).'/hooks/'.$this->unixname.'/check-mime-type.pl');
	}
}
?>
