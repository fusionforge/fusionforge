/**
 * headerMenu Plugin Js Controller
 *
 * Copyright 2012-2014,2016,2021, Franck Villaume - TrivialDev
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

var EditHeaderMenuController = function(params) {
	this.params = params;
	this.bindControls();
	this.initializeView();
};

var HeaderMenuController = function(params) {
	this.params = params;
	this.bindControls();
	this.initializeView();
};

var GroupMenuController = function(params) {
	this.params = params;
	this.bindControls();
	this.initializeView();
};

HeaderMenuController.prototype =
{
	bindControls: function() {
		this.params.inputHtmlCode.click(jQuery.proxy(this, "htmlCodeView"));
		this.params.inputURL.click(jQuery.proxy(this, "htmlUrlView"));
		this.params.inputOuter.click(jQuery.proxy(this, "inputHtmlCodeView"));
		this.params.inputHeader.click(jQuery.proxy(this, "initializeView"));
		this.params.validOutButton.click(jQuery.proxy(this, "validateOutLinkOrder"));
		this.params.validHeaButton.click(jQuery.proxy(this, "validateHeaLinkOrder"));
	},

	initializeView: function() {
		this.params.inputHtmlCode.prop('disabled', true);
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
		this.params.inputURL.attr('checked', 'checked');
		if (typeof(this.params.tableOutTbLink) !== 'undefined') {
			this.params.tableOutTbLink.sortable({
				update: function(event, ui) {
					jQuery(".sortable_outermenu_listlinks tbody").children().each(function() {
						if (jQuery(this).index() % 2 === 0) {
							jQuery(this).attr("class", "even");
						} else {
							jQuery(this).attr("class", "odd");
						}
						jQuery(this).children(":first").text(jQuery(this).index());
					});
					jQuery("#linkorderoutervalidatebutton").show();
				}
			});
		}
		if (typeof(this.params.tableHeaTbLink) !== 'undefined') {
			this.params.tableHeaTbLink.sortable({
				update: function(event, ui) {
					jQuery(".sortable_headermenu_listlinks tbody").children().each(function() {
						if (jQuery(this).index() % 2 === 0) {
							jQuery(this).attr("class", "even");
						} else {
							jQuery(this).attr("class", "odd");
						}
						jQuery(this).children(":first").text(jQuery(this).index());
					});
					jQuery("#linkorderheadervalidatebutton").show();
				}
			});
		}
	},

	htmlCodeView: function() {
		this.params.trHtmlCode.show();
		this.params.trUrlCode.hide();
	},

	htmlUrlView: function() {
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
	},

	inputHtmlCodeView: function() {
		this.params.inputHtmlCode.prop('disabled', false);
	},

	validateOutLinkOrder: function() {
		var linkOrder = this.params.tableOutTbLink.sortable('toArray').toString();
		jQuery.getJSON(this.params.headerMenuUrl,
				{
					action:		'validateOrder',
					linkorder:	linkOrder,
					type:		'globaladmin'
				},
				jQuery.proxy(function(data) {
						jQuery('#maindiv > .feedback').remove();
						jQuery('#maindiv > .error').remove();
						jQuery('#maindiv > .warning_msg').remove();
						if (typeof data.html !== 'undefined') {
							jQuery('#maindiv').prepend(data.html);
						}
						this.params.validOutButton.hide();
					}, this)
			);
	},

	validateHeaLinkOrder: function() {
		var linkOrder = this.params.tableHeaTbLink.sortable('toArray').toString();
		jQuery.getJSON(this.params.headerMenuUrl,
				{
					action:		'validateOrder',
					linkorder:	linkOrder,
					type:		'globaladmin'
				},
				jQuery.proxy(function(data) {
						jQuery('#maindiv > .feedback').remove();
						jQuery('#maindiv > .error').remove();
						jQuery('#maindiv > .warning_msg').remove();
						if (typeof data.html !== 'undefined') {
							jQuery('#maindiv').prepend(data.html);
						}
						this.params.validHeaButton.hide();
					}, this)
			);
	}
};

GroupMenuController.prototype =
{
	bindControls: function() {
		this.params.inputHtmlCode.click(jQuery.proxy(this, "htmlCodeView"));
		this.params.inputURL.click(jQuery.proxy(this, "htmlUrlView"));
		this.params.validateButton.click(jQuery.proxy(this, "validateProjectLinkOrder"));
	},

	initializeView: function() {
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
		this.params.inputURL.attr('checked', 'checked');
		if (typeof(this.params.tableTbodyLink) !== 'undefined') {
			this.params.tableTbodyLink.sortable({
				update: function(event, ui) {
					jQuery("#sortable").find("tbody").children().each(function() {
						if (jQuery(this).index() % 2 === 0) {
							jQuery(this).attr("class", "even");
						} else {
							jQuery(this).attr("class", "odd");
						}
						jQuery(this).children(":first").text(jQuery(this).index());
					});
					jQuery("#linkordervalidatebutton").show();
				}
			});
		}
	},

	htmlCodeView: function() {
		this.params.trHtmlCode.show();
		this.params.trUrlCode.hide();
		this.params.trIframeView.hide();
	},

	htmlUrlView: function() {
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
		this.params.trIframeView.show();
	},

	inputHtmlCodeView: function() {
		this.params.inputHtmlCode.prop('disabled', false);
	},

	validateProjectLinkOrder: function() {
		var linkOrder = jQuery("#sortable").find("tbody").sortable('toArray').toString();
		jQuery.getJSON(this.params.headerMenuUrl,
				{
					group_id:	this.params.groupId,
					action:		'validateOrder',
					linkorder:	linkOrder,
					type:		'projectadmin'
				},
				jQuery.proxy(function(data) {
						jQuery('#maindiv > .feedback').remove();
						jQuery('#maindiv > .error').remove();
						jQuery('#maindiv > .warning_msg').remove();
						if (typeof data.html !== 'undefined') {
							jQuery('#maindiv').prepend(data.html);
						}
						this.params.validateButton.hide();
					}, this)
			);
	}
};

EditHeaderMenuController.prototype =
{
	bindControls: function() {
		this.params.inputHtmlCode.click(jQuery.proxy(this, "htmlCodeView"));
		this.params.inputURL.click(jQuery.proxy(this, "htmlUrlView"));
		if (typeof(this.params.inputOuter) !== 'undefined') {
			this.params.inputOuter.click(jQuery.proxy(this, "inputHtmlCodeView"));
		}
		if (typeof(this.params.inputHeader) !== 'undefined') {
			this.params.inputHeader.click(jQuery.proxy(this, "headerView"));
		}
		if (typeof(this.params.inputURLIframe) !== 'undefined') {
			this.params.inputURLIframe.click(jQuery.proxy(this, "htmlUrlView"));
		}
	},

	initializeView: function() {
		if (this.params.inputHeader.attr("checked")) {
			this.params.inputHtmlCode.prop('disabled', true);
		}
		if (this.params.inputHtmlCode.attr("checked")) {
			this.params.trHtmlCode.show();
			this.params.trUrlCode.hide();
		}
		if (this.params.inputURL.attr("checked")) {
			this.params.trHtmlCode.hide();
			this.params.trUrlCode.show();
		}
		if (typeof(this.params.inputURLIframe) !== 'undefined') {
			if (this.params.inputURLIframe.attr("checked")) {
				this.params.trHtmlCode.hide();
				this.params.trUrlCode.show();
			}
		}
	},

	htmlUrlView: function() {
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
	},

	htmlCodeView: function() {
		this.params.trHtmlCode.show();
		this.params.trUrlCode.hide();
	},

	headerView: function() {
		this.params.inputHtmlCode.prop('disabled', true);
		this.params.trHtmlCode.hide();
		this.params.trUrlCode.show();
		this.params.inputURL.attr('checked', 'checked');
	},

	inputHtmlCodeView: function() {
		this.params.inputHtmlCode.prop('disabled', false);
	}
};
