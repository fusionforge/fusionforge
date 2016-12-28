/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2008. All rights reserved
 * Originally written by Nicolas Terray, 2008
 *
 * Copyright 2013, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

WidgetBuilderController = function(params)
{
	this.params		= params;
	this.bindControls();
};

WidgetBuilderController.prototype =
{
	loadRemoveColumn: function(i, e) {
		jQuery(e).mouseenter(function(){jQuery(this).addClass('layout-manager-column-remove_hover')})
			.mouseleave(function(){jQuery(this).removeClass('layout-manager-column-remove_hover')});
		jQuery(e).click(function(){
				var tr = jQuery(e).parents('tr').first();
				if (jQuery('#layout-manager').find('.layout-manager-column').length == 1) {
					alert('You must keep at least one column in your layout.');
				} else if (tr.find('.layout-manager-column').length == 1) {
					tr.parents('table').first().next().remove();
					tr.parents('table').first().remove();
				} else {
					jQuery(e).parent().next().remove();
					jQuery(e).parent().remove();
					WidgetBuilderController.prototype.distributeWidth(tr);
				}
			});
	},

	loadAddColumn: function(i, e) {
		jQuery(e).mouseenter(function(){jQuery(this).addClass('layout-manager-column-add_hover')})
			.mouseleave(function(){jQuery(this).removeClass('layout-manager-column-add_hover')});
		jQuery(e).click(function(){
				var newCol = jQuery('<td></td>');
				newCol.addClass('layout-manager-column');
				newCol.append('<div>x</div>');
				newCol.droppable({
						drop: function(event, ui) {
							ui.draggable.appendTo(this).css('position', '');
							ui.draggable.parent().droppable('destroy');
							ui.draggable.parent().find('#fake').remove();
							ui.draggable.find('#x'+ui.draggable.attr('id')).show()
						},
						over: function(event, ui) {
							ui.helper.css('z-index', 1);
						},
					});
				newCol.children('div').addClass('layout-manager-column-remove');
				WidgetBuilderController.prototype.loadRemoveColumn(0, newCol.children('div'));
				newCol.append('<div class="layout-manager-column-width"><input type="number" value="" autocomplete="off" size="1" maxlength="3" />%</div>');
				newCol.append('<div id="fake" class="wb_extrafield" />');
				jQuery(e).parent().append(newCol);
				newCol = jQuery('<td>+</td>');
				newCol.addClass('layout-manager-column-add');
				WidgetBuilderController.prototype.loadAddColumn(0, newCol);
				jQuery(e).parent().append(newCol);
				WidgetBuilderController.prototype.distributeWidth(jQuery(e).parent());
			});
	},

	loadRemoveEF: function(i, e) {
		jQuery(e).mouseenter(function(){jQuery(this).addClass('ef-column-remove_hover')})
			.mouseleave(function(){jQuery(this).removeClass('ef-column-remove_hover')});
		jQuery(e).click(function() {
			if (jQuery(this).parent().parent().hasClass('layout-manager-column')) {
				jQuery(this).parent().parent().droppable({
							accept: '#extrafield_table .wb_extrafield',
							drop: function(event, ui) {
								ui.draggable.appendTo(this).css('position', '');
								ui.draggable.parent().droppable('destroy');
								ui.draggable.parent().find('#fake').remove();
								ui.draggable.find('#x'+ui.draggable.attr('id')).show()},
							over: function(event, ui) {
								ui.helper.css('z-index', 1);
							},
						});
				jQuery(this).parent().parent().append('<div id="fake" class="wb_extrafield" />');
			}
			jQuery(this).parent().find('.ef-widget-remove').hide();
			jQuery(this).parent().appendTo(jQuery('#td'+jQuery(this).parent().attr('id')));
			jQuery(this).parent().draggable({
							cursor: "move",
							helper: "clone",
						});
		});
	},

	loadAddRow: function(i, e) {
		jQuery(e).mouseenter(function(){jQuery(this).addClass('layout-manager-row-add_hover')})
			.mouseleave(function(){jQuery(this).removeClass('layout-manager-row-add_hover')});
		jQuery(e).click(function(){
				var newRow = jQuery('<table class="layout-manager-row" cellspacing="5" cellpadding="2"><tr><td>+</td></tr></table>');
				newRow.insertAfter(jQuery(e));
				var newCol = jQuery(e).next().find('td').first();
				newCol.addClass('layout-manager-column-add');
				WidgetBuilderController.prototype.loadAddColumn(0, newCol);
				newRow = jQuery('<div>+</div>');
				newRow.addClass("layout-manager-row-add");
				WidgetBuilderController.prototype.loadAddRow(0, newRow);
				newRow.insertAfter(jQuery(e).next());
			});
	},

	distributeWidth: function(row) {
		var cols = row.find('input[type=number]');
		var width = Math.round(100 / cols.length);
		cols.val(width);
	},

	/*! Binds the controls to the actions
	 */
	bindControls: function() {
		this.params.buttonAddRow.each(jQuery.proxy(this.loadAddRow, this));
		this.params.buttonAddColumn.each(jQuery.proxy(this.loadAddColumn, this));
		this.params.buttonRemoveColumn.each(jQuery.proxy(this.loadRemoveColumn, this));
		if (this.params.buttonRemoveEF != 'undefined') {
			this.params.buttonRemoveEF.each(jQuery.proxy(this.loadRemoveEF, this));
		}
	}
};
