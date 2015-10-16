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
	$(dirname $0)/upgrade-conf.sh $2

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

	# Destination
	postfix_append_config 'mydestination' $users_host
	postfix_append_config 'relay_domains' $lists_host

	# Mailman
	echo "$lists_host mailman:" > /etc/postfix/fusionforge-lists-transport
	postmap /etc/postfix/fusionforge-lists-transport
	postfix_append_config 'transport_maps' 'hash:/etc/postfix/fusionforge-lists-transport'
	postconf -e mailman_destination_recipient_limit=1

	# Users aliases - database link
	touch /etc/postfix/fusionforge-users.cf
	chown root:postfix /etc/postfix/fusionforge-users.cf
	chmod 640 /etc/postfix/fusionforge-users.cf  # database password
	cat > /etc/postfix/fusionforge-users.cf <<-EOF
		hosts = unix:/var/run/postgresql
		user = $(forge_get_config database_user)_mta
		password = $(forge_get_config database_password_mta)
		dbname = $(forge_get_config database_name)
		domain = $users_host
		query = SELECT email FROM mta_users WHERE login = '%u'
		EOF
	postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-users.cf'

	# Configuration automatically reloaded through 'postconf'
	;;
    
    remove)
	if [ "$(forge_get_config noreply_to_bitbucket)" != 'no' ] ; then
	    sed -i -e '/^noreply:/d' /etc/aliases
	fi
	rm -f /etc/postfix/fusionforge-lists-transport /etc/postfix/fusionforge-lists-transport.db
	postconf -e transport_maps="$(postconf -h transport_maps \
            | sed "s|\(, *\)\?hash:/etc/postfix/fusionforge-lists-transport||")"
	postconf -e virtual_alias_maps="$(postconf -h virtual_alias_maps \
            | sed "s|\(, *\)\?proxy:pgsql:/etc/postfix/fusionforge-users.cf||")"
	postconf -e relay_domains="$(postconf -h relay_domains | sed "s/\(, *\)\?$lists_host//")"
	postconf -e mydestination="$(postconf -h mydestination | sed "s/\(, *\)\?$users_host//")"

	# Configuration automatically reloaded through 'postconf'
	;;

    *)
	echo "Usage: $0 {configure|remove}"
	exit 1
	;;
esac
