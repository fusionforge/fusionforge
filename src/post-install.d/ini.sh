#!/bin/bash -x
# Post-install .ini configuration
# (all other .ini configuration is done at install time)

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)

# TODO: support 'db_get @PACKAGE@/shared/web_host' ?
if [ ! -e $config_path/config.ini.d/post-install.ini ]; then \
	sed $source_path/templates/post-install.ini \
		-e 's,@web_host@,$(hostname -f),' \
		> $config_path/config.ini.d/post-install.ini; \
fi

# Don't overwrite existing config (e.g. previous or Puppet-generated)
if [ -e $config_path/config.ini.d/post-install-secrets.ini ]; then
    exit 0
fi

database_host=$1
database_port=$2
database_name=$3
database_user=$4
database_password_file=$5

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
database_password=$(cat "$database_password_file" 2>/dev/null)
if [ -z $database_password ]; then
    database_password=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)
fi

# Generate session key here for simplificy
session_key=$((head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -d' ' -f1)

# Create config file
sed $source_path/templates/post-install-secrets.ini \
    -e "s,@database_host@,$database_host," \
    -e "s,@database_port@,$database_port," \
    -e "s,@database_name@,$database_name," \
    -e "s,@database_user@,$database_user," \
    -e "s,@database_password@,$database_password," \
    -e "s,@session_key@,$session_key," \
    > $config_path/config.ini.d/post-install-secrets.ini
chmod 600 $config_path/config.ini.d/post-install-secrets.ini
