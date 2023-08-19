<?php
/**
 * FusionForge Diary aka blog
 *
 * Copyright 2019,2023, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/diary/index.php to add require */

$view = getStringFromRequest('view', 'main');
$views_whitelist_array = array('detail',
				'archivelist',
				'main');
if (in_array($view, $views_whitelist_array)) {
	include ($gfcommon.'diary/views/'.$view.'.php');
}
