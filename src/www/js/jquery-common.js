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

jQuery(function() {
	if ( typeof(jQuery(window).tipsy) == 'function') {
		jQuery('#tracker-monitor').tipsy({gravity: 'n', html: true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-summary').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-assigned_to').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-priority').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-status_id').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-description').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-canned_response').tipsy({gravity: 'w', html: true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-comment').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-new_artifact_type_id').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-manage-roles').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-vote').tipsy({gravity: 'w', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('#tracker-votes').tipsy({gravity: 'n', delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_monitor').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_save_place').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_start_thread').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});

		jQuery('.forum_reply').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_move').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_edit').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_delete').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_attach').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_attach_add').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_attach_edit').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('.forum_attach_delete').tipsy({gravity: 'ne', html:true, delayIn: 1000, delayOut: 500, fade: true});

		jQuery('select.trove-nodes').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('span.trove-nodes').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
	}
});

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

