#! /bin/bash
# Configure Postfix for FusionForge+Mailman
#
# Christian Bayle, Roland Mas
# Julien Goodwin
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

set -e

users_host=$(forge_get_config users_host)
lists_host=$(forge_get_config lists_host)
data_path=$(forge_get_config data_path)

function postfix_append_config {
    param=$1
    append_val=$2

    prev_val=$(postconf -h $param)
    if ! echo $prev_val | grep -q "$append_val"; then
	if [ -z "$prev_val" ]; then
	    postconf -e $param="$append_val"
	else
	    postconf -e $param="$prev_val, $append_val"
	fi
    fi
}

case "$1" in
    configure)
	# Init postfix configuration if missing
	if [ ! -e /etc/postfix/main.cf ]; then
	    cp /usr/share/postfix/main.cf.debian /etc/postfix/main.cf
	fi

	# Redirect "noreply" mail to the bit bucket (if need be)
	if [ "$(forge_get_config noreply_to_bitbucket)" != 'no' ] ; then
	    if ! grep -q '^noreply:' /etc/aliases ; then
		echo 'noreply: /dev/null' >> /etc/aliases
	    fi
	fi

	# Transport
	postfix_append_config 'mydestination' $users_host
	postfix_append_config 'relay_domains' $lists_host

	# Forwarding rules
	postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:pgsql_fusionforge_users'

	postfix_append_config 'transport_maps' "hash:$data_path/etc/postfix-transport"
	mkdir -m 755 -p $data_path/etc/
	echo "$lists_host mailman:" > $data_path/etc/postfix-transport
	postmap $data_path/etc/postfix-transport

	if ! grep -q '^### BEGIN FUSIONFORGE BLOCK' /etc/postfix/main.cf; then
	    cat <<-EOF >>/etc/postfix/main.cf
		### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT
		### END FUSIONFORGE BLOCK -- DO NOT EDIT
		EOF
	fi
	chmod 600 /etc/postfix/main.cf  # adding database password
	sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/ { ' -e 'ecat' -e 'd }' \
	    /etc/postfix/main.cf <<EOF
### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT ###
# You may move this block around to accomodate your local needs as long as you
# keep it in an appropriate position, where "appropriate" is defined by you.
pgsql_fusionforge_users_hosts = unix:/var/run/postgresql
pgsql_fusionforge_users_user = $(forge_get_config database_user)_mta
pgsql_fusionforge_users_password = $(forge_get_config database_password_mta)
pgsql_fusionforge_users_dbname = $(forge_get_config database_name)
pgsql_fusionforge_users_domain = $users_host
pgsql_fusionforge_users_query = SELECT email FROM mta_users WHERE login = '%u'
mailman_destination_recipient_limit = 1
### END FUSIONFORGE BLOCK ###
EOF
	;;
    
    remove)
	if [ "$(forge_get_config noreply_to_bitbucket)" != 'no' ] ; then
	    sed -i -e '/^noreply:/d' /etc/aliases
	fi
	sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/d' /etc/postfix/main.cf
	rm -f $data_path/etc/postfix-transport
	postconf -e transport_maps="$(postconf -h transport_maps \
            | sed "s|\(, *\)\?hash:$data_path/etc/postfix-transport||")"
	postconf -e virtual_alias_maps="$(postconf -h virtual_alias_maps \
            | sed "s/\(, *\)\?proxy:pgsql:pgsql_fusionforge_users//")"
	postconf -e relay_domains="$(postconf -h relay_domains | sed "s/\(, *\)\?$lists_host//")"
	postconf -e mydestination="$(postconf -h mydestination | sed "s/\(, *\)\?$users_host//")"
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
