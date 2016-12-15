<?php
/**
 * jQuery plugins toolbox
 *
 * Copyright 2016 Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org/
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

function init_datetimepicker() {

	// awful hack
	$language_code = language_name_to_locale_code(choose_language_from_context ());
	if (!in_array($language_code,array('en-GB','pt-BR','sr-YU','zh-TW'))) {
		$language_code = substr($language_code, 0, 2);
	}
	$datetime_format = _('Y-m-d H:i');
	$javascript = "$.datetimepicker.setLocale('".$language_code."'); $('.datetimepicker').datetimepicker({format:'".$datetime_format."'});";
	return html_e('script', array( 'type'=>'text/javascript'), '//<![CDATA['."\n".'$(function(){'.$javascript.'});'."\n".'//]]>');
}
