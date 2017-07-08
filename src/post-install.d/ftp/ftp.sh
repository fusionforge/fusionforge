#! /bin/bash
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

set -x
set -e

. $(forge_get_config source_path)/post-install.d/common/service.inc

configure_ftpd() {
    sed -i -e 's/^anonymous_enable=.*$/anonymous_enable=NO/' /etc/vsftpd/vsftpd.conf
    sed -i -e 's/^#ftpd_banner=.*$/ftpd_banner=Welcome to FusionForge FTP server/' /etc/vsftpd/vsftpd.conf
    sed -i -e 's/^chroot_local_user=.*$/chroot_local_user=YES/' /etc/vsftpd/vsftpd.conf
}

remove_ftpd() {
    sed -i -e 's/^anonymous_enable=NO.*$/anonymous_enable=YES/' /etc/vsftpd/vsftpd.conf
    sed -i -e 's/^ftpd_banner=Welcome.*$/#ftpd_banner=Welcome to blah FTP service./' /etc/vsftpd/vsftpd.conf
    sed -i -e 's/^chroot_local_user=YES.*$/chroot_local_user=NO/' /etc/vsftpd/vsftpd.conf
}

restart_ftp_service()
{
    service vsftpd restart
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

