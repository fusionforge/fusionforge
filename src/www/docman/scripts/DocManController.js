/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011-2014, Franck Villaume - TrivialDev
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
	if (this.params.enableResize) {
		this.resizableDiv();
	}
	this.initModalEditWindow();
};

DocManAddItemController = function(params)
{
	this.params	= params;
	this.bindControls();
};

DocManAddFileController = function(params)
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
		var splitterPosition = '30%';
		var mainwidth = jQuery('#maindiv').width();
		if (jQuery.Storage.get('splitterStyle') !== undefined) {
			var storedSplitterPosition = jQuery.Storage.get('splitterStyle').replace(/px;?/g, '').replace(/left: /g, '');
			splitterPosition = Math.round(storedSplitterPosition * 100 / mainwidth )+'%';
		}
		if (this.params.page == 'trashfile') {
			(this.params.divLeft.height() > this.params.divRight.height()) ? mainheight = this.params.divLeft.height() : mainheight = this.params.divRight.height();
		} else {
			var fixwidth = 0;
			if (jQuery('#editFile').length) {
				fixwidth = jQuery('#editFile').height() - jQuery('#resourcePopupContainer').height();
				if ( fixwidth < 0) {
					fixwidth = 0;
				}
			}
			if (fixwidth == 0) {
				fixwidth = -40;
			}
			var totalRightHeight = 0;
			this.params.divRight.children().each(function() {
					if (jQuery(this).is(':visible')) {
						totalRightHeight = totalRightHeight + jQuery(this).outerHeight();
					}
				});
			totalRightHeight = totalRightHeight - fixwidth;
			(this.params.divRight.height() - fixwidth < 0) ? useRightHeight = this.params.divRight.height() : useRightHeight = this.params.divRight.height() - fixwidth;
			(useRightHeight < totalRightHeight) ? useRightHeight = totalRightHeight : useRightHeight ;
			(this.params.divLeft.height() > this.params.divRight.height()) ? mainheight = this.params.divLeft.height() : mainheight = useRightHeight;
		}
		jQuery('#views').height(mainheight)
				.split({orientation:'vertical', limit:100, position: splitterPosition});
		jQuery('.vsplitter').mouseup(function(){
			jQuery.Storage.set('splitterStyle',''+jQuery('.vsplitter').attr('style'));
		});
	},

	initModalEditWindow: function() {
		var modalId = this.params.divEditFile;
		jQuery(modalId).dialog({
			autoOpen: false,
			width: 475,
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
				}, this)
			}
		});
		jQuery(modalId).bind('dialogclose', jQuery.proxy(function() {
			var id = jQuery('#docid').attr('value');
			jQuery.get(this.params.docManURL, {
				group_id:	this.params.groupId,
				action:		'lockfile',
				lock:		0,
				fileid:		id,
				childgroup_id:	this.params.childGroupId
			});
			clearInterval(this.lockInterval[id]);
		}, this));
	},

	/*! toggle edit group view div visibility
	 */
	toggleEditDirectoryView: function() {
		if (!this.params.divEditDirectory.is(":visible")) {
			this.params.divEditDirectory.show();
			if (typeof(this.params.divAddItem) != 'undefined') {
				this.params.divAddItem.hide();
			}
			computeHeight = this.params.divRight.height() + this.params.divEditDirectory.height();
			currentLeftHeight = this.params.divLeft.height();
			this.params.divLeft.height(currentLeftHeight + this.params.divEditDirectory.height());
		} else {
			this.params.divEditDirectory.hide();
			computeHeight = this.params.divRight.height() - this.params.divEditDirectory.height();
			currentLeftHeight = this.params.divLeft.height();
			this.params.divLeft.height(currentLeftHeight - this.params.divEditDirectory.height());
		}
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > computeHeight) {
				jQuery('#views').height(this.params.divLeft.height());
			} else {
				jQuery('#views').height(computeHeight);
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
			computeHeight = this.params.divRight.height() + jQuery(this.params.divAddItem).height();
			currentLeftHeight = this.params.divLeft.height();
			this.params.divLeft.height(currentLeftHeight + jQuery(this.params.divAddItem).height());
		} else {
			this.params.divAddItem.hide();
			computeHeight = this.params.divRight.height() - jQuery(this.params.divAddItem).height();
			currentLeftHeight = this.params.divLeft.height();
			this.params.divLeft.height(currentLeftHeight - jQuery(this.params.divAddItem).height());
		}
		if (typeof(this.params.divLeft) != 'undefined' && typeof(this.params.divRight) != 'undefined') {
			if (this.params.divLeft.height() > computeHeight) {
				jQuery('#views').height(this.params.divLeft.height());
			} else {
				jQuery('#views').height(computeHeight);
			}
		}
		return false;
	},

	/*! toggle add file edit view div visibility and play with lock
	 *
	 * @param docparams array
	 */
	toggleEditFileView: function(docparams) {
		this.docparams = docparams;
		jQuery('#title').val(this.docparams.title);
		jQuery('#description').val(this.docparams.description);
		jQuery('#docid').val(this.docparams.id);
		if (this.docparams.isHtml) {
			jQuery('#defaulteditfiletype').val('text/html');
		}
		if (this.docparams.isText && ! this.docparams.isHtml) {
			jQuery('#defaulteditfiletype').val('text/plain');
		}
		if (this.docparams.isURL) {
			jQuery('#uploadnewroweditfile').hide();
			jQuery('#fileurlroweditfile').show();
			jQuery('#fileurlroweditfile').find('input').attr("required", "required");
			jQuery('#fileurlroweditfile').find('input').val(this.docparams.filename);
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
				if (data) {
					jQuery('#defaulteditzone').text(data.body);
				}
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
			if (typeof(jQuery("input:checked")[h].className) != "undefined" && jQuery("input:checked")[h].className.match('checkeddocid'+id)) {
				jQuery('#massaction'+id).show();
			}
		}
	}
};

