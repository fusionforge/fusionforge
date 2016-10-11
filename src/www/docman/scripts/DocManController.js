/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011, Alain Peyrat
 * Copyright 2011-2016, Franck Villaume - TrivialDev
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
	this.listfileparams	= params;
	this.bindControls();
	if (this.listfileparams.enableResize) {
		this.resizableDiv();
	}
	this.initModalEditWindow();
	this.initModelNotifyWindow();
};

DocManAddItemController = function(params)
{
	this.additemparams	= params;
	this.bindControls();
};

DocManAddFileController = function(params)
{
	this.addfileparams	= params;
	this.bindControls();
};

DocManSearchController = function(params)
{
	this.searchparams	= params;
	this.bindControls();
};

DocManListFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		if (typeof(this.listfileparams.buttonAddItem) != 'undefined') {
			this.listfileparams.buttonAddItem.click(jQuery.proxy(this, "toggleAddItemView"));
		}
	},

	resizableDiv: function() {
		var splitterPosition = '30%';
		var mainwidth = jQuery('#maindiv').innerWidth();
		if (jQuery.Storage.get('splitterStyle') !== undefined) {
			var storedSplitterPosition = jQuery.Storage.get('splitterStyle').replace(/px;?/g, '').replace(/left: /g, '');
			splitterPosition = Math.round(storedSplitterPosition * 100 / mainwidth )+'%';
		}
		if (this.listfileparams.page == 'trashfile') {
			(this.listfileparams.divLeft.outerHeight() > this.listfileparams.divRight.outerHeight()) ? mainheight = this.listfileparams.divLeft.outerHeight() : mainheight = this.listfileparams.divRight.outerHeight();
		} else {
			var fixwidth = -40;
			if (jQuery('#editFile').length >= 1) {
				fixwidth += jQuery('#editFile').outerHeight() - jQuery('[aria-describedby="editFile"]').outerHeight();
			}
			if (jQuery('#notifyUsers').length >= 1) {
				fixwidth += jQuery('#notifyUsers').outerHeight() - jQuery('[aria-describedby="notifyUsers"]').outerHeight();
			}
			var totalRightHeight = 0;
			this.listfileparams.divRight.children().each(function() {
					if (jQuery(this).is(':visible')) {
						totalRightHeight += jQuery(this).outerHeight();
					}
				});
			totalRightHeight -= fixwidth;
			(this.listfileparams.divRight.outerHeight() - fixwidth < 0) ? useRightHeight = this.listfileparams.divRight.outerHeight() : useRightHeight = this.listfileparams.divRight.outerHeight() - fixwidth;
			(useRightHeight < totalRightHeight) ? useRightHeight = totalRightHeight : useRightHeight ;
			(this.listfileparams.divLeft.outerHeight() + 30 > this.listfileparams.divRight.outerHeight()) ? mainheight = this.listfileparams.divLeft.outerHeight() + 30 : mainheight = useRightHeight;
		}
		jQuery('#views').height(mainheight)
				.split({orientation:'vertical', limit:100, position: splitterPosition});
		jQuery('.vsplitter').mouseup(function(){
			jQuery.Storage.set('splitterStyle',''+jQuery('.vsplitter').attr('style'));
		});
	},

	initModalEditWindow: function() {
		var modalId = this.listfileparams.divEditFile;
		jQuery(modalId).dialog({
			autoOpen: false,
			width: 1000,
			modal: true,
			title: this.listfileparams.divEditTitle,
			buttons: {
				Save: jQuery.proxy(function() {
					jQuery('#editdocdata').submit();
					var id = jQuery('#docid').attr('value');
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		id,
						type:		'file',
						childgroup_id:	this.listfileparams.childGroupId
					});
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		this.listfileparams.docgroupId,
						type:		'dir',
						childgroup_id:	this.listfileparams.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
					jQuery(modalId).dialog( "close" );
				}, this),
				Cancel: jQuery.proxy(function() {
					var id = jQuery('#docid').attr('value');
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		id,
						type:		'file',
						childgroup_id:	this.listfileparams.childGroupId
					});
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		this.listfileparams.docgroupId,
						type:		'dir',
						childgroup_id:	this.listfileparams.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
					jQuery(modalId).dialog('close');
				}, this)
			}
		});
		jQuery(modalId).bind('dialogclose', jQuery.proxy(function() {
			var id = jQuery('#docid').attr('value');
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				itemid:		id,
				type:		'file',
				childgroup_id:	this.listfileparams.childGroupId
			});
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				itemid:		this.listfileparams.docgroupId,
				type:		'dir',
				childgroup_id:	this.listfileparams.childGroupId
			});
			clearInterval(this.lockInterval[id]);
			clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
		}, this));
	},

	initModelNotifyWindow: function() {
		var modalId = this.listfileparams.divNotifyUsers;
		jQuery(modalId).dialog({
			autoOpen: false,
			width: 475,
			modal: true,
			title: this.listfileparams.divNotifyTitle,
			buttons: {
				Save: { text: this.listfileparams.divNotifySaveButtonTxt,
					click: jQuery.proxy(function() {
					jQuery('#notifyusersdoc').submit();
					var id = jQuery('#notifydocid').attr('value');
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		id,
						type:		'file',
						childgroup_id:	this.listfileparams.childGroupId
					});
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		this.listfileparams.docgroupId,
						type:		'dir',
						childgroup_id:	this.listfileparams.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
					jQuery(modalId).dialog( "close" );
				}, this)},
				Cancel: jQuery.proxy(function() {
					var id = jQuery('#notifydocid').attr('value');
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		id,
						type:		'file',
						childgroup_id:	this.listfileparams.childGroupId
					});
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		0,
						itemid:		this.listfileparams.docgroupId,
						type:		'dir',
						childgroup_id:	this.listfileparams.childGroupId
					});
					clearInterval(this.lockInterval[id]);
					clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
					jQuery(modalId).dialog('close');
				}, this)
			}
		});
		jQuery(modalId).bind('dialogclose', jQuery.proxy(function() {
			var id = jQuery('#notifydocid').attr('value');
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				itemid:		id,
				type:		'file',
				childgroup_id:	this.listfileparams.childGroupId
			});
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				itemid:		this.listfileparams.docgroupId,
				type:		'dir',
				childgroup_id:	this.listfileparams.childGroupId
			});
			clearInterval(this.lockInterval[id]);
			clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
		}, this));
	},

	/*! toggle edit group view div visibility
	 */
	toggleEditDirectoryView: function() {
		if (!this.listfileparams.divEditDirectory.is(":visible")) {
			jQuery.getJSON(this.listfileparams.docManURL + '/?group_id='+this.listfileparams.groupId+'&action=lock&json=1&type=dir&itemid='+this.listfileparams.docgroupId, jQuery.proxy(function(data){
				if (typeof data.html != 'undefined') {
					jQuery('#maindiv > .feedback').remove();
					jQuery('#maindiv > .error').remove();
					jQuery('#maindiv > .warning_msg').remove();
					jQuery('#maindiv').prepend(data.html);
				} else {
					this.listfileparams.divEditDirectory.show();
					if (typeof(this.listfileparams.divAddItem) != 'undefined') {
						this.listfileparams.divAddItem.hide();
					}
					computeHeight = this.listfileparams.divRight.outerHeight() + this.listfileparams.divEditDirectory.outerHeight();
					currentLeftHeight = this.listfileparams.divLeft.outerHeight();
					this.listfileparams.divLeft.height(currentLeftHeight + this.listfileparams.divEditDirectory.outerHeight());
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		1,
						type:		'dir',
						itemid:		this.listfileparams.docgroupId,
						childgroup_id:	this.listfileparams.childGroupId
					});
					this.lockInterval[this.listfileparams.docgroupId] = setInterval("jQuery.get('" + this.listfileparams.docManURL + "/', {group_id:"+this.listfileparams.groupId+", action:'lock', lock:1, type:'dir', itemid:"+this.listfileparams.docgroupId+", childgroup_id:"+this.listfileparams.childGroupId+"})", this.listfileparams.lockIntervalDelay);
					if (typeof(this.listfileparams.divLeft) != 'undefined' && typeof(this.listfileparams.divRight) != 'undefined') {
						if (this.listfileparams.divLeft.outerHeight() > computeHeight) {
							jQuery('#views').height(this.listfileparams.divLeft.outerHeight());
						} else {
							jQuery('#views').height(computeHeight);
						}
					}
				}
			}, this));
		} else {
			this.listfileparams.divEditDirectory.hide();
			computeHeight = this.listfileparams.divRight.outerHeight() - this.listfileparams.divEditDirectory.outerHeight();
			currentLeftHeight = this.listfileparams.divLeft.outerHeight();
			this.listfileparams.divLeft.height(currentLeftHeight - this.listfileparams.divEditDirectory.outerHeight());
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				type:		'dir',
				itemid:		this.listfileparams.docgroupId,
				childgroup_id:	this.listfileparams.childGroupId
			});
			clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
			if (typeof(this.listfileparams.divLeft) != 'undefined' && typeof(this.listfileparams.divRight) != 'undefined') {
				if (this.listfileparams.divLeft.outerHeight() > computeHeight) {
					jQuery('#views').height(this.listfileparams.divLeft.outerHeight());
				} else {
					jQuery('#views').height(computeHeight);
				}
			}
		}
		return false;
	},

	/*! toggle add item view div visibility
	 */
	toggleAddItemView: function() {
		if (!this.listfileparams.divAddItem.is(":visible")) {
			jQuery.getJSON(this.listfileparams.docManURL + '/?group_id='+this.listfileparams.groupId+'&action=lock&json=1&type=dir&itemid='+this.listfileparams.docgroupId, jQuery.proxy(function(data){
				if (typeof data.html != 'undefined') {
					jQuery('#maindiv > .feedback').remove();
					jQuery('#maindiv > .error').remove();
					jQuery('#maindiv > .warning_msg').remove();
					jQuery('#maindiv').prepend(data.html);
				} else {
					jQuery.get(this.listfileparams.docManURL+'/', {
						group_id:	this.listfileparams.groupId,
						action:		'lock',
						lock:		1,
						type:		'dir',
						itemid:		this.listfileparams.docgroupId,
						childgroup_id:	this.listfileparams.childGroupId
					});
					this.lockInterval[this.listfileparams.docgroupId] = setInterval("jQuery.get('"+this.listfileparams.docManURL+"/', {group_id:"+this.listfileparams.groupId+", action:'lock', lock:1, type:'dir', itemid:"+this.listfileparams.docgroupId+", childgroup_id:"+this.listfileparams.childGroupId+"})", this.listfileparams.lockIntervalDelay);
					this.listfileparams.divAddItem.show();
					this.listfileparams.divEditDirectory.hide();
					computeHeight = this.listfileparams.divRight.outerHeight() + jQuery(this.listfileparams.divAddItem).outerHeight();
					currentLeftHeight = this.listfileparams.divLeft.outerHeight();
					this.listfileparams.divLeft.height(currentLeftHeight + jQuery(this.listfileparams.divAddItem).outerHeight());
					if (typeof(this.listfileparams.divLeft) != 'undefined' && typeof(this.listfileparams.divRight) != 'undefined') {
						if (this.listfileparams.divLeft.outerHeight() > computeHeight) {
							jQuery('#views').height(this.listfileparams.divLeft.outerHeight());
						} else {
							jQuery('#views').height(computeHeight);
						}
					}
				}
			}, this));
		} else {
			jQuery.get(this.listfileparams.docManURL+'/', {
				group_id:	this.listfileparams.groupId,
				action:		'lock',
				lock:		0,
				type:		'dir',
				itemid:		this.listfileparams.docgroupId,
				childgroup_id:	this.listfileparams.childGroupId
			});
			clearInterval(this.lockInterval[this.listfileparams.docgroupId]);
			this.listfileparams.divAddItem.hide();
			computeHeight = this.listfileparams.divRight.outerHeight() - jQuery(this.listfileparams.divAddItem).outerHeight();
			currentLeftHeight = this.listfileparams.divLeft.outerHeight();
			this.listfileparams.divLeft.height(currentLeftHeight - jQuery(this.listfileparams.divAddItem).outerHeight());
			if (typeof(this.listfileparams.divLeft) != 'undefined' && typeof(this.listfileparams.divRight) != 'undefined') {
				if (this.listfileparams.divLeft.outerHeight() > computeHeight) {
					jQuery('#views').height(this.listfileparams.divLeft.outerHeight());
				} else {
					jQuery('#views').height(computeHeight);
				}
			}
		}
		return false;
	},

	/*! toggle add file edit view div visibility and play with lock
	 *
	 * @param params array
	 */
	toggleEditFileView: function(params) {
		this.docparams = params;
		this.listfileparams.tableAddVersion.hide();
		jQuery('#doc_group').empty();
                for (var i = 0; i < this.docparams.docgroupDict.length; i++) {
                        jQuery('#doc_group').append(jQuery('<option>').text(this.docparams.docgroupDict[i][1]).attr('value', this.docparams.docgroupDict[i][0]));
                };
		jQuery('#doc_group option[value='+this.docparams.docgroupId+']').attr('selected', 'selected');
		jQuery('#stateid').empty();
		jQuery.each(this.docparams.statusDict, function(key, value) {
			jQuery('#stateid').append(jQuery('<option>').text(key).attr('value',value));
		});
		jQuery('#stateid option[value='+this.docparams.statusId+']').attr('selected', 'selected');
		jQuery('#docid').val(this.docparams.id);
		var docid_groupid = this.listfileparams.groupId;
		if (this.listfileparams.childGroupId != 0) {
			docid_groupid = this.listfileparams.childGroupId;
		}
		jQuery.getJSON(this.listfileparams.docManURL + '/?group_id=' + docid_groupid + '&action=getdocversions&docid='+ this.docparams.id, jQuery.proxy(function(data){
				if (typeof data.html != 'undefined') {
					jQuery('#editFile > .feedback').remove();
					jQuery('#editFile > .error').remove();
					jQuery('#editFile > .warning_msg').remove();
					jQuery('#editFile').prepend(data.html);
				} else {
					jQuery('#sortable_doc_version_table > tbody').children().remove();
					jQuery('#sortable_doc_version_table > tbody').css('max-height', '400px').css('overflow-y', 'auto').css('display', 'block');
					jQuery('#sortable_doc_version_table > thead > tr').css('display', 'block');
					eachdocparams = this.docparams;
					jQuery.each(data, function (i, val) {
						//_('ID (x)'), _('Filename'), _('Title'), _('Description'), _('Comment'), _('Author'), _('Last Time'), _('Size'), _('Actions'));
						currenttdcontent = '';
						if (val.current_version == 1) {
							currenttdcontent += ' (x)';
						}
						if (eachdocparams.statusId != 2) {
							filenametdcontent = jQuery('<a>'+val.filename+'</a>');
							if (val.filetype == 'URL') {

								filenametdcontent.attr('href', val.filename);
							} else {
								filenametdcontent.attr('href', eachdocparams.docManURL+'/view.php/'+eachdocparams.groupId+'/versions/'+eachdocparams.id+'/'+val.version.substring(1));
							}
						} else {
							filenametdcontent = jQuery('<span>'+val.filename+'</span>');
						}
						versionactiontdcontent = '';
						versionActionsArrayLength = val.versionactions.length;
						for (var i = 0; i < versionActionsArrayLength; i++) {
							versionactiontdcontent += val.versionactions[i];
						}
						// please sync with the editfile.php widths if you change it here.
						var htmlString = '<tr id="docversion'+val.version.substr(1)+'" ><td style="width: 60px">'+val.version.substr(1)+currenttdcontent+'</td><td style="width: 150px">'+filenametdcontent[0].outerHTML+'</td><td style="width: 150px">'+val.title+'</td><td style="width: 150px">'+val.description+'</td><td style="width: 110px">'+val.vcomment+'</td><td style="width: 100px">'+val.created_by_username+'</td><td style="width: 100px">'+val.lastdate+'</td><td style="width: 50px">'+val.filesize_readable+'</td><td style="width: 50px">'+versionactiontdcontent+'</td></tr>'
						jQuery('#sortable_doc_version_table > tbody:last-child').append(htmlString);
						});
				}
			}, this));

		jQuery('#editdocdata').attr('action', this.docparams.action);

		jQuery.get(this.docparams.docManURL+'/', {
				group_id:	this.docparams.groupId,
				action:		'lock',
				lock:		1,
				type:		'dir',
				itemid:		this.docparams.docgroupId,
				childgroup_id:	this.docparams.childGroupId
			});
		this.lockInterval[this.docparams.id] = setInterval("jQuery.get('" + this.docparams.docManURL + "/', {group_id:"+this.docparams.groupId+", action:'lock', lock:1, type:'file', itemid:"+this.docparams.id+", childgroup_id:"+this.docparams.childGroupId+"})", this.docparams.lockIntervalDelay);
		this.lockInterval[this.docparams.docgroupId] = setInterval("jQuery.get('" + this.docparams.docManURL + "/', {group_id:"+this.docparams.groupId+", action:'lock', lock:1, type: 'dir', itemid:"+this.docparams.docgroupId+", childgroup_id:"+this.docparams.childGroupId+"})", this.docparams.lockIntervalDelay);
		jQuery(this.listfileparams.divEditFile).dialog('open');
		return false;
	},

	toggleAddVersionView: function() {
		jQuery('#title').val('');
		jQuery('#description').val('');
		jQuery(':file').val('');
		jQuery('#edit_version').val('');
		jQuery('#defaulteditzone').text();
		jQuery('#current_version').prop('checked', false);
		jQuery('#current_version').attr('onclick', 'return true');
		if (!this.listfileparams.tableAddVersion.is(':visible')) {
			jQuery('#new_version').val(1);
			this.listfileparams.tableAddVersion.show();
		} else {
			this.listfileparams.tableAddVersion.hide();
			jQuery('#new_version').val(0);
		}
	},

	toggleEditVersionView: function(params) {
		this.version = params;
		if (this.version.isHtml) {
			jQuery('#defaulteditfiletype').val('text/html');
		}
		if (this.version.isText && !this.version.isHtml) {
			jQuery('#defaulteditfiletype').val('text/plain');
		}
		if (this.version.isText) {
			jQuery.getJSON(this.listfileparams.docManURL+'/?group_id='+this.docparams.groupId+'&action=getfile&type=file&itemid='+this.docparams.id+'&version='+this.version.version, jQuery.proxy(function(data){
				if (data) {
					jQuery('#defaulteditzone').text(data.body);
				}
			}, this));
		}

		if (!this.listfileparams.tableAddVersion.is(':visible')) {
			if (this.version.isURL) {
				jQuery('#uploadnewroweditfile').hide();
				jQuery('#fileurlroweditfile').show();
				jQuery('#fileurlroweditfile').find('input').attr('required', 'required').prop('required', true);
				jQuery('#fileurlroweditfile').find('input').val(this.version.filename);
				jQuery('#editonlineroweditfile').hide();
				jQuery('#editor').attr('disabled', true);
				jQuery('#editButtonUrl').prop('checked', true);
			} else if (this.docparams.useCreateOnline && this.version.isText){
				jQuery('#fileurlroweditfile').hide();
				jQuery('#uploadnewroweditfile').hide();
				jQuery('#editonlineroweditfile').show();
				jQuery('#editor').removeAttr('disabled');
				jQuery('#editButtonEditor').prop('checked', true);
			} else {
				jQuery('#onlineroweditfile').hide();
				jQuery('#editor').attr('disabled', true);
				jQuery('#fileurlroweditfile').hide();
				jQuery('#uploadnewroweditfile').show();
				jQuery('#editButtonFile').prop('checked', true);
			}
			jQuery('#title').val(this.version.title);
			jQuery('#description').val(this.version.description);
			jQuery('#vcomment').val(this.version.vcomment);
			jQuery('#edit_version').val(this.version.version);
			if (this.version.current_version == 1) {
				jQuery('#current_version').attr('checked', 'checked').prop('checked', true);
				jQuery('#current_version').attr('onclick', 'return false');
			}
			this.listfileparams.tableAddVersion.show();
		} else {
			this.listfileparams.tableAddVersion.hide();
			jQuery('#title').val('');
			jQuery('#description').val('');
			jQuery('#vcomment').val('');
			jQuery(':file').val('');
			jQuery('#edit_version').val('');
			jQuery('#current_version').removeAttr('checked');
			jQuery('#current_version').attr('onclick', 'return true');
			jQuery('#fileurlroweditfile').find('input').val('');
			jQuery('#fileurlroweditfile').find('input').removeAttr('required');
			jQuery('#fileurlroweditfile').hide();
			jQuery('#uploadnewroweditfile').hide();
			jQuery('#editonlineroweditfile').hide();
			jQuery('#defaulteditzone').text('');
		}
	},

	deleteVersion: function(params) {
		this.delversion = params;
		jQuery.getJSON(this.docparams.docManURL + '/?group_id=' + this.docparams.groupId + '&action=deleteversion&docid='+this.docparams.id+'&version='+this.delversion.version , jQuery.proxy(function(data){
				if (typeof data.html != 'undefined') {
					jQuery('#editFile > .feedback').remove();
					jQuery('#editFile > .error').remove();
					jQuery('#editFile > .warning_msg').remove();
					jQuery('#editFile').prepend(data.html);
				}
				if (typeof data.status != 'undefined') {
					if (data.status == 1) {
						jQuery('#docversion'+this.version).remove();
						if (jQuery('#sortable_doc_version_table tr').length <= 2) {
							jQuery('#version_action_delete').remove();
						}
					}
				}
			}, this.delversion));
	},

	toggleMoveFileView: function() {
		if (!this.listfileparams.divMoveFile.is(':visible')) {
			this.listfileparams.divMoveFile.show();
			jQuery('#movefileinput').val(function() {
					var CheckedBoxes = new Array();
					for (var h = 0; h < jQuery('input:checked').length; h++) {
						if (typeof(jQuery('input:checked')[h].className) != 'undefined' && jQuery('input:checked')[h].className.match('checkeddocidactive')) {
							CheckedBoxes.push(jQuery('input:checked')[h].value);
						}
					}
					return CheckedBoxes;
				});
		} else {
			this.listfileparams.divMoveFile.hide();
		}
	},

	toggleNotifyUserView: function(params) {
		this.notifyparams = params;
		jQuery('#notifytitle').text(this.notifyparams.title);
		jQuery('#notifydescription').text(this.notifyparams.description);
		jQuery('#notifydocid').val(this.notifyparams.id);
		jQuery('#notifyfilelink').text(this.notifyparams.filename);
		if (this.notifyparams.statusId != 2) {
			if (this.notifyparams.isURL) {
				jQuery('#notifyfilelink').attr('href', this.notifyparams.filename);
			} else {
				jQuery('#notifyfilelink').attr('href', this.notifyparams.docManURL+'/view.php/'+this.notifyparams.groupId+'/'+this.notifyparams.id);
			}
		}

		jQuery('#notifyusersdoc').attr('action', this.notifyparams.action);
		jQuery.get(this.notifyparams.docManURL+'/', {
				group_id:	this.notifyparams.groupId,
				action:		'lock',
				lock:		1,
				type:		'dir',
				itemid:		this.notifyparams.docgroupId,
				childgroup_id:	this.notifyparams.childGroupId
			});
		this.lockInterval[this.notifyparams.id] = setInterval("jQuery.get('" + this.notifyparams.docManURL + "/', {group_id:"+this.notifyparams.groupId+",action:'lock',lock:1,type:'file',itemid:"+this.notifyparams.id+",childgroup_id:"+this.notifyparams.childGroupId+"})", this.notifyparams.lockIntervalDelay);
		this.lockInterval[this.notifyparams.docgroupId] = setInterval("jQuery.get('" + this.notifyparams.docManURL + "/', {group_id:"+this.notifyparams.groupId+",action:'lock',lock:1,type:'dir',itemid:"+this.notifyparams.docgroupId+",childgroup_id:"+this.notifyparams.childGroupId+"})", this.notifyparams.lockIntervalDelay);
		jQuery(this.listfileparams.divNotifyUsers).dialog('open');

		return false;

	},

	/*! build list of id, comma separated
	 */
	buildUrlByCheckbox: function(id) {
		var CheckedBoxes = new Array();
		for (var h = 0; h < jQuery('input:checked').length; h++) {
			if (typeof(jQuery('input:checked')[h].className) != 'undefined' && jQuery('input:checked')[h].className.match('checkeddocid'+id)) {
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
			jQuery('#movefile').hide();
		}
		for (var h = 0; h < jQuery('input:checked').length; h++) {
			if (typeof(jQuery('input:checked')[h].className) != 'undefined' && jQuery('input:checked')[h].className.match('checkeddocid'+id)) {
				jQuery('#massaction'+id).show();
				break;
			}
		}
	}
};

