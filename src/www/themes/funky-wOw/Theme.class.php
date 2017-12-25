<?php
/**
 * FusionForge Funky wOw Theme
 *
 * Copyright 2010, Antoine Mercadal - Capgemini
 * Copyright 2010, Marc-Etienne Vargenau, Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011-2017, Franck Villaume - TrivialDev
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once forge_get_config('themes_root').'/funky/Theme.class.php';

class Theme_Funky_Wow extends Theme_Funky {
	function __construct() {
		parent::__construct();
		$this->themeurl = util_make_uri('themes/funky-wOw/');
		$this->imgbaseurl = $this->themeurl . 'images/';
		$this->css = array();
		$this->css_min = array();
		$this->stylesheets = array();
		$this->addStylesheet('/themes/css/fusionforge.css');
		$this->addStylesheet('/themes/funky-wOw/css/theme.css');
		$this->addStylesheet('/themes/funky-wOw/css/theme-pages.css');
		$this->addStylesheet('/scripts/jquery-ui/css/sunny/jquery-ui-1.12.4.css');
		$this->addStylesheet('/scripts/jquery-ui/css/sunny/jquery-ui.structure-1.12.4.css');
		$this->addStylesheet('/scripts/jquery-ui/css/sunny/jquery-ui.theme-1.12.4.css');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
