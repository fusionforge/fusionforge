#!/bin/bash -e
# Create user and database, and import initial data
#
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

. $(forge_get_config source_path)/post-install.d/common/service.inc

database_host=$(forge_get_config database_host)
database_port=$(forge_get_config database_port)
database_name=$(forge_get_config database_name)
database_user=$(forge_get_config database_user)
database_password=$(forge_get_config database_password)
database_password_mta=$(forge_get_config database_password_mta)
database_password_ssh_akc=$(forge_get_config database_password_ssh_akc)
source_path=$(forge_get_config source_path)

if [ -z "$database_name" ]; then
    echo "Cannot get database_name"
    exit 1
fi

# Create database
if ! su - postgres -c 'psql -At -l' | grep "^$database_name|" >/dev/null; then
    su - postgres -c "createdb --template template0 --encoding UNICODE $database_name"
    echo "CREATE EXTENSION IF NOT EXISTS plpgsql" | su - postgres -c "psql $database_name"
fi

# Create DB user
if ! su - postgres -c 'psql -At -c \\du' | grep "^$database_user|" >/dev/null; then
    su - postgres -c "createuser -SDR $database_user"
fi
if ! su - postgres -c 'psql -At -c \\du' | grep "^${database_user}_nss|" >/dev/null; then
    su - postgres -c "createuser -SDR ${database_user}_nss"
fi
if ! su - postgres -c 'psql -At -c \\du' | grep "^${database_user}_mta|" >/dev/null; then
    su - postgres -c "createuser -SDR ${database_user}_mta"
fi
if ! su - postgres -c 'psql -At -c \\du' | grep "^${database_user}_ssh_akc|" >/dev/null; then
    su - postgres -c "createuser -SDR ${database_user}_ssh_akc"
fi
database_password_quoted=$(echo $database_password | sed -e "s/'/''/")
database_password_mta_quoted=$(echo $database_password_mta | sed -e "s/'/''/")
database_password_ssh_akc_quoted=$(echo $database_password_ssh_akc | sed -e "s/'/''/")
su - postgres -c psql <<EOF >/dev/null
ALTER ROLE $database_user WITH PASSWORD '$database_password_quoted';
GRANT CREATE ON DATABASE $database_name TO $database_user;  -- for wiki schemas
ALTER ROLE ${database_user}_mta WITH PASSWORD '$database_password_mta_quoted';
ALTER ROLE ${database_user}_ssh_akc WITH PASSWORD '$database_password_ssh_akc_quoted';
EOF

export PGPASSFILE=$(mktemp)
cat <<EOF > $PGPASSFILE
$database_host:$database_port:$database_name:$database_user:$database_password
EOF

# Database init
if ! su - postgres -c "psql $database_name -c 'SELECT COUNT(*) FROM users;'" >/dev/null 2>&1;  then
    echo "Importing initial database..."
    psql -h $database_host -p $database_port -U $database_user $database_name < $source_path/db/1-fusionforge-init.sql >/dev/null
fi

# Database upgrade
$source_path/post-install.d/db/upgrade.php

# Additional grants
psql -h $database_host -p $database_port -U $database_user $database_name <<EOF >/dev/null
GRANT SELECT ON nss_passwd TO ${database_user}_nss;
GRANT SELECT ON nss_groups TO ${database_user}_nss;
GRANT SELECT ON nss_usergroups TO ${database_user}_nss;
GRANT SELECT ON mta_users TO ${database_user}_mta;
GRANT SELECT ON mta_lists TO ${database_user}_mta;
GRANT SELECT ON ssh_authorized_keys TO ${database_user}_ssh_akc;
EOF

# Admin user
req="SELECT COUNT(*) FROM users WHERE user_name='admin'"
if [ "$(echo $req | su - postgres -c "psql -At $database_name")" != "1" ]; then
    psql -h $database_host -p $database_port -U $database_user $database_name <<EOF >/dev/null
INSERT INTO users (user_name, realname, firstname, lastname, email,
    unix_pw, status, theme_id)
  VALUES ('admin', 'Forge Admin', 'Forge', 'Admin', 'root@localhost.localdomain',
    'INVALID', 'A', (SELECT theme_id FROM themes WHERE dirname='funky'));
EOF
    forge_make_admin admin  # set permissions
    # Note: no password defined yet
fi

rm -f $PGPASSFILE
unset PGPASSFILE

# Usually systasksd fails to start on first install because the DB
# isn't populated yet.  Also it's a good idea to restart resident
# daemons on schema upgrade.
service fusionforge-systasksd restart
