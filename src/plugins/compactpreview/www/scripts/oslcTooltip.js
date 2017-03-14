/**
 * Copyright 2011, Sabri LABBENE, Institut TELECOM
 * Copyright 2014, Franck Villaume - TrivialDev
 *
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

// This is heavily inspired by code presented in http://rndnext.blogspot.com/2009/02/jquery-ajax-tooltip.html from Caleb Tucker
// TODO : verify license
// TODO : use the improved version with hoverIntent plugin (http://cherne.net/brian/resources/jquery.hoverIntent.html) as documented in : http://rndnext.blogspot.com/2009/02/jquery-live-and-plugins.html

jQuery(function(){
	var hideDelay = 2000;
	var hideTimer = null;

	// One instance that's reused to show info for the current resource
	var container = jQuery('<div id="resourcePopupContainer">'
			+ '<table class="resourcePopupPopup">'
			+ '<tr>'
			+ '   <td class="corner topLeft"></td>'
			+ '   <td class="top"></td>'
			+ '   <td class="corner topRight"></td>'
			+ '</tr>'
			+ '<tr>'
			+ '   <td class="left">&nbsp;</td>'
			+ '   <td class="resourcePopupResult"><div id="resourcePopupContent"></div></td>'
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

	jQuery('.resourceLocalPopupTrigger').on('mouseover', function() {
		var url = jQuery(this).attr('href');

		if (hideTimer) {
			clearTimeout(hideTimer);
		}
		var pos = jQuery(this).offset();
		container.css({
			left: (pos.left) + 'px',
			top: pos.top + 10 + 'px'
		});

		jQuery.ajax({
			type: 'GET',
			url: url,
			dataType: 'html',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("Accept","application/x-fusionforge-compact+html");
			},

			success: function(data) {
				jQuery('#resourcePopupContent').html(data);
			}
		}
		);

		container.css('display', 'block');
	});

	jQuery('.resourceOslcPopupTrigger').on('mouseover', function() {
		var url = jQuery(this).attr('href');

		if (hideTimer) {
			clearTimeout(hideTimer);
		}
		var pos = jQuery(this).offset();
		var width = jQuery(this).width();
		container.css({
			left: (pos.left + width) + 'px',
			top: pos.top - 5 + 'px'
		});

		// if remote URL, do some OSLC compact-preview fetching
		jQuery('#resourcePopupContent').html('<i>...loading compact preview...</i>');

		// Fetch the OSLC compact preview representation of the resource
		//url: '/'+ resource +'/' + resourceId + '/',
		jQuery.ajax({
			type: 'GET',
			url: url,
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
								jQuery('#resourcePopupContent').load(prevDocUrl);
							}
						}
					}
				}
			}
		});

		container.css('display', 'block');
	});

	jQuery('.resourceLocalPopupTrigger').on('mouseout', function() {
		if (hideTimer) {
			clearTimeout(hideTimer);
		}
		hideTimer = setTimeout(function() {
			container.css('display', 'none');
			},
			hideDelay
		);
	});

	jQuery(document).on('mouseup', function(e) {
		if (!container.is(e.target) && container.has(e.target).length === 0) {
			container.hide();
		}
	});

	jQuery('.resourceOslcPopupTrigger').on('mouseout', function() {
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
	jQuery('#resourceLocalPopupContainer').mouseover(function() {
		if (hideTimer) {
			clearTimeout(hideTimer);
		}
	});

	// Allow mouse over of details without hiding details
	jQuery('#resourceOslcPopupContainer').mouseover(function() {
		if (hideTimer) {
			clearTimeout(hideTimer);
		}
	});

	// Hide after mouseout
	jQuery('#resourceLocalPopupContainer').mouseout(function() {
		if (hideTimer){
			clearTimeout(hideTimer);
		}
		hideTimer = setTimeout(function() {
				container.css('display', 'none');
			},
			hideDelay
		);
	});
	// Hide after mouseout
	jQuery('#resourceOslcPopupContainer').mouseout(function() {
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
