#!/bin/bash
# Create user and database, and import initial data

database_host=$(forge_get_config database_host)
database_port=$(forge_get_config database_port)
database_name=$(forge_get_config database_name)
database_user=$(forge_get_config database_user)
database_password=$(forge_get_config database_password)
source_path=$(forge_get_config source_path)

if [ -z "$database_name" ]; then
    echo "Cannot get database_name"
    exit 1
fi

# Abort if db already exists
if su - postgres -c "psql $database_name" </dev/null 2>/dev/null; then
    echo "Database '$database_name' already exists, skipping."
    exit 0
fi

# Create DB user
su - postgres -c 'createuser -SDR fusionforge'
database_password_quoted=$(echo $database_password | sed -e "s/'/''/")
su - postgres -c psql <<EOF
ALTER ROLE $database_user WITH PASSWORD '$database_password_quoted' ;
EOF

# Create database
su - postgres -c "createdb --template template0 --encoding UNICODE $database_name"
if ! su - postgres -c "createlang -l $database_name" | grep -q plpgsql; then \
    su - postgres -c "createlang plpgsql $database_name"; \
fi

export PGPASSFILE=$(mktemp)
cat <<EOF > $PGPASSFILE
$database_host:$database_port:$database_name:$database_user:$database_password
EOF
psql -h $database_host -p $database_port -U $database_user $database_name < $source_path/db/1-fusionforge-init.sql
rm -f $PGPASSFILE
unset PGPASSFILE
