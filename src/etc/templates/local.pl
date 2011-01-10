# PLEASE DO NOT REMOVE THIS LINE

$fgcpath = '{usr_share_gforge}/bin' ;
%forge_config_cache = ();

sub forge_get_config ($$) {
    my $var = shift;
    my $sec = shift || 'core';

    if (!defined $forge_config_cache{$sec}{$var}) {
	$forge_config_cache{$sec}{$var} = qx!$fgcpath/forge_get_config $var $sec!;
	chomp $forge_config_cache{$sec}{$var};
    }
    return $forge_config_cache{$sec}{$var};
}

$sys_default_domain = &forge_get_config ('web_host') ;
$sys_scm_host = &forge_get_config ('web_host') ;
$domain_name = &forge_get_config ('web_host') ;
$sys_users_host = &forge_get_config ('users_host') ;
$sys_lists_host = &forge_get_config ('lists_host') ;
$sys_name = &forge_get_config ('forge_name') ;
$sys_themeroot = &forge_get_config ('themes_root') ;
$sys_news_group = &forge_get_config ('news_group') ;
$sys_dbhost = &forge_get_config ('database_host') ;
$sys_dbport = &forge_get_config ('database_port') ;
$sys_dbname = &forge_get_config ('database_name') ;
$sys_dbuser = &forge_get_config ('database_user') ;
$sys_dbpasswd = &forge_get_config ('database_password') ;
$sys_ldap_base_dn = &forge_get_config ('ldab_base_dn') ;
$sys_ldap_host = &forge_get_config ('ldap_host') ;
$server_admin = &forge_get_config ('admin_email') ;
$peerrating_groupid = &forge_get_config ('peer_rating_group') ;
$chroot_prefix = &forge_get_config ('chroot') ;
$homedir_prefix = &forge_get_config ('homedir_prefix') ;
$grpdir_prefix = &forge_get_config ('groupdir_prefix') ;
$file_dir = &forge_get_config ('data_path') ;

$noreply_to_bitbucket = '{noreply_to_bitbucket}' ;
$sys_ip_address = '{ip_address}';

1 ;
