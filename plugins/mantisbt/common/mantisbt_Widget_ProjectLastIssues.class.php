<?php
/**
 * Copyright 2011, Franck Villaume - Capgemini
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
require_once('common/widget/Widget.class.php');
require_once('common/widget/WidgetLayoutManager.class.php');

class mantisBT_Widget_ProjectLastIssues extends Widget {
	function mantisBT_Widget_ProjectLastIssues() {
		$this->Widget('plugin_mantisbt_project_latestissues');
	}

	function getTitle() {
		return _("MantisBT title");
	}

	function getCategory() {
		return _('MantisBT');
	}

	function getDescription() {
		return _("MantisBT description.");
	}

	function getContent() {
		return '<div class="warning">'._('Not yet implemented').'</div>';
	}
}

?>
