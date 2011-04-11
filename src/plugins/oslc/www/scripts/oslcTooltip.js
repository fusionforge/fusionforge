/**
 * This file is (c) Copyright 2011 by Sabri LABBENE, Institut TELECOM
 *
 * This file is part of FusionForge.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

jQuery(function(){  
	var hideDelay = 500;    
	var hideTimer = null;  

	// One instance that's reused to show info for the current person  
	var container = $('<div id="personPopupContainer">'  
			+ '<table width="" border="0" cellspacing="0" cellpadding="0" class="personPopupPopup">'  
			+ '<tr>'  
			+ '   <td class="corner topLeft"></td>'  
			+ '   <td class="top"></td>'  
			+ '   <td class="corner topRight"></td>'  
			+ '</tr>'  
			+ '<tr>'  
			+ '   <td class="left">&nbsp;</td>'
			+ '   <td class="personPopupResult"><div id="personPopupContent"></div></td>'  
			+ '   <td class="right">&nbsp;</td>'  
			+ '</tr>'  
			+ '<tr>'  
			+ '   <td class="corner bottomLeft">&nbsp;</td>'  
			+ '   <td class="bottom">&nbsp;</td>'  
			+ '   <td class="corner bottomRight"></td>'  
			+ '</tr>'  
			+ '</table>'  
			+ '</div>'
	);  

	jQuery('body').append(container);
	
	jQuery('.personPopupTrigger').live('mouseover', function() {
		var params = jQuery(this).attr('rel').split(',');
		var resourceType = params[0];
		var resourceId = params[1];

		if(resourceType == 'user') {
			resource = 'users';
		}
		if(resourceType == 'project'){
			resource = 'projects';
		}

		if (hideTimer) {
			clearTimeout(hideTimer);  
		}
		var pos = jQuery(this).offset();
		var width = jQuery(this).width();
		container.css({  
			left: (pos.left + width) + 'px',  
			top: pos.top - 5 + 'px'  
		});

		jQuery('#personPopupContent').html('&nbsp;');

		jQuery.ajax({  
			type: 'GET',
			url: '/'+ resource +'/' + resourceId + '/',
			dataType: 'xml',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("Accept","application/x-oslc-compact+xml");
			},
			success: function(data) { 
				var smPreview = data.documentElement.getElementsByTagName('oslc:smallPreview')[0];
				if( smPreview ) {
					var Preview = smPreview.getElementsByTagName('oslc:Preview')[0];
					if(Preview){
						var oslcDoc = Preview.getElementsByTagName('oslc:document')[0];
						if( oslcDoc ) {
							var prevDocUrl = oslcDoc.getAttribute('rdf:ressource');
							if( prevDocUrl ) {
								jQuery('#personPopupContent').load(prevDocUrl);
							}
						}
					}
				}
			}  
		});  

		container.css('display', 'block');  
	});  

	jQuery('.personPopupTrigger').live('mouseout', function() { 
		if (hideTimer) { 
			clearTimeout(hideTimer);
		}
		hideTimer = setTimeout(function() {  
			container.css('display', 'none');  
			}, 
			hideDelay
		);  
	});

	// Allow mouse over of details without hiding details  
	jQuery('#personPopupContainer').mouseover(function() {  
		if (hideTimer) { 
			clearTimeout(hideTimer);
		}
	});  

	// Hide after mouseout  
	jQuery('#personPopupContainer').mouseout(function() {  
		if (hideTimer){  
			clearTimeout(hideTimer);
		}
		hideTimer = setTimeout(function() {  
				container.css('display', 'none');  
			},
			hideDelay
		);  
	});  
}); 