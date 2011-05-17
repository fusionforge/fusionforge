<?php
/**
 * FusionForge configuration variables
 *
 * Copyright 2010, Roland Mas
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

forge_define_config_item ('forge_name', 'core', $GLOBALS['sys_name']) ;
forge_define_config_item ('force_login', 'core', $GLOBALS['sys_force_login']) ;
forge_set_config_item_bool ('force_login', 'core') ;
forge_define_config_item ('user_registration_restricted', 'core', $GLOBALS['sys_user_reg_restricted']) ;
forge_set_config_item_bool ('user_registration_restricted', 'core') ;
forge_define_config_item ('project_registration_restricted', 'core', $GLOBALS['sys_project_reg_restricted']) ;
forge_set_config_item_bool ('project_registration_restricted', 'core') ;
forge_define_config_item ('project_auto_approval', 'core', false) ;
forge_set_config_item_bool ('project_auto_approval', 'core') ;
forge_define_config_item ('project_auto_approval_user', 'core', 'admin') ;
forge_define_config_item ('allow_project_without_template', 'core', true) ;
forge_set_config_item_bool ('allow_project_without_template', 'core') ;
forge_define_config_item ('web_host', 'core', $GLOBALS['sys_default_domain']) ;
forge_define_config_item ('http_port', 'core', 80) ;
forge_define_config_item ('https_port', 'core', 443) ;
forge_define_config_item ('apache_user', 'core', $GLOBALS['sys_apache_user']) ;
forge_define_config_item ('apache_group', 'core', $GLOBALS['sys_apache_group']) ;
forge_define_config_item ('scm_tarballs_path', 'core', '$core/data_path/scmtarballs') ;
forge_define_config_item ('scm_snapshots_path', 'core', '$core/data_path/scmsnapshots') ;
forge_define_config_item ('plugins_path', 'core', '$core/source_path/plugins') ;
forge_define_config_item ('themes_root', 'core', '$core/source_path/www/themes') ;
forge_define_config_item ('default_theme', 'core', $GLOBALS['sys_theme']) ;
forge_define_config_item ('default_language', 'core', $GLOBALS['sys_lang']) ;
forge_define_config_item ('default_timezone', 'core', $GLOBALS['sys_default_timezone']) ;
forge_define_config_item ('default_country_code', 'core', $GLOBALS['sys_default_country_code']) ;
forge_define_config_item ('use_scm', 'core', $GLOBALS['sys_use_scm']) ;
forge_set_config_item_bool ('use_scm', 'core') ;
forge_define_config_item ('use_dav', 'core', @$GLOBALS['sys_use_dav']) ;
forge_set_config_item_bool ('use_dav', 'core') ;
forge_define_config_item ('use_tracker', 'core', $GLOBALS['sys_use_tracker']) ;
forge_set_config_item_bool ('use_tracker', 'core') ;
forge_define_config_item ('use_forum', 'core', $GLOBALS['sys_use_forum']) ;
forge_set_config_item_bool ('use_forum', 'core') ;
forge_define_config_item ('use_pm', 'core', $GLOBALS['sys_use_pm']) ;
forge_set_config_item_bool ('use_pm', 'core') ;
forge_define_config_item ('use_docman', 'core', $GLOBALS['sys_use_docman']) ;
forge_set_config_item_bool ('use_docman', 'core') ;
forge_define_config_item ('use_diary', 'core', $GLOBALS['sys_use_diary']) ;
forge_set_config_item_bool ('use_diary', 'core') ;
forge_define_config_item ('use_news', 'core', $GLOBALS['sys_use_news']) ;
forge_set_config_item_bool ('use_news', 'core') ;
forge_define_config_item ('use_mail', 'core', $GLOBALS['sys_use_mail']) ;
forge_set_config_item_bool ('use_mail', 'core') ;
forge_define_config_item ('use_survey', 'core', $GLOBALS['sys_use_survey']) ;
forge_set_config_item_bool ('use_survey', 'core') ;
forge_define_config_item ('use_frs', 'core', $GLOBALS['sys_use_frs']) ;
forge_set_config_item_bool ('use_frs', 'core') ;
forge_define_config_item ('use_project_tags', 'core', $GLOBALS['sys_use_project_tags']) ;
forge_set_config_item_bool ('use_project_tags', 'core') ;
forge_define_config_item ('use_project_full_list', 'core', $GLOBALS['sys_use_project_full_list']) ;
forge_set_config_item_bool ('use_project_full_list', 'core') ;
forge_define_config_item ('use_fti', 'core', $GLOBALS['sys_use_fti']) ;
forge_set_config_item_bool ('use_fti', 'core') ;
forge_define_config_item ('use_ftp', 'core', $GLOBALS['sys_use_ftp']) ;
forge_set_config_item_bool ('use_ftp', 'core') ;
forge_define_config_item ('use_trove', 'core', $GLOBALS['sys_use_trove']) ;
forge_set_config_item_bool ('use_trove', 'core') ;
forge_define_config_item ('use_snippet', 'core', $GLOBALS['sys_use_snippet']) ;
forge_set_config_item_bool ('use_snippet', 'core') ;
forge_define_config_item ('use_ssl', 'core', $GLOBALS['sys_use_ssl']) ;
forge_set_config_item_bool ('use_ssl', 'core') ;
forge_define_config_item ('use_people', 'core', $GLOBALS['sys_use_people']) ;
forge_set_config_item_bool ('use_people', 'core') ;
forge_define_config_item ('use_shell', 'core', $GLOBALS['sys_use_shell']) ;
forge_set_config_item_bool ('use_shell', 'core') ;
forge_define_config_item ('use_ratings', 'core', $GLOBALS['sys_use_ratings']) ;
forge_set_config_item_bool ('use_ratings', 'core') ;
forge_define_config_item ('use_ftp_uploads', 'core', $GLOBALS['sys_use_ftpuploads']) ;
forge_set_config_item_bool ('use_ftp_uploads', 'core') ;
forge_define_config_item ('ftp_upload_dir', 'core', $GLOBALS['sys_ftp_upload_dir']) ;
forge_define_config_item ('upload_dir', 'core', $GLOBALS['sys_upload_dir']) ;
forge_define_config_item ('use_manual_uploads', 'core', isset ($GLOBALS['sys_use_manual_uploads']) ? $GLOBALS['sys_use_manual_uploads'] : false) ;
forge_set_config_item_bool ('use_manual_uploads', 'core') ;
forge_define_config_item ('use_gateways', 'core', $GLOBALS['sys_use_gateways']) ;
forge_set_config_item_bool ('use_gateways', 'core') ;
forge_define_config_item ('use_project_vhost', 'core', $GLOBALS['sys_use_project_vhost']) ;
forge_set_config_item_bool ('use_project_vhost', 'core') ;
forge_define_config_item ('use_project_database', 'core', $GLOBALS['sys_use_project_database']) ;
forge_set_config_item_bool ('use_project_database', 'core') ;
forge_define_config_item ('use_project_multimedia', 'core', $GLOBALS['sys_use_project_multimedia']) ;
forge_set_config_item_bool ('use_project_multimedia', 'core') ;
forge_define_config_item ('download_host', 'core', $GLOBALS['sys_download_host']) ;
forge_define_config_item ('shell_host', 'core', $GLOBALS['sys_shell_host']) ;
forge_define_config_item ('users_host', 'core', $GLOBALS['sys_users_host']) ;
forge_define_config_item ('lists_host', 'core', $GLOBALS['sys_lists_host']) ;
forge_define_config_item ('scm_host', 'core', $GLOBALS['sys_scm_host']) ;
forge_define_config_item ('forum_return_domain', 'core', $GLOBALS['sys_forum_return_domain']) ;
forge_define_config_item ('use_jabber', 'core', $GLOBALS['sys_use_jabber']) ;
forge_set_config_item_bool ('use_jabber', 'core') ;
forge_define_config_item ('jabber_host', 'core', $GLOBALS['sys_jabber_server']) ;
forge_define_config_item ('jabber_port', 'core', $GLOBALS['sys_jabber_port']) ;
forge_define_config_item ('jabber_user', 'core', $GLOBALS['sys_jabber_user']) ;
forge_define_config_item ('jabber_password', 'core', isset($GLOBALS['sys_jabber_pass']) ? $GLOBALS['sys_jabber_pass'] : '') ;
forge_define_config_item ('ldap_host', 'core', $GLOBALS['sys_ldap_host']) ;
forge_define_config_item ('ldap_port', 'core', $GLOBALS['sys_ldap_port']) ;
forge_define_config_item ('ldap_version', 'core', $GLOBALS['sys_ldap_version']) ;
forge_define_config_item ('ldap_base_dn', 'core', $GLOBALS['sys_ldap_base_dn']) ;
forge_define_config_item ('ldap_bind_dn', 'core', $GLOBALS['sys_ldap_bind_dn']) ;
forge_define_config_item ('ldap_admin_dn', 'core', $GLOBALS['sys_ldap_admin_dn']) ;
forge_define_config_item ('ldap_password', 'core', isset($GLOBALS['sys_ldap_passwd']) ? $GLOBALS['sys_ldap_passwd'] : '') ;
forge_define_config_item ('news_group', 'core', $GLOBALS['sys_news_group']) ;
forge_define_config_item ('stats_group', 'core', $GLOBALS['sys_stats_group']) ;
forge_define_config_item ('peer_rating_group', 'core', $GLOBALS['sys_peer_rating_group']) ;
forge_define_config_item ('template_group', 'core', $GLOBALS['sys_template_group']) ;
forge_define_config_item ('sendmail_path', 'core', $GLOBALS['sys_sendmail_path']) ;
forge_define_config_item ('mailman_path', 'core', $GLOBALS['sys_path_to_mailman']) ;
forge_define_config_item ('jpgraph_path', 'core', $GLOBALS['sys_path_to_jpgraph']) ;
forge_define_config_item ('account_manager_type', 'core', $GLOBALS['sys_account_manager_type']) ;
forge_define_config_item ('unix_cipher', 'core', $GLOBALS['unix_cipher']) ;
forge_define_config_item ('homedir_prefix', 'core', $GLOBALS['homedir_prefix']) ;
forge_define_config_item ('groupdir_prefix', 'core', $GLOBALS['groupdir_prefix']) ;
forge_define_config_item ('url_root', 'core', $GLOBALS['sys_urlroot']) ;
forge_define_config_item ('url_prefix', 'core', $GLOBALS['sys_urlprefix']) ;
forge_define_config_item ('images_url', 'core', $GLOBALS['sys_images_url']) ;
forge_define_config_item ('images_secure_url', 'core', $GLOBALS['sys_images_secure_url']) ;
forge_define_config_item ('admin_email', 'core', $GLOBALS['sys_admin_email']) ;
forge_define_config_item ('session_key', 'core', $GLOBALS['sys_session_key']) ;
forge_define_config_item ('session_expire', 'core', $GLOBALS['sys_session_expire']) ;
forge_define_config_item ('show_source', 'core', $GLOBALS['sys_show_source']) ;
forge_set_config_item_bool ('show_source', 'core') ;
forge_define_config_item ('default_trove_cat', 'core', $GLOBALS['default_trove_cat']) ;
forge_define_config_item ('user_registration_accept_conditions', 'core', $GLOBALS['sys_require_accept_conditions']);
forge_set_config_item_bool ('user_registration_accept_conditions', 'core') ;

// Arch plugin
if (file_exists ($gfconfig.'plugins/scmarch/config.php')) {
	require_once $gfconfig.'plugins/scmarch/config.php' ;
	
	forge_define_config_item ('default_server', 'scmarch', $default_arch_server) ;
	if (isset ($arch_root)) {
		forge_define_config_item ('repos_path', 'scmarch', $arch_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmarch',
					  forge_get_config('chroot').'/scmrepos/arch') ;
	}
}

// Bazaar plugin
if (file_exists ($gfconfig.'plugins/scmbzr/config.php')) {
	require_once $gfconfig.'plugins/scmbzr/config.php' ;
	
	forge_define_config_item ('default_server', 'scmbzr', $default_bzr_server) ;
	if (isset ($bzr_root)) {
		forge_define_config_item ('repos_path', 'scmbzr', $bzr_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmbzr',
					  forge_get_config('chroot').'/scmrepos/bzr') ;
	}
}

// ClearCase plugin
if (file_exists ($gfconfig.'plugins/scmccase/config.php')) {
	require_once $gfconfig.'plugins/scmccase/config.php' ;
	forge_define_config_item ('default_server', 'scmccase', $default_ccase_server) ;
	forge_define_config_item ('this_server', 'scmccase', $this_server) ;
	forge_define_config_item ('tag_pattern', 'scmccase', $tag_pattern) ;
}

// CVS plugin
if (file_exists ($gfconfig.'plugins/scmcvs/config.php')) {
	require_once $gfconfig.'plugins/scmcvs/config.php' ;
	
	forge_define_config_item ('default_server', 'scmcvs', $default_cvs_server) ;
	if (isset ($cvs_root)) {
		forge_define_config_item ('repos_path', 'scmcvs', $cvs_root) ;
	} elseif (isset ($cvsdir_prefix)) {
		forge_define_config_item ('repos_path', 'scmcvs', $cvsdir_prefix) ;
	} else {
		forge_define_config_item ('repos_path', 'scmcvs',
					  forge_get_config('chroot').'/scmrepos/cvs') ;
	}
}

// Darcs plugin
if (file_exists ($gfconfig.'plugins/scmdarcs/config.php')) {
	require_once $gfconfig.'plugins/scmdarcs/config.php' ;
	
	forge_define_config_item ('default_server', 'scmdarcs', $default_darcs_server) ;
	if (isset ($darcs_root)) {
		forge_define_config_item ('repos_path', 'scmdarcs', $darcs_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmdarcs',
					  forge_get_config('chroot').'/scmrepos/darcs') ;
	}
}

// Git plugin
if (file_exists ($gfconfig.'plugins/scmgit/config.php')) {
	require_once $gfconfig.'plugins/scmgit/config.php' ;
	
	forge_define_config_item ('default_server', 'scmgit', $default_git_server) ;
	if (isset ($git_root)) {
		forge_define_config_item ('repos_path', 'scmgit', $git_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmgit',
					  forge_get_config('chroot').'/scmrepos/git') ;
	}
}

// Mercurial plugin
if (file_exists ($gfconfig.'plugins/scmhg/config.php')) {
	require_once $gfconfig.'plugins/scmhg/config.php' ;
	
	forge_define_config_item ('default_server', 'scmhg', $default_hg_server) ;
	if (isset ($hg_root)) {
		forge_define_config_item ('repos_path', 'scmhg', $hg_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmhg',
					  forge_get_config('chroot').'/scmrepos/hg') ;
	}
}

// Subversion plugin
if (file_exists ($gfconfig.'plugins/scmsvn/config.php')) {
	require_once $gfconfig.'plugins/scmsvn/config.php' ;
	
	forge_define_config_item ('default_server', 'scmsvn', $default_svn_server) ;
	if (isset ($svn_root)) {
		forge_define_config_item ('repos_path', 'scmsvn', $svn_root) ;
	} else {
		forge_define_config_item ('repos_path', 'scmsvn',
					  forge_get_config('chroot').'/scmrepos/svn') ;
	}

	forge_define_config_item ('use_ssh', 'scmsvn', $use_ssh ? 1 : 0) ;
	forge_set_config_item_bool ('use_ssh', 'scmsvn') ;
	forge_define_config_item ('use_dav', 'scmsvn', $use_dav ? 1 : 0) ;
	forge_set_config_item_bool ('use_dav', 'scmsvn') ;
	forge_define_config_item ('use_ssl', 'scmsvn', $use_ssl ? 1 : 0) ;
	forge_set_config_item_bool ('use_ssl', 'scmsvn') ;
}

// Mediawiki plugin
if (isset ($sys_use_mwframe)) {
	forge_define_config_item ('use_frame', 'mediawiki', $sys_use_mwframe ? 1 : 0) ;
	forge_set_config_item_bool ('use_frame', 'mediawiki') ;
}

// Mantis plugin
if (file_exists ($gfconfig.'plugins/mantis/config.php')) {
	require_once $gfconfig.'plugins/mantis/config.php' ;
	
	forge_define_config_item ('server', 'mantis', $serveur_mantis) ;
	forge_define_config_item ('db_host', 'mantis', $mantis_db_host) ;
	forge_define_config_item ('db_user', 'mantis', $mantis_db_user) ;
	forge_define_config_item ('db_passwd', 'mantis', $mantis_db_passwd) ;
}

// forge_define_config_item ('', 'core', $GLOBALS['sys_']) ;

/* Long-term:
 require_once $gfcommon.'include/FusionForge.class.php';
 $forge = new FusionForge() ;
 forge_define_config_item ('forge_name', 'core', $forge->software_name) ;
 forge_define_config_item ('user_registration_restricted', 'core', false) ;
*/

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
