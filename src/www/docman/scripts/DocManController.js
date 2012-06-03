/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011-2012, Franck Villaume - TrivialDev
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
	this.initModalEditWindow();
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

	resizableDiv: function() {
		if (typeof(this.params.divHandle) != 'undefined') {
			this.params.divHandle.mousedown(jQuery.proxy(this, "dragging"));
			var params = this.params;
			var w = jQuery('#maindiv').width() - this.params.divHandle.width() - 10;
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

	initSize: function() {
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > this.params.divRight.height()) {
				this.params.divHandle.css('height', this.params.divLeft.height());
			} else {
				this.params.divHandle.css('height', this.params.divRight.height());
			}
			if (jQuery.Storage.get("treesize") != 0) {
				this.params.divLeft.css('width', parseInt(jQuery.Storage.get("treesize")));
				var w = jQuery('#maindiv').width() - this.params.divHandle.width() - 10;
				this.params.divRight.css('width', w - this.params.divLeft.width());
			}
		}
	},

	initModalEditWindow: function() {
		var modalId = this.params.divEditFile;
		jQuery(modalId).dialog({
			autoOpen: false,
			height: 350,
			width: 450,
			modal: true,
			title: this.params.divEditTitle,
			buttons: {
				Save: jQuery.proxy(function() {
					jQuery('#editdocdata').submit();
					var id = jQuery('#docid').attr('value');
					jQuery.get(this.params.docManURL, {
						group_id:	this.params.groupId,
						action:		'lockfile',
						lock:		0,
						fileid:		id,
						childgroup_id:	this.params.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					jQuery(modalId).dialog( "close" );
				}, this),
				Cancel: jQuery.proxy(function() {
					var id = jQuery('#docid').attr('value');
					jQuery.get(this.params.docManURL, {
						group_id:	this.params.groupId,
						action:		'lockfile',
						lock:		0,
						fileid:		id,
						childgroup_id:	this.params.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					jQuery(modalId).dialog( "close" );
				}, this),
			},
		});
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
	 * @param id the docid
	 */
	toggleEditFileView: function(docparams) {
		this.docparams = docparams;
		jQuery('#title').attr('value', this.docparams.title);
		jQuery('#description').attr('value', this.docparams.description);
		jQuery('#docid').attr('value', this.docparams.id);
		if (this.docparams.isURL) {
			jQuery('#uploadnewroweditfile').hide();
			jQuery('#fileurlroweditfile').show();
		} else {
			jQuery('#fileurlroweditfile').hide();
			jQuery('#uploadnewroweditfile').show();
		}
		if (!this.docparams.useCreateOnline || !this.docparams.isText) {
			jQuery('#editonlineroweditfile').hide();
			jQuery('#editor').attr('disabled', true);
		}
		jQuery('#filelink').text(this.docparams.filename);
		if (this.docparams.statusId != 2) {
			if (this.docparams.isURL) {
				jQuery('#filelink').attr('href', this.docparams.filename);
			} else {
				jQuery('#filelink').attr('href', this.docparams.docManURL + '/view.php/' + this.docparams.groupId + '/' + this.docparams.id + '/' + this.docparams.filename);
			}
		}
		jQuery('#doc_group').empty();
		jQuery.each(this.docparams.docgroupDict, function(key, value) {
			jQuery('#doc_group').append(jQuery("<option>").text(key).attr("value",value));
		});
		jQuery('#doc_group option[value='+this.docparams.docgroupId+']').attr("selected", "selected");
		jQuery('#stateid').empty();
		jQuery.each(this.docparams.statusDict, function(key, value) {
			jQuery('#stateid').append(jQuery("<option>").text(key).attr("value",value));
		});
		jQuery('#stateid option[value='+this.docparams.statusId+']').attr("selected", "selected");
		if (this.docparams.isText) {
			jQuery.getJSON(this.docparams.docManURL + '/?group_id=' + this.docparams.groupId + '&action=getfile&fileid=' + this.docparams.id , jQuery.proxy(function(data){
			jQuery('#defaulteditzone').text(data.body);
			}, this));
		}
		jQuery('#editdocdata').attr('action', this.docparams.action);
		
		jQuery.get(this.docparams.docManURL, {
				group_id:	this.docparams.groupId,
				action:		'lockfile',
				lock:		1,
				fileid:		this.docparams.id,
				childgroup_id:	this.docparams.childGroupId
			});		
		this.lockInterval[this.docparams.id] = setInterval("jQuery.get('" + this.docparams.docManURL + "', {group_id:"+this.docparams.groupId+",action:'lockfile',lock:1,fileid:"+this.docparams.id+",childgroup_id:"+this.docparams.childGroupId+"})",this.docparams.lockIntervalDelay);
		jQuery(this.params.divEditFile).dialog("open");

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
