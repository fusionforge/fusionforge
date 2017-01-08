/**
 * FusionForge Tracker
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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

/* based on http://jsfiddle.net/hbxk4e61/ */

function iefixform() {
	var isIE11 = !(window.ActiveXObject) && "ActiveXObject" in window;
	var sampleElement = $('[form]').get(0);
	if (sampleElement && window.HTMLFormElement && sampleElement.form instanceof HTMLFormElement && !isIE11) {
		// browser supports it, no need to fix
		return;
	}
	jQuery('[form*="trackerform"]').each(function(index){
		if (this.name != 'submit' && !jQuery(this).parent().is('form')) {
			jQuery(this).clone(true).css('display', 'none').appendTo(jQuery('#trackerform'));
			jQuery('#trackerform').submit();
		}
	});
}
