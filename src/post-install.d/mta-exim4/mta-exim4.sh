#! /bin/bash -e
# Configure Exim4 for FusionForge+Mailman
#
# Christian Bayle, Roland Mas, debian-sf
# Converted to Exim4 by Guillem Jover
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

. $(forge_get_config source_path)/post-install.d/common/service.inc

####
# Handle the three configuration types (unsplit, split, manual)
# Note: all are available in Debian; CentOS is manual only
cfgs_exim4_main=''
cfgs_exim4_router=''
if [ -e /etc/exim4/exim4.conf.template ]; then
	cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/exim4.conf.template"
	cfgs_exim4_router="$cfgs_exim4_router /etc/exim4/exim4.conf.template"
fi
if [ -e /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs ]; then
	cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs"
	# + /etc/exim4/conf.d/router/01_fusionforge_forwards entirely generated
fi
if [ -e /etc/exim4/exim4.conf ]; then
	cfgs_exim4_main="$cfgs_exim4_main /etc/exim4/exim4.conf"
	cfgs_exim4_router="$cfgs_exim4_router /etc/exim4/exim4.conf"
fi
if [ -e /etc/exim/exim.conf ]; then
	cfgs_exim4_main="$cfgs_exim4_main /etc/exim/exim.conf"
	cfgs_exim4_router="$cfgs_exim4_router /etc/exim/exim.conf"
fi

case "$1" in
	configure)
		$(dirname $0)/upgrade-conf.sh $2

		users_host=$(forge_get_config users_host)
		lists_host=$(forge_get_config lists_host)
		pgsock='/var/run/postgresql/.s.PGSQL.5432'
		if [ -e '/etc/redhat-release' ]; then pgsock='/tmp/.s.PGSQL.5432'; fi
		database_name=$(forge_get_config database_name)
		database_user=$(forge_get_config database_user)
		database_password_mta=$(forge_get_config database_password_mta)

		# Redirect "noreply" mail to the bit bucket (if need be)
		if [ "$(forge_get_config noreply_to_bitbucket)" != 'no' ] ; then
			if ! grep -q '^noreply:' /etc/aliases ; then
				echo 'noreply: :blackhole:' >> /etc/aliases
			fi
		fi

		# Main configuration: list of local domains
		for i in $cfgs_exim4_main; do
			sed -i '/:FUSIONFORGE_DOMAINS/! s/^domainlist local_domains.*/&:FUSIONFORGE_DOMAINS/' $i
			if ! grep -q '^FUSIONFORGE_DOMAINS=' $i; then
				chmod 600 $i
				sed -i '/^domainlist local_domains/ecat' $i <<EOF
hide pgsql_servers = ($pgsock)/mail/Debian-exim/bogus:($pgsock)/$database_name/${database_user}_mta/${database_password_mta}
FUSIONFORGE_DOMAINS=$users_host:$lists_host
EOF
			fi
		done

		# Router configuration
		block=$(mktemp)
		cat <<EOF > $block
### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT ###
# You may move this block around to accomodate your local needs as long as you
# keep it in the Directors Configuration section (between the second and the
# third occurences of a line containing only the word "end")

forward_for_fusionforge:
  domains = $users_host
  driver = redirect
  file_transport = address_file
  data = \${lookup pgsql {select email from mta_users where login='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists:
  domains = $lists_host
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select post_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_owner:
  domains = $lists_host
  local_part_suffix = -owner
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select owner_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_request:
  domains = $lists_host
  local_part_suffix = -request
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select request_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_admin:
  domains = $lists_host
  local_part_suffix = -admin
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select admin_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_bounces:
  domains = $lists_host
  local_part_suffix = -bounces : -bounces+*
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select bounces_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_confirm:
  domains = $lists_host
  local_part_suffix = -confirm : -confirm+*
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select confirm_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_join:
  domains = $lists_host
  local_part_suffix = -join
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select join_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_leave:
  domains = $lists_host
  local_part_suffix = -leave
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select leave_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_subscribe:
  domains = $lists_host
  local_part_suffix = -subscribe
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select subscribe_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup

forward_for_fusionforge_lists_unsubscribe:
  domains = $lists_host
  local_part_suffix = -unsubscribe
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select unsubscribe_address from mta_lists where list_name='\$local_part'}{\$value}}
  user = nobody
  group = nogroup
### END FUSIONFORGE BLOCK -- DO NOT EDIT
EOF
		# Stand-alone file:
		if [ -d /etc/exim4/conf.d/router/ ]; then
			cp $block /etc/exim4/conf.d/router/01_fusionforge_forwards
		fi
		# Add the same in the unsplit big file(s)
		for i in $cfgs_exim4_router; do
			if ! grep -q '^### BEGIN FUSIONFORGE BLOCK' $i; then
				sed -i -e '/^begin routers$/ {' -e 'ecat' -e 'd }' $i <<-EOF
					begin routers
					### BEGIN FUSIONFORGE BLOCK -- DO NOT EDIT ###
					### END FUSIONFORGE BLOCK ###
					EOF
			fi
			sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/ { ' \
				-e 'ecat' -e 'd }' $i < $block
		done
		rm -f $block

		service exim4 restart
		;;

	remove)
		if [ "$(forge_get_config noreply_to_bitbucket)" != 'no' ] ; then
			sed -i -e '/^noreply:/d' /etc/aliases
		fi

		# main conf
		database_name=$(forge_get_config database_name)
		for i in $cfgs_exim4_main; do
			sed -i -e '/^FUSIONFORGE_DOMAINS=/d' \
				-e "/^hide pgsql_servers = .*$database_name.*/d" \
				-e '/domainlist local_domains.*/ s/:FUSIONFORGE_DOMAINS//' $i
		done

		# routers
		for i in $cfgs_exim4_router; do
			sed -i -e '/^### BEGIN FUSIONFORGE BLOCK/,/^### END FUSIONFORGE BLOCK/d' $i
		done
		rm -f /etc/exim4/conf.d/router/01_fusionforge_forwards

		service exim4 restart
		;;

	*)
		echo "Usage: $0 {configure|remove}"
		exit 1
		;;
esac
