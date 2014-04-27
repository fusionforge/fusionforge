/**
 * FusionForge Tooltip
 *
 * Copyright 2010, Alain Peyrat
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

jQuery(document).ready(function(){

	// Show button and hide help text on load
	jQuery('#slide_button').show();
	jQuery('#slide_text').hide();

	// slideToggle help text when help button is pressed.
	jQuery('#slide_button').click(function() {
		jQuery('#slide_text').slideToggle("slow");
		return false;
	});

	jQuery('#fieldset1').coolfieldset();
	jQuery('#fieldset1_closed').coolfieldset({collapsed:true});
});

