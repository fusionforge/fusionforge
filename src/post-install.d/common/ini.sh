#!/bin/bash
# Post-install .ini configuration, params vary for each install
# (all other .ini configuration is done at install time)
#
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)

# TODO: support 'db_get @PACKAGE@/shared/web_host' ?
hostname=$(hostname -f || hostname)
if [ ! -e $config_path/config.ini.d/post-install.ini ]; then \
	sed $source_path/templates/post-install.ini \
		-e "s,@web_host@,$hostname," \
		> $config_path/config.ini.d/post-install.ini; \
fi

database_host=$1
database_port=$2
database_name=$3
database_user=$4
database_password_file=$5
database_password_file_mta=$6
database_password_file_ssh_akc=$7

if [ -z $database_host ]; then
    database_host=127.0.0.1
fi
if [ -z $database_port ]; then
    database_port=5432
fi
if [ -z $database_name ]; then
    database_name=fusionforge
fi
if [ -z $database_user ]; then
    database_user=fusionforge
fi

# Don't overwrite existing config (e.g. previous or Puppet-generated)
if [ ! -e $config_path/config.ini.d/post-install-secrets.ini ]; then
    database_password=$(cat "$database_password_file" 2>/dev/null)
    if [ -z "$database_password" ]; then
	database_password=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)
    fi
    database_password_mta=$(cat "$database_password_mta_file" 2>/dev/null)
    if [ -z "$database_password_mta" ]; then
	database_password_mta=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)
    fi
    
    # Generate session key here for simplificy
    session_key=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)
    
    # Create config file
    sed $source_path/templates/post-install-secrets.ini \
	-e "s,@database_host@,$database_host," \
	-e "s,@database_port@,$database_port," \
	-e "s,@database_name@,$database_name," \
	-e "s,@database_user@,$database_user," \
	-e "s,@session_key@,$session_key," \
	> $config_path/config.ini.d/post-install-secrets.ini
    chmod 600 $config_path/config.ini.d/post-install-secrets.ini
    sed -i -e '/^@secrets@/ { ' -e 'ecat' -e 'd }' \
	$config_path/config.ini.d/post-install-secrets.ini <<-EOF
	session_key=$session_key
	database_password=$database_password
	database_password_mta=$database_password_mta
	EOF
fi

# Special conf for AuthorizedKeysCommand (chown'd in post-install.d/shell/shell.sh)
if [ ! -e $config_path/config.ini.d/post-install-secrets-ssh_akc.ini ]; then
    database_password_ssh_akc=$(cat "$database_password_ssh_akc_file" 2>/dev/null)
    if [ -z "$database_password_ssh_akc" ]; then
	database_password_ssh_akc=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)
    fi
    cat <<-EOF > $config_path/config.ini.d/post-install-secrets-ssh_akc.ini
	[core]
	database_host=$database_host
	database_port=$database_port
	database_name=$database_name
	database_user_ssh_akc=${database_user}_ssh_akc
	database_password_ssh_akc=$database_password_ssh_akc
	EOF
    chmod 600 $config_path/config.ini.d/post-install-secrets-ssh_akc.ini
fi
