/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2008. All rights reserved
 * Copyright 2013, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

function toggle_iframe(joburl) {
	if (!jQuery('#hudson_iframe_div').visible()) {
		jQuery('#hudson_iframe_div').fadeToggle('slow', 'linear');
	}
	jQuery('#hudson_iframe').attr('src', joburl);
	if (jQuery('#link_show_only').length) {
		jQuery('#link_show_only').attr('href', joburl);
	}
}