DocManAddItemController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.additemparams.submitZip.click(jQuery.proxy(this, "submitFormZip"));
	},

	submitFormZip: function() {
		this.additemparams.injectZip.submit();
		this.additemparams.submitZip.attr('disabled', true);
	}
};

DocManAddFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.addfileparams.buttonFile.click(jQuery.proxy(this, "toggleFileRowView"));
		this.addfileparams.buttonUrl.click(jQuery.proxy(this, "toggleUrlRowView"));
		if (typeof(this.addfileparams.buttonManualUpload) != 'undefined') {
			this.addfileparams.buttonManualUpload.click(jQuery.proxy(this, "toggleManualUploadView"));
		}
		if (typeof(this.addfileparams.buttonEditor) != 'undefined') {
			this.addfileparams.buttonEditor.click(jQuery.proxy(this, "toggleEditorView"));
		}
	},

	toggleFileRowView: function() {
		this.addfileparams.fileRow.show();
		this.addfileparams.fileRow.find('input').attr("required", "required");
		this.addfileparams.urlRow.hide();
		this.addfileparams.urlRow.find('input').removeAttr("required");
		this.addfileparams.pathRow.hide();
		this.addfileparams.pathRow.find('input').removeAttr("required");
		this.addfileparams.editRow.hide();
		this.addfileparams.editNameRow.hide();
	},

	toggleUrlRowView: function() {
		this.addfileparams.fileRow.hide();
		this.addfileparams.fileRow.find('input').removeAttr("required");
		this.addfileparams.urlRow.show();
		this.addfileparams.urlRow.find('input').attr("required", "required");
		this.addfileparams.pathRow.hide();
		this.addfileparams.pathRow.find('input').removeAttr("required");
		this.addfileparams.editRow.hide();
		this.addfileparams.editNameRow.hide();
	},

	toggleManualUploadView: function() {
		this.addfileparams.fileRow.hide();
		this.addfileparams.fileRow.find('input').removeAttr("required");
		this.addfileparams.urlRow.hide();
		this.addfileparams.urlRow.find('input').removeAttr("required");
		this.addfileparams.pathRow.show();
		this.addfileparams.pathRow.find('input').attr("required", "required");
		this.addfileparams.editRow.hide();
		this.addfileparams.editNameRow.hide();
	},

	toggleEditorView: function() {
		this.addfileparams.fileRow.hide();
		this.addfileparams.fileRow.find('input').removeAttr("required");
		this.addfileparams.urlRow.hide();
		this.addfileparams.urlRow.find('input').removeAttr("required");
		this.addfileparams.pathRow.hide();
		this.addfileparams.pathRow.find('input').removeAttr("required");
		this.addfileparams.editRow.show();
		this.addfileparams.editNameRow.show();
	}
};

DocManSearchController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.searchparams.buttonStartDate.click(jQuery.proxy(this, 'setStartDate'));
		this.searchparams.buttonEndDate.click(jQuery.proxy(this, 'setEndDate'));
	},

	setStartDate: function() {
		if (this.searchparams.buttonStartDate.is(':checked')) {
			this.searchparams.datePickerStartDate.removeAttr('disabled');
			this.searchparams.datePickerStartDate.attr('required', 'required');
		} else {
			this.searchparams.datePickerStartDate.attr('disabled', 'disabled');
			this.searchparams.datePickerStartDate.removeAttr('required');
		}
	},

	setEndDate: function() {
		if (this.searchparams.buttonEndDate.is(':checked')) {
			this.searchparams.datePickerEndDate.removeAttr('disabled');
			this.searchparams.datePickerEndDate.attr('required', 'required');
		} else {
			this.searchparams.datePickerEndDate.attr('disabled', 'disabled');
			this.searchparams.datePickerEndDate.removeAttr('required');
		}
	},
};
