/**
 * FusionForge FRS
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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

FRSController = function() {};

FRSController.prototype =
{
	toggleConfirmBox: function(params) {
		this.params = params;
		jQuery('#'+this.params.idconfirmbox).dialog({
			resizable: false,
			height: this.params.height,
			width: this.params.width,
			modal: true,
			buttons: [{
					text: this.params.do,
					click: jQuery.proxy(function() {
						jQuery('#'+this.params.idconfirmbox).dialog('close');
						jQuery.getJSON(this.params.action+'&sure=1&really_sure=1', jQuery.proxy(function(data) {
							jQuery('#maindiv > .feedback').remove();
							jQuery('#maindiv > .error').remove();
							jQuery('#maindiv > .warning_msg').remove();
							if (data.format == 'multi') {
								var arrayLength = Object.keys(data).length;
								console.log(arrayLength);
								for (var i = 0; i < arrayLength; i++) {
									console.log(data[i]);
									if (typeof data[i].html != 'undefined') {
										jQuery('#maindiv').prepend(data[i].html);
									}
									if (typeof data[i].deletedom != 'undefined') {
										jQuery('#'+data[i].deletedom).remove();
									}
								}
							} else {
								if (typeof data.html != 'undefined') {
									jQuery('#maindiv').prepend(data.html);
								}
								if (typeof data.deletedom != 'undefined') {
									jQuery('#'+data.deletedom).remove();
								}
							}
						}, this));
					}, this)
				},
				{
					text: this.params.cancel,
					click: function() { jQuery(this).dialog('close'); }
				}]
		});
	},

	doAction: function(params) {
		this.params = params;
		jQuery.getJSON(this.params.action, jQuery.proxy(function(data){
			jQuery('#maindiv > .feedback').remove();
			jQuery('#maindiv > .error').remove();
			jQuery('#maindiv > .warning_msg').remove();
			if (data.format == 'multi') {
				var arrayLength = Object.keys(data).length;
				for (var i = 0; i < arrayLength; i++) {
					if (typeof data[i].html != 'undefined') {
						jQuery('#maindiv').prepend(data[i].html);
					}
					if (typeof data[i].action != 'undefined') {
						jQuery('#'+this.params.id).attr(data[i].property, data[i].action);
					}
					if (typeof data[i].img != 'undefined') {
						jQuery('#'+this.params.id+' img').remove();
						jQuery('#'+this.params.id).append(data[i].img);
					}
				}
			} else {
				if (typeof data.html != 'undefined') {
					jQuery('#maindiv').prepend(data.html);
				}
				if (typeof data.action != 'undefined') {
					jQuery('#'+this.params.id).attr(data.property, data.action);
				}
				if (typeof data.img != 'undefined') {
					jQuery('#'+this.params.id+' img').remove();
					jQuery('#'+this.params.id).append(data.img);
				}
			}
		}, this));
	},

	updatePackage: function(params) {
		this.params = params;
		var td = jQuery(this.params.rowid).children();
		jQuery.getJSON(this.params.action, {package_name: td[2].children.package_name.value, status_id: td[3].children.status_id.value }, function(data){
			jQuery('#maindiv > .feedback').remove();
			jQuery('#maindiv > .error').remove();
			jQuery('#maindiv > .warning_msg').remove();
			if (typeof data.html != 'undefined') {
					jQuery('#maindiv').prepend(data.html);
			}
		});
	},

	/*! build list of id, comma separated
	 */
	buildUrlByCheckbox: function(id) {
		var CheckedBoxes = new Array();
		for (var h = 0; h < jQuery('input:checked').length; h++) {
			if (typeof(jQuery('input:checked')[h].className) != 'undefined' && jQuery('input:checked')[h].className.match('checkedrelid'+id)) {
				CheckedBoxes.push(jQuery('input:checked')[h].value);
			}
		}
		return CheckedBoxes;
	},

	checkAll: function(id, type) {
		if (jQuery('#checkall'+type).is(':checked')) {
			jQuery('.'+id).each(function() {
				jQuery(this).prop('checked', true);
				});
			jQuery('#massaction'+type).show();
		} else {
			jQuery('.'+id).each(function() {
				jQuery(this).prop('checked', false);
			});
			jQuery('#massaction'+type).hide();
		}
	},

	checkgeneral: function(id) {
		if (jQuery(this).attr('checked', false)) {
			jQuery('#checkall'+id).prop('checked', false);
			jQuery('#massaction'+id).hide();
		}
		for (var h = 0; h < jQuery('input:checked').length; h++) {
			if (typeof(jQuery('input:checked')[h].className) != 'undefined' && jQuery('input:checked')[h].className.match('checkedrelid'+id)) {
				jQuery('#massaction'+id).show();
			}
		}
	}
};
