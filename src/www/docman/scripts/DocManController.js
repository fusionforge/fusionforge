/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

/*! ListFileController
 * @param groupId the current FusionForge groupID
 * @param tipsyElements [{selector: "name", options:{delayIn: 1000, delayOut: 1000, fade: true, gravity: 's'}}]
 */
DocManListFileController = function(params)
{
	this.lockInterval	= [];
	this.params		= params;

	if ( typeof(jQuery(window).tipsy) == 'function') {
		this.initTipsy();
	}
	this.bindControls();
};

DocManAddItemController = function(params)
{
	this.params		= params;

	if ( typeof(jQuery(window).tipsy) == 'function') {
		this.initTipsy();
	}
	this.bindControls();
};

DocManMenuController = function(params)
{
	this.params		= params;

	if ( typeof(jQuery(window).tipsy) == 'function') {
		this.initTipsy();
	}
};

DocManListFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function()
	{
		this.params.buttonEditDirectory.click(jQuery.proxy(this, "toggleEditDirectoryView"));
		this.params.buttonAddItem.click(jQuery.proxy(this, "toggleAddItemView"));
	},

	/*! initializes tipsy
	 */
	initTipsy: function()
	{
		for(var i = 0; i < this.params.tipsyElements.length; i++)
		{
			var el = this.params.tipsyElements[i];

			jQuery(el.selector).tipsy({
				gravity: el.options.gravity,
				delayIn: el.options.delayIn,
				delayOut: el.options.delayOut,
				fade: el.options.fade});
		}
	},

	/*! toggle edit group view div visibility
	 */
	toggleEditDirectoryView: function() 
	{
		if (!this.params.divEditDirectory.is(":visible"))
		{
			this.params.divEditDirectory.show();
			this.params.divAddItem.hide();
		}
		else
		{
			this.params.divEditDirectory.hide();
		}
	},

	/*! toggle add item view div visibility
	 */
	toggleAddItemView: function()
	{
		if (!this.params.divAddItem.is(":visible"))
		{
			this.params.divAddItem.show();
			this.params.divEditDirectory.hide();
		}
		else
		{
			this.params.divAddItem.hide();
		}
	},

	/*! toggle add file edit view div visibility and play with lock
	 *
	 * @param	string	id of the div
	 */
	toggleEditFileView: function(id)
	{
		var divid	= '#editfile'+id,
		el		= jQuery(divid);

		if (!el.is(":visible"))
		{
			el.show();

			jQuery.get(this.params.docManURL, {
				group_id:	this.params.groupId,
				action:		'lockfile',
				lock:		1,
				fileid:		id
			});

			this.lockInterval[id] = setInterval("jQuery.get('" + this.params.docManURL + "', {group_id:"+this.params.groupId+",action:'lockfile',lock:1,fileid:"+id+"})",this.params.lockIntervalDelay);
		}
		else
		{
			el.hide();
			jQuery.get(this.params.docManURL, {
				group_id:	this.params.groupId,
				action:		'lockfile',
				lock:		0,
				fileid:		id
			});

			clearInterval(this.lockInterval[id]);
		}
	},

	/*! build list of id, comma separated
	 */
	buildUrlByCheckbox: function()
	{
		var CheckedBoxes = new Array();
		for (var h = 0; h < jQuery("input:checked").length; h++) {
			if (jQuery("input:checked")[h].id == 'checkeddocid' ) {
				CheckedBoxes.push(jQuery("input:checked")[h].value);
			}
		}
		return CheckedBoxes;
	},

	checkAll: function()
	{
		if (jQuery('#checkall').is(':checked')) {
			jQuery('.checkeddocid').each(function() {
				jQuery(this).attr('checked',true);
				});
		} else {
			jQuery('.checkeddocid').each(function() {
				jQuery(this).attr('checked',false);
			});
		}
	},

	checkgeneral: function()
	{
		if (jQuery(this).attr('checked',false)) {
			jQuery('#checkall').attr('checked',false);
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
	bindControls: function()
	{
		this.params.buttonDoc.click(jQuery.proxy(this, "toggleAddFileView"));
		this.params.buttonDir.click(jQuery.proxy(this, "toggleAddDirectoryView"));
		this.params.buttonZip.click(jQuery.proxy(this, "toggleInjectZipView"));
	},

	toggleAddDirectoryView: function()
	{
		if (!this.params.divCreateDir.is(":visible"))
		{
			this.params.divCreateDir.show();
			this.params.divCreateDoc.hide();
			this.params.divZipInject.hide();
		}
		else
		{
			this.params.divCreateDoc.hide();
			this.params.divZipInject.hide();
		}
	},

	toggleInjectZipView: function()
	{
		if (!this.params.divZipInject.is(":visible"))
		{
			this.params.divZipInject.show();
			this.params.divCreateDir.hide();
			this.params.divCreateDoc.hide();
		}
		else
		{
			this.params.divCreateDir.hide();
			this.params.divCreateDoc.hide();
		}
	},

	toggleAddFileView: function()
	{
		if (!this.params.divCreateDoc.is(":visible"))
		{
			this.params.divCreateDoc.show();
			this.params.divCreateDir.hide();
			this.params.divZipInject.hide();
		}
		else
		{
			this.params.divCreateDir.hide();
			this.params.divZipInject.hide();
		}
	},

	/*! initializes tipsy
	 */
	initTipsy: function()
	{
		for(var i = 0; i < this.params.tipsyElements.length; i++)
		{
			var el = this.params.tipsyElements[i];

			jQuery(el.selector).tipsy({
				gravity: el.options.gravity,
				delayIn: el.options.delayIn,
				delayOut: el.options.delayOut,
				fade: el.options.fade});
		}
	}
}

DocManMenuController.prototype =
{
	/*! initializes tipsy
	*/
	initTipsy: function()
	{
		for(var i = 0; i < this.params.tipsyElements.length; i++)
		{
			var el = this.params.tipsyElements[i];

			jQuery(el.selector).tipsy({
				gravity: el.options.gravity,
				delayIn: el.options.delayIn,
				delayOut: el.options.delayOut,
				fade: el.options.fade});
		}
	}
}