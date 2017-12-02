#!/bin/bash -ex
# Degrade packaging so it fits in Debian testing
#
# Copyright (C) 2015  Inria (Sylvain Beucler)
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

# bzr has FTBFS for months
# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=794146
sed -i -e '/^Package: fusionforge-plugin-scmbzr/,/^$/d' debian/plugins

# Not willing to support CVS anymore
# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=801142
sed -i -e '/^Package: fusionforge-plugin-scmcvs/,/^$/d' debian/plugins

# Unfixable + no time to fix all piuparts nitpicks on a top-priority basis (or else)
# QA Team unresponsive (BTS and private mails)
# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=789772
# https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=789773
rm -f debian/*.{preinst,postinst,prerm,postrm}
mv debian/fusionforge-common.README.Debian-testing debian/fusionforge-common.README.Debian

# Regen debian/control
debian/rules debian/control
