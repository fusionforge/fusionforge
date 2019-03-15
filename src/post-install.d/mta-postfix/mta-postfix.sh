#! /bin/bash -e
# Configure Postfix for FusionForge+Mailman
#
# Christian Bayle, Roland Mas
# Julien Goodwin
# Copyright (C) 2014  Inria (Sylvain Beucler)
# Copyright 2019, Franck Villaume - TrivialDev
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

		# Mailing-lists aliases - database link
		touch /etc/postfix/fusionforge-lists.cf
		chown root:postfix /etc/postfix/fusionforge-lists.cf
		chmod 640 /etc/postfix/fusionforge-lists.cf  # database password
		cat > /etc/postfix/fusionforge-lists.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists.cf'

		touch /etc/postfix/fusionforge-lists-owner.cf
		chown root:postfix /etc/postfix/fusionforge-lists-owner.cf
		chmod 640 /etc/postfix/fusionforge-lists-owner.cf  # database password
		cat > /etc/postfix/fusionforge-lists-owner.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-owner' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-owner.cf'

		touch /etc/postfix/fusionforge-lists-request.cf
		chown root:postfix /etc/postfix/fusionforge-lists-request.cf
		chmod 640 /etc/postfix/fusionforge-lists-request.cf  # database password
		cat > /etc/postfix/fusionforge-lists-request.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-request' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-request.cf'

		touch /etc/postfix/fusionforge-lists-admin.cf
		chown root:postfix /etc/postfix/fusionforge-lists-admin.cf
		chmod 640 /etc/postfix/fusionforge-lists-admin.cf  # database password
		cat > /etc/postfix/fusionforge-lists-admin.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-admin' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-admin.cf'

		touch /etc/postfix/fusionforge-lists-bounces.cf
		chown root:postfix /etc/postfix/fusionforge-lists-bounces.cf
		chmod 640 /etc/postfix/fusionforge-lists-bounces.cf  # database password
		cat > /etc/postfix/fusionforge-lists-bounces.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-bounces' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-bounces.cf'

		touch /etc/postfix/fusionforge-lists-confirm.cf
		chown root:postfix /etc/postfix/fusionforge-lists-confirm.cf
		chmod 640 /etc/postfix/fusionforge-lists-confirm.cf  # database password
		cat > /etc/postfix/fusionforge-lists-confirm.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-confirm' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-confirm.cf'

		touch /etc/postfix/fusionforge-lists-join.cf
		chown root:postfix /etc/postfix/fusionforge-lists-join.cf
		chmod 640 /etc/postfix/fusionforge-lists-join.cf  # database password
		cat > /etc/postfix/fusionforge-lists-join.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-join' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-join.cf'

		touch /etc/postfix/fusionforge-lists-leave.cf
		chown root:postfix /etc/postfix/fusionforge-lists-leave.cf
		chmod 640 /etc/postfix/fusionforge-lists-leave.cf  # database password
		cat > /etc/postfix/fusionforge-lists-leave.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-leave' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-leave.cf'

		touch /etc/postfix/fusionforge-lists-subscribe.cf
		chown root:postfix /etc/postfix/fusionforge-lists-subscribe.cf
		chmod 640 /etc/postfix/fusionforge-lists-subscribe.cf  # database password
		cat > /etc/postfix/fusionforge-lists-subscribe.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-subscribe' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-subscribe.cf'

		touch /etc/postfix/fusionforge-lists-unsubscribe.cf
		chown root:postfix /etc/postfix/fusionforge-lists-unsubscribe.cf
		chmod 640 /etc/postfix/fusionforge-lists-unsubscribe.cf  # database password
		cat > /etc/postfix/fusionforge-lists-unsubscribe.cf <<-EOF
			hosts = unix:/var/run/postgresql
			user = $(forge_get_config database_user)_mta
			password = $(forge_get_config database_password_mta)
			dbname = $(forge_get_config database_name)
			domain = $users_host
			query = SELECT list_name||'-unsubscribe' FROM mta_lists WHERE list_name = '%u'
			EOF
		postfix_append_config 'virtual_alias_maps' 'proxy:pgsql:/etc/postfix/fusionforge-lists-unsubscribe.cf'

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
		rm -f /etc/postfix/fusionforge-lists.cf /etc/postfix/fusionforge-users.cf
		postconf -e transport_maps="$(postconf -h transport_maps \
			| sed "s|\(, *\)\?hash:/etc/postfix/fusionforge-lists-transport||")"
		postconf -e virtual_alias_maps="$(postconf -h virtual_alias_maps \
			| sed "s|\(, *\)\?proxy:pgsql:/etc/postfix/fusionforge-lists.cf||")"
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
