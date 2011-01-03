# PLEASE DO NOT REMOVE THIS LINE

$fgcpath = '{usr_share_gforge}/bin' ;

$sys_default_domain = qx!$fgcpath/forge_get_config web_host! ;
chomp $sys_default_domain ;
$sys_scm_host = $sys_default_domain ;
$domain_name = $sys_default_domain;
$sys_users_host = qx!$fgcpath/forge_get_config users_host!;
chomp $sys_users_host ;
$sys_lists_host = qx!$fgcpath/forge_get_config lists_host!;
chomp $sys_lists_host ;
$sys_name = qx!$fgcpath/forge_get_config forge_name!;
chomp $sys_name ;
$sys_themeroot = qx!$fgcpath/forge_get_config themes_root!;
chomp $sys_themeroot ;
$sys_news_group = qx!$fgcpath/forge_get_config news_group!;
chomp $sys_news_group ;
$sys_dbhost = qx!$fgcpath/forge_get_config database_host!;
chomp $sys_dbhost ;
$sys_dbport = qx!$fgcpath/forge_get_config database_port!;
chomp $sys_dbport ;
$sys_dbname = qx!$fgcpath/forge_get_config database_name!;
chomp $sys_dbname ;
$sys_dbuser = qx!$fgcpath/forge_get_config database_user!;
chomp $sys_dbuser ;
$sys_dbpasswd = qx!$fgcpath/forge_get_config database_password!;
chomp $sys_dbpasswd ;
$sys_ldap_base_dn = qx!$fgcpath/forge_get_config ldab_base_dn!;
chomp $sys_ldap_base_dn ;
$sys_ldap_host = qx!$fgcpath/forge_get_config ldap_host!;
chomp $sys_ldap_host ;
$server_admin = qx!$fgcpath/forge_get_config admin_email!;
$peerrating_groupid = qx!$fgcpath/forge_get_config peer_rating_group!;
chomp $peerrating_groupid ;
$noreply_to_bitbucket = '{noreply_to_bitbucket}' ;
$sys_ip_address = '{ip_address}';
$chroot_prefix = qx!$fgcpath/forge_get_config chroot!;
chomp $chroot_prefix ;
$homedir_prefix = qx!$fgcpath/forge_get_config homedir_prefix!;
chomp $homedir_prefix ;
$grpdir_prefix = qx!$fgcpath/forge_get_config groupdir_prefix!;
chomp $grpdir_prefix ;
$file_dir = qx!$fgcpath/forge_get_config data_path!;
chomp $file_dir ;

1 ;
