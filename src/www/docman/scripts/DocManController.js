/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
 * Copyright 2011, Alain Peyrat
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

/*! ListFileController
 * @param groupId the current FusionForge groupID
 */
DocManListFileController = function(params)
{
	this.lockInterval	= [];
	this.params		= params;
	this.bindControls();
	this.resizableDiv();
	this.initSize();
};

DocManAddItemController = function(params)
{
	this.params	= params;
	this.bindControls();
};

DocManListFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		if (typeof(this.params.buttonEditDirectory) != 'undefined') {
			this.params.buttonEditDirectory.click(jQuery.proxy(this, "toggleEditDirectoryView"));
		}
		if (typeof(this.params.buttonAddItem) != 'undefined') {
			this.params.buttonAddItem.click(jQuery.proxy(this, "toggleAddItemView"));
		}
	},

	resizableDiv:function() {
		if (typeof(this.params.divHandle) != 'undefined') {
			this.params.divHandle.mousedown(jQuery.proxy(this, "dragging"));
			var params = this.params;
			var w = jQuery('#maindiv').width() - this.params.divHandle.width() - 70;
			jQuery(document).mouseup(function(){isDragging = false;}).mousemove(function(e){
				if (typeof(isDragging) != 'undefined') {
					if (isDragging) {
						params.divLeft.css('width', e.pageX);
						params.divRight.css('width', w - e.pageX);
						jQuery.Storage.set("treesize",""+params.divLeft.width());
					}
				}
			});
		}
	},

	initSize:function() {
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > this.params.divRight.height()) {
				this.params.divHandle.css('height', this.params.divLeft.height());
			} else {
				this.params.divHandle.css('height', this.params.divRight.height());
			}
			if (jQuery.Storage.get("treesize") != 0) {
				this.params.divLeft.css('width', parseInt(jQuery.Storage.get("treesize")));
				var w = jQuery('#maindiv').width() - this.params.divHandle.width() - 70;
				this.params.divRight.css('width', w - this.params.divLeft.width());
			}
		}
	},

	dragging: function() {
		isDragging = true;
	},

	/*! toggle edit group view div visibility
	 */
	toggleEditDirectoryView: function() {
		if (!this.params.divEditDirectory.is(":visible")) {
			this.params.divEditDirectory.show();
			if (typeof(this.params.divAddItem) != 'undefined') {
				this.params.divAddItem.hide();
			}
		} else {
			this.params.divEditDirectory.hide();
		}
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > this.params.divRight.height()) {
				this.params.divHandle.css('height', this.params.divLeft.height());
			} else {
				this.params.divHandle.css('height', this.params.divRight.height());
			}
		}
		return false;
	},

	/*! toggle add item view div visibility
	 */
	toggleAddItemView: function() {
		if (!this.params.divAddItem.is(":visible")) {
			this.params.divAddItem.show();
			this.params.divEditDirectory.hide();
		} else {
			this.params.divAddItem.hide();
		}
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > this.params.divRight.height()) {
				this.params.divHandle.css('height', this.params.divLeft.height());
			} else {
				this.params.divHandle.css('height', this.params.divRight.height());
			}
		}
		return false;
	},

	/*! toggle add file edit view div visibility and play with lock
	 *
	 * @param	string	id of the div
	 */
	toggleEditFileView: function(id) {
		var divid	= '#docid'+id,
		el		= jQuery(divid);

		if (!el.is(":visible")) {
			el.show();

			jQuery.get(this.params.docManURL, {
				group_id:	this.params.groupId,
				action:		'lockfile',
				lock:		1,
				fileid:		id,
				childgroup_id:	this.params.childGroupId
			});

			this.lockInterval[id] = setInterval("jQuery.get('" + this.params.docManURL + "', {group_id:"+this.params.groupId+",action:'lockfile',lock:1,fileid:"+id+",childgroup_id:"+this.params.childGroupId+"})",this.params.lockIntervalDelay);
		} else {
			el.hide();
			jQuery.get(this.params.docManURL, {
				group_id:	this.params.groupId,
				action:		'lockfile',
				lock:		0,
				fileid:		id,
				childgroup_id:	this.params.childGroupId
			});

			clearInterval(this.lockInterval[id]);
		}
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > this.params.divRight.height()) {
				this.params.divHandle.css('height', this.params.divLeft.height());
			} else {
				this.params.divHandle.css('height', this.params.divRight.height());
			}
		}
		return false;
	},

	/*! build list of id, comma separated
	 */
	buildUrlByCheckbox: function(id) {
		var CheckedBoxes = new Array();
		for (var h = 0; h < jQuery("input:checked").length; h++) {
			if (typeof(jQuery("input:checked")[h].className) != "undefined" && jQuery("input:checked")[h].className.match('checkeddocid'+id)) {
				CheckedBoxes.push(jQuery("input:checked")[h].value);
			}
		}
		return CheckedBoxes;
	},

	checkAll: function(id, type) {
		if (jQuery('#checkall'+type).is(':checked')) {
			jQuery('.'+id).each(function() {
				jQuery(this).attr('checked', true);
				});
			jQuery('#massaction'+type).show();
		} else {
			jQuery('.'+id).each(function() {
				jQuery(this).attr('checked', false);
			});
			jQuery('#massaction'+type).hide();
		}
	},

	checkgeneral: function(id) {
		if (jQuery(this).attr('checked', false)) {
			jQuery('#checkall'+id).attr('checked', false);
			jQuery('#massaction'+id).hide();
		}
		for (var h = 0; h < jQuery("input:checked").length; h++) {
			console.log("%s", jQuery("input:checked")[h].className);
			if (typeof(jQuery("input:checked")[h].className) != "undefined" && jQuery("input:checked")[h].className.match('checkeddocid'+id)) {
				jQuery('#massaction'+id).show();
			}
		}
	},

	computeDocumentsData: function() {
		/*
		TODO:
		build the array in php is not dynamic, and clearly, this sucks.
		It would be better to be able to ask JSON data containing the contents of a dir
		etc. and compute this data with Javascript in order to build the table.
		This will avoids to reload the page when you simply want to lock / remove / add a file etc.
		*/
	}
}

