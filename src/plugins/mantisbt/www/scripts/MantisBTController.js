/**
 * MantisBT Plugin
 *
 * Copyright 2011, Franck Villaume - Capgemini
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

/*! MantisBTInitController
 * @param groupId the current FusionForge groupID
 * @param tipsyElements [{selector: "name", options:{delayIn: 1000, delayOut: 1000, fade: true, gravity: 's'}}]
 */
MantisBTInitController = function(params)
{
	this.params	= params;

	if ( typeof(jQuery(window).tipsy) == 'function') {
		this.initTipsy();
	}
	this.bindControls();
};

MantisBTInitUserController = function(params)
{
	this.params	= params;

	if ( typeof(jQuery(window).tipsy) == 'function') {
		this.initTipsy();
	}
};

MantisBTInitController.prototype =
{
	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.params.checkboxCreate.click(jQuery.proxy(this, "toggleInputName"));
	},

	/*! initializes tipsy
	 */
	initTipsy: function() {
		for(var i = 0; i < this.params.tipsyElements.length; i++) {
			var el = this.params.tipsyElements[i];
			jQuery(el.selector).tipsy({
				gravity: el.options.gravity,
				delayIn: el.options.delayIn,
				delayOut: el.options.delayOut,
				fade: el.options.fade});
		}
	},

	/*! disable name input
	 */
	toggleInputName: function() {
		if (!this.params.checkboxCreate.is(":checked")) {
			this.params.inputName.attr('disabled',false);
		} else {
			this.params.inputName.attr('disabled',true);
		}
	},
}

MantisBTInitUserController.prototype =
{
	/*! initializes tipsy
	*/
	initTipsy: function() {
		for(var i = 0; i < this.params.tipsyElements.length; i++) {
			var el = this.params.tipsyElements[i];
			jQuery(el.selector).tipsy({
				gravity: el.options.gravity,
						  delayIn: el.options.delayIn,
						  delayOut: el.options.delayOut,
						  fade: el.options.fade});
		}
	},
}