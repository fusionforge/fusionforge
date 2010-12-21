#! /bin/sh

set -e

config_dir=$(forge_get_config extra_config_dirs | xargs -n 1 echo | head -1)
if [ "$config_dir" = "" ] ; then
    config_path=$(forge_get_config config_path)/config.ini.d
else
    config_path=$config_dir
fi

[ -e $config_path ] || mkdir -p $config_path

add_config () {
    section=$1
    var=$2

    value=$(forge_get_config $var $section)
    if [ "$value" = '' ] ; then
	return
    fi

    if [ "$section" != "$lastsection" ] ; then
	echo
	echo "[$section]"
	lastsection=$section
    fi

    if echo $value | grep -q [^[:alnum:]] ; then
	echo $var = \"$value\"
    else
	echo $var = $value
    fi
}

lastsection=''
tmp=$(mktemp)
cat > $tmp <<EOF
# This is a generated file with values migrated from your previous configuration
# You may want to move the values in here to the main configuration files
EOF

add_config core account_manager_type >> $tmp
add_config core admin_email >> $tmp
add_config core apache_group >> $tmp
add_config core apache_user >> $tmp
add_config core bcc_all_emails >> $tmp
add_config core chroot >> $tmp
add_config core custom_path >> $tmp
add_config core default_country_code >> $tmp
add_config core default_language >> $tmp
add_config core default_theme >> $tmp
add_config core default_timezone >> $tmp
add_config core default_trove_cat >> $tmp
add_config core extra_config_dirs >> $tmp
add_config core extra_config_files >> $tmp
add_config core force_login >> $tmp
add_config core forge_name >> $tmp
add_config core forum_return_domain >> $tmp
add_config core ftp_upload_dir >> $tmp
add_config core ftp_upload_host >> $tmp
add_config core groupdir_prefix >> $tmp
add_config core homedir_prefix >> $tmp
add_config core images_url >> $tmp
add_config core jpgraph_path >> $tmp
add_config core lists_host >> $tmp
add_config core mailman_path >> $tmp
add_config core master_path >> $tmp
add_config core news_group >> $tmp
add_config core peer_rating_group >> $tmp
add_config core plugins_path >> $tmp
add_config core project_registration_restricted >> $tmp
add_config core projects_path >> $tmp
add_config core require_unique_email >> $tmp
add_config core scm_host >> $tmp
add_config core scm_snapshots_path >> $tmp
add_config core scm_tarballs_path >> $tmp
add_config core sendmail_path >> $tmp
add_config core show_source >> $tmp
add_config core src_path >> $tmp
add_config core stats_group >> $tmp
add_config core sys_proxy >> $tmp
add_config core template_group >> $tmp
add_config core themes_root >> $tmp
add_config core unix_cipher >> $tmp
add_config core upload_dir >> $tmp
add_config core url_prefix >> $tmp
add_config core url_root >> $tmp
add_config core use_docman >> $tmp
add_config core use_forum >> $tmp
add_config core use_frs >> $tmp
add_config core use_fti >> $tmp
add_config core use_ftp >> $tmp
add_config core use_ftp_uploads >> $tmp
add_config core use_gateways >> $tmp
add_config core use_jabber >> $tmp
add_config core use_mail >> $tmp
add_config core use_manual_uploads >> $tmp
add_config core use_news >> $tmp
add_config core use_people >> $tmp
add_config core use_pm >> $tmp
add_config core use_project_database >> $tmp
add_config core use_project_multimedia >> $tmp
add_config core use_project_vhost >> $tmp
add_config core use_ratings >> $tmp
add_config core user_registration_restricted >> $tmp
add_config core users_host >> $tmp
add_config core use_scm >> $tmp
add_config core use_shell >> $tmp
add_config core use_snippet >> $tmp
add_config core use_ssl >> $tmp
add_config core use_survey >> $tmp
add_config core use_tracker >> $tmp
add_config core use_trove >> $tmp
add_config core web_host >> $tmp

add_config core source_path >> $tmp
add_config core data_path >> $tmp
add_config core config_path >> $tmp

add_config scmarch default_server >> $tmp
add_config scmarch repos_path >> $tmp

add_config scmbzr default_server >> $tmp
add_config scmbzr repos_path >> $tmp
    
add_config scmccase default_server >> $tmp
add_config scmccase this_server >> $tmp
add_config scmccase tag_pattern >> $tmp
    
add_config scmcvs default_server >> $tmp
add_config scmcvs repos_path >> $tmp
    
add_config scmgit default_server >> $tmp
add_config scmgit repos_path >> $tmp
    
add_config scmhg default_server >> $tmp
add_config scmhg repos_path >> $tmp
    
add_config scmsvn default_server >> $tmp
add_config scmsvn repos_path >> $tmp
add_config scmsvn use_dav >> $tmp
add_config scmsvn use_ssh >> $tmp
add_config scmsvn use_ssl >> $tmp

add_config mediawiki enable_uploads >> $tmp

add_config mantis server >> $tmp

mv $tmp $config_path/zzz-migrated-old-config
chmod 644 $config_path/zzz-migrated-old-config

lastsection=''
tmp=$(mktemp)
cat > $tmp <<EOF
# This is a generated file with values migrated from your previous configuration
# You may want to move the values in here to the main configuration files
EOF

add_config core database_host >> $tmp
add_config core database_name >> $tmp
add_config core database_password >> $tmp
add_config core database_port >> $tmp
add_config core database_user >> $tmp
add_config core jabber_host >> $tmp
add_config core jabber_password >> $tmp
add_config core jabber_port >> $tmp
add_config core jabber_user >> $tmp
add_config core ldap_base_dn >> $tmp
add_config core ldap_host >> $tmp
add_config core ldap_password >> $tmp
add_config core ldap_port >> $tmp
add_config core ldap_version >> $tmp
add_config core session_key >> $tmp

add_config mantis db_name >> $tmp
add_config mantis db_host >> $tmp
add_config mantis db_user >> $tmp
add_config mantis db_passwd >> $tmp

mv $tmp $config_path/zzz-migrated-old-secrets
chmod 600 $config_path/zzz-migrated-old-secrets