DocManAddItemController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.params.buttonDoc.click(jQuery.proxy(this, "toggleAddFileView"));
		this.params.buttonDir.click(jQuery.proxy(this, "toggleAddDirectoryView"));
		this.params.buttonZip.click(jQuery.proxy(this, "toggleInjectZipView"));
	},

	toggleAddDirectoryView: function() {
		if (!this.params.divCreateDir.is(":visible")) {
			this.params.divCreateDir.show();
			this.params.divCreateDoc.hide();
			this.params.divZipInject.hide();
		} else {
			this.params.divCreateDoc.hide();
			this.params.divZipInject.hide();
		}
		if (typeof(jQuery('#left')) != 'undefined' && typeof(jQuery('#right')) != 'undefined') {
			if (jQuery('#left').height() > jQuery('#right').height()) {
				jQuery('#handle').css('height', jQuery('#left').height());
			} else {
				jQuery('#handle').css('height', jQuery('#right').height());
			}
		}
	},

	toggleInjectZipView: function() {
		if (!this.params.divZipInject.is(":visible")) {
			this.params.divZipInject.show();
			this.params.divCreateDir.hide();
			this.params.divCreateDoc.hide();
		} else {
			this.params.divCreateDir.hide();
			this.params.divCreateDoc.hide();
		}
		if (typeof(jQuery('#left')) != 'undefined' && typeof(jQuery('#right')) != 'undefined') {
			if (jQuery('#left').height() > jQuery('#right').height()) {
				jQuery('#handle').css('height', jQuery('#left').height());
			} else {
				jQuery('#handle').css('height', jQuery('#right').height());
			}
		}
	},

	toggleAddFileView: function() {
		if (!this.params.divCreateDoc.is(":visible")) {
			this.params.divCreateDoc.show();
			this.params.divCreateDir.hide();
			this.params.divZipInject.hide();
		} else {
			this.params.divCreateDir.hide();
			this.params.divZipInject.hide();
		}
		if (typeof(jQuery('#left')) != 'undefined' && typeof(jQuery('#right')) != 'undefined') {
			if (jQuery('#left').height() > jQuery('#right').height()) {
				jQuery('#handle').css('height', jQuery('#left').height());
			} else {
				jQuery('#handle').css('height', jQuery('#right').height());
			}
		}
	},
}
