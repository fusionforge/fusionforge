<?php
/**
 * FusionForge configuration variables
 *
 * Copyright 2010, Roland Mas
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

forge_define_config_item ('forge_name', 'core', $GLOBALS['sys_name']) ;
forge_define_config_item ('user_registration_restricted', 'core', $GLOBALS['sys_user_reg_restricted']) ;
forge_define_config_item ('web_host', 'core', $GLOBALS['sys_default_domain']) ;
forge_define_config_item ('scm_tarballs_path', 'core', $GLOBALS['sys_scm_tarballs_path']) ;
forge_define_config_item ('scm_snapshots_path', 'core', $GLOBALS['sys_scm_snapshots_path']) ;
forge_define_config_item ('themes_root', 'core', $GLOBALS['sys_themeroot']) ;
forge_define_config_item ('default_theme', 'core', $GLOBALS['sys_theme']) ;
forge_define_config_item ('default_language', 'core', $GLOBALS['sys_lang']) ;
forge_define_config_item ('default_timezone', 'core', $GLOBALS['sys_default_timezone']) ;
forge_define_config_item ('default_country_code', 'core', $GLOBALS['sys_default_country_code']) ;
forge_define_config_item ('use_scm', 'core', $GLOBALS['sys_use_scm']) ;
forge_define_config_item ('use_tracker', 'core', $GLOBALS['sys_use_tracker']) ;
forge_define_config_item ('use_forum', 'core', $GLOBALS['sys_use_forum']) ;
forge_define_config_item ('use_pm', 'core', $GLOBALS['sys_use_pm']) ;
forge_define_config_item ('use_docman', 'core', $GLOBALS['sys_use_docman']) ;
forge_define_config_item ('use_news', 'core', $GLOBALS['sys_use_news']) ;
forge_define_config_item ('use_mail', 'core', $GLOBALS['sys_use_mail']) ;
forge_define_config_item ('use_survey', 'core', $GLOBALS['sys_use_survey']) ;
forge_define_config_item ('use_frs', 'core', $GLOBALS['sys_use_frs']) ;
forge_define_config_item ('use_fti', 'core', $GLOBALS['sys_use_fti']) ;
forge_define_config_item ('use_ftp', 'core', $GLOBALS['sys_use_ftp']) ;
forge_define_config_item ('use_trove', 'core', $GLOBALS['sys_use_trove']) ;
forge_define_config_item ('use_snippet', 'core', $GLOBALS['sys_use_snippet']) ;
forge_define_config_item ('use_ssl', 'core', $GLOBALS['sys_use_ssl']) ;
forge_define_config_item ('use_people', 'core', $GLOBALS['sys_use_people']) ;
forge_define_config_item ('use_shell', 'core', $GLOBALS['sys_use_shell']) ;
forge_define_config_item ('use_ratings', 'core', $GLOBALS['sys_use_ratings']) ;
forge_define_config_item ('use_ftpuploads', 'core', $GLOBALS['sys_use_ftpuploads']) ;
forge_define_config_item ('use_gateways', 'core', $GLOBALS['sys_use_gateways']) ;
forge_define_config_item ('use_project_vhost', 'core', $GLOBALS['sys_use_project_vhost']) ;
forge_define_config_item ('use_project_database', 'core', $GLOBALS['sys_use_project_database']) ;
forge_define_config_item ('use_project_multimedia', 'core', $GLOBALS['sys_use_project_multimedia']) ;
forge_define_config_item ('download_host', 'core', $GLOBALS['sys_download_host']) ;
forge_define_config_item ('shell_host', 'core', $GLOBALS['sys_shell_host']) ;
forge_define_config_item ('users_host', 'core', $GLOBALS['sys_users_host']) ;
forge_define_config_item ('lists_host', 'core', $GLOBALS['sys_lists_host']) ;
forge_define_config_item ('scm_host', 'core', $GLOBALS['sys_scm_host']) ;
forge_define_config_item ('forum_return_domain', 'core', $GLOBALS['sys_forum_return_domain']) ;
// forge_define_config_item ('', 'core', $GLOBALS['sys_']) ;

/* Long-term:
 require_once $gfcommon.'include/FusionForge.class.php';
 $forge = new FusionForge() ;
 forge_define_config_item ('forge_name', 'core', $forge->software_name) ;
 forge_define_config_item ('user_registration_restricted', 'core', false) ;
*/

?>
