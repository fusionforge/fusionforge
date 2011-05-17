/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
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


DocManListFileController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function()
	{
		this.params.buttonAddDirectory.click(jQuery.proxy(this, "toggleAddDirectoryView"));
		this.params.buttonEditDirectory.click(jQuery.proxy(this, "toggleEditDirectoryView"));
		this.params.buttonAddNewFile.click(jQuery.proxy(this, "toggleAddFileView"));
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

	/*! toggle sub group view div visibility
	 */
	toggleAddDirectoryView: function()
	{
		if (!this.params.divAddDirectory.is(":visible"))
		{
			this.params.divAddDirectory.show();
			this.params.divAddFile.hide();
			this.params.divEditDirectory.hide();
		}
		else
		{
			this.params.divAddDirectory.hide();
		}
	},

	/*! toggle edit group view div visibility
	 */
	toggleEditDirectoryView: function() 
	{
		if (!this.params.divEditDirectory.is(":visible"))
		{
			this.params.divEditDirectory.show();
			this.params.divAddDirectory.hide();
			this.params.divAddFile.hide();
		}
		else
		{
			this.params.divEditDirectory.hide();
		}
	},

	/*! toggle add file view div visibility
	 */
	toggleAddFileView: function()
	{
		if (!this.params.divAddFile.is(":visible"))
		{
			this.params.divAddFile.show();
			this.params.divAddDirectory.hide();
			this.params.divEditDirectory.hide();
		}
		else
		{
			this.params.divAddFile.hide();
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
