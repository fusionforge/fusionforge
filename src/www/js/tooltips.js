/**
* FusionForge Tooltip
*
* Copyright 2010, Alain Peyrat
* Copyright 2011, Franck Villaume - Capgemini
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
* You should have received a copy of the GNU General Public License
* along with FusionForge; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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

		jQuery('select.trove-nodes').tipsy({gravity: 'w', html:true, delayIn: 1000, delayOut: 500, fade: true});
		jQuery('span.trove-nodes').tipsy({gravity: 'n', html:true, delayIn: 1000, delayOut: 500, fade: true});
	}
});
