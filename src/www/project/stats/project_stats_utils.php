<?php
/**
 * Project Statistics Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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

function period2seconds($period_name,$span) {
	if (!$period_name || $period_name=="lifespan") {
		return "";
	}

	if (!is_int ($span) || !$span) $span=1;

	if ($period_name=="day") {
		return 60*60*24*$span;
	} elseif ($period_name=="week") {
		return 60*60*24*7*$span;
	} elseif ($period_name=="month") {
		return 60*60*24*30*$span;
	} elseif ($period_name=="year") {
		return 60*60*24*365*$span;
	} else {
		return $span;
	}
}

function period2timestamp($period_name,$span) {
	$seconds=period2seconds($period_name,$span);
	if (!$seconds) return '';
	return (string)(time()-$seconds);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
