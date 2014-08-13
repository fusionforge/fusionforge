#!/bin/bash -x
# Call the post-install scripts
# Distro packages will usually call each post-install script directly
# Scripts is "idempotent", aka can safely be run multiple times (Puppet slang)

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)

# Forge system user
# TODO: make this a configuration option, or ditch the user
# TODO: move me out of this script
user=gforge
if ! getent passwd $user >/dev/null; then useradd $user -s /bin/false -d $data_path; fi

# Post-install .ini configuration
$source_path/post-install.d/ini.sh

# Database
$source_path/post-install.d/db-configure.sh
$source_path/post-install.d/db-populate.sh
$source_path/bin/upgrade-db.php

# Apache
$source_path/post-install.d/httpd-configure.sh