DocManAddItemController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.params.submitZip.click(jQuery.proxy(this, "submitFormZip"));
	},

	submitFormZip: function() {
		this.params.injectZip.submit();
		this.params.submitZip.attr('disabled', true);
	}
};

DocManAddFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.params.buttonFile.click(jQuery.proxy(this, "toggleFileRowView"));
		this.params.buttonUrl.click(jQuery.proxy(this, "toggleUrlRowView"));
		if (typeof(this.params.buttonManualUpload) != 'undefined') {
			this.params.buttonManualUpload.click(jQuery.proxy(this, "toggleManualUploadView"));
		}
		if (typeof(this.params.buttonEditor) != 'undefined') {
			this.params.buttonEditor.click(jQuery.proxy(this, "toggleEditorView"));
		}
	},

	toggleFileRowView: function() {
		this.params.fileRow.show();
		this.params.fileRow.find('input').attr("required", "required");
		this.params.urlRow.hide();
		this.params.urlRow.find('input').removeAttr("required");
		this.params.pathRow.hide();
		this.params.pathRow.find('input').removeAttr("required");
		this.params.editRow.hide();
		this.params.editNameRow.hide();
	},

	toggleUrlRowView: function() {
		this.params.fileRow.hide();
		this.params.fileRow.find('input').removeAttr("required");
		this.params.urlRow.show();
		this.params.urlRow.find('input').attr("required", "required");
		this.params.pathRow.hide();
		this.params.pathRow.find('input').removeAttr("required");
		this.params.editRow.hide();
		this.params.editNameRow.hide();
	},

	toggleManualUploadView: function() {
		this.params.fileRow.hide();
		this.params.fileRow.find('input').removeAttr("required");
		this.params.urlRow.hide();
		this.params.urlRow.find('input').removeAttr("required");
		this.params.pathRow.show();
		this.params.pathRow.find('input').attr("required", "required");
		this.params.editRow.hide();
		this.params.editNameRow.hide();
	},

	toggleEditorView: function() {
		this.params.fileRow.hide();
		this.params.fileRow.find('input').removeAttr("required");
		this.params.urlRow.hide();
		this.params.urlRow.find('input').removeAttr("required");
		this.params.pathRow.hide();
		this.params.pathRow.find('input').removeAttr("required");
		this.params.editRow.show();
		this.params.editNameRow.show();
	}
};
