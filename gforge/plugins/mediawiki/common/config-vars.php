<?php
/**
 * Mediawiki plugin configuration variables
 *
 * Copyright 2010, Olaf Lenz
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */
forge_define_config_item('src_path','mediawiki', "/usr/share/mediawiki");

forge_define_config_item('var_path','mediawiki', forge_get_config('data_path')."/plugins/mediawiki");
forge_define_config_item('master_path', 'mediawiki', forge_get_config('source_path')."/plugins/mediawiki/www");
forge_define_config_item('projects_path', 'mediawiki', forge_get_config('data_path')."/plugins/mediawiki/projects");

forge_define_config_item('enable_uploads', 'mediawiki', false);
forge_set_config_item_bool('enable_uploads', 'mediawiki');
