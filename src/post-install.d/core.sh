#!/bin/bash -x
source_path=$(forge_get_config source_path)
data_path=$(forge_get_config data_path)

user=gforge  # FIXME: make this a configuration option, or ditch the user
if ! getent passwd $user >/dev/null; then useradd $user -s /bin/false -d $data_path; fi
$source_path/post-install.d/db-configure.sh
$source_path/post-install.d/db-populate.sh
$source_path/bin/upgrade-db.php
$source_path/post-install.d/httpd-configure.sh
