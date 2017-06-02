#!/bin/bash
#
# FusionForge startpoint script. Entrypoint for docker container
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
set -e

__postinstall() {
/usr/local/share/fusionforge/post-install.d/common/common.sh configure
/usr/local/share/fusionforge/post-install.d/web/web.sh rawconfigure
/usr/local/share/fusionforge/post-install.d/shell/shell.sh rawconfigure
}

__etchost() {
echo "127.0.0.1  scm."`hostname -f` >> /etc/hosts
}

__run_supervisor() {
supervisord -n
}

# Call all functions
__postinstall
__etchost
__run_supervisor
