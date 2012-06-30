<?php
/**
 * scmhook allowRevPropChange Plugin Class
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class allowRevPropChange extends scmhook {
	function __construct() {
		$this->name        = "Allow RevProp Changes";
		$this->description = _('Allow SCM committers to change revision properties.');
		$this->classname   = "allowRevPropChange";
		$this->command     = 'exit 0';
		$this->hooktype    = "pre-revprop-change";
		$this->label       = "scmsvn";
		$this->unixname    = "allowrevpropchange";
		$this->needcopy    = 0;
	}
}
?>
