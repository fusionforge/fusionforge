#! /bin/bash -e
# Configure FTP server
#
# Copyright 2017, Franck Villaume - TrivialDev
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
vsftpdconffile=$(ls /etc/vsftpd.conf /etc/vsftpd/vsftpd.conf 2>/dev/null | tail -1)
#Debian: /etc/vsftpd.conf
#CentOS: /etc/vsftpd/vsftpd.conf

configure_ftpd() {
	sed -i -e 's/^anonymous_enable=.*$/anonymous_enable=NO/' $vsftpdconffile
	sed -i -e 's/^#ftpd_banner=.*$/ftpd_banner=Welcome to FusionForge FTP server/' $vsftpdconffile
	sed -i -e 's/^#chroot_local_user=.*$/chroot_local_user=YES/' $vsftpdconffile
	if [[ ! -n $is_docker ]]; then
		if [[ -z `grep 'background=NO' $vsftpdconffile` ]];then
			echo 'background=NO' >> $vsftpdconffile
		fi
	fi
}

remove_ftpd() {
	sed -i -e 's/^anonymous_enable=NO.*$/anonymous_enable=YES/' $vsftpdconffile
	sed -i -e 's/^ftpd_banner=Welcome.*$/#ftpd_banner=Welcome to blah FTP service./' $vsftpdconffile
	sed -i -e 's/^chroot_local_user=YES.*$/#chroot_local_user=NO/' $vsftpdconffile
	if [[ ! -n $is_docker ]]; then
		if [[ ! -z `grep 'background=NO' $vsftpdconffile` ]];then
			sed -i '$d' $vsftpdconffile
		fi
	fi
}

restart_ftp_service() {
	if [[ ! -n $is_docker ]]; then
		killall vsftpd >/dev/null 2>&1
	else
		service vsftpd restart
	fi
}

# Main
case "$1" in
	rawconfigure)
		configure_ftpd
		;;
	configure)
		configure_ftpd
		restart_ftp_service
		;;
	remove)
		remove_ftpd
		restart_ftp_service
		;;
	purge)
		;;
	*)
		echo "Usage: $0 {configure|rawconfigure|remove|purge}"
		exit 1
		;;
esac

