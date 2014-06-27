<?php
/**
 * FusionForge FRS : view dispatcher
 *
 * Copyright 2014 Franck Villaume - TrivialDev
 * http://fusionforge.org
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $gfcommon;

$view = getStringFromRequest('view', 'listpackages');
switch ($view) {
	case 'admin':
	case 'deleterelease':
	case 'editrelease':
	case 'listpackages':
	case 'qrs':
	case 'reporting':
	case 'shownotes':
	case 'showreleases': {
		include ($gfcommon.'frs/views/'.$view.'.php');
		break;
	}
}
