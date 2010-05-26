#! /bin/sh

set -e

variable_set=$1
dest_file=$2

case $variable_set in
    public)
	vars="
account_manager_type
admin_email
apache_group
apache_user
bcc_all_emails
chroot
custom_path
default_country_code
default_language
default_theme
default_timezone
default_trove_cat
enable_uploads
extra_config_dirs
extra_config_files
force_login
forge_name
forum_return_domain
ftp_upload_dir
ftp_upload_host
groupdir_prefix
homedir_prefix
images_url
jpgraph_path
lists_host
mailman_path
master_path
news_group
peer_rating_group
plugins_path
project_registration_restricted
projects_path
require_unique_email
scm_host
scm_snapshots_path
scm_tarballs_path
sendmail_path
show_source
src_path
stats_group
sys_proxy
template_group
themes_root
unix_cipher
upload_dir
url_prefix
url_root
use_docman
use_forum
use_frs
use_fti
use_ftp
use_ftpuploads
use_ftp_uploads
use_gateways
use_jabber
use_mail
use_manual_uploads
use_news
use_people
use_pm
use_project_database
use_project_multimedia
use_project_vhost
use_ratings
user_registration_restricted
users_host
use_scm
use_shell
use_snippet
use_ssl
use_survey
use_tracker
use_trove
web_host"
	;;
    secret)
	vars="
database_host
database_name
database_password
database_port
database_user
jabber_host
jabber_password
jabber_port
jabber_user
ldap_base_dn
ldap_host
ldap_password
ldap_port
ldap_version
session_key"
	;;
    *)
	echo "Unknown or missing variable set"
	exit 1
	;;
esac

tmp=$(mktemp)
cat > $tmp <<EOF
# This is a generated file
# You may want to move the values in here to the main configuration files

[core]
EOF
for v in $vars ; do
    echo -n "$v = " ; forge_get_config $v
done >> $tmp

mv $tmp $dest_file
