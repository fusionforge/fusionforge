#!/bin/bash -e
# Generate .spec from .spec.in + ./plugins + plugin_pkg_desc.php
#
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

version=$1
snapshot=$2

if [ -z "$version" ]; then version=$(make version); fi

# rpm needs snapshot version separately (because 6.0+20141027 > 6.0.1, unlike in Debian)
# Note: update fusionforge.spec.in:Release manually (0.1, 0.2, 1, 2...)
rpm_snapshot=''
if echo $version | grep -q 'rc'; then
    rpm_version=$(echo $version | sed 's/rc.*//')
    # Note: update fusionforge.spec.in:Release as 0.X@snapshot@ when rolling out RCs
    # https://fedoraproject.org/wiki/Packaging%3aNamingGuidelines#NonNumericRelease
    rpm_snapshot=.rc$(echo $version | sed 's/.*rc//')
else
    rpm_version=$version
fi

tarball_version=$version
if [ -n "$snapshot" ]; then
    tarball_version=$version+$snapshot
    rpm_snapshot=$rpm_snapshot.$snapshot
fi

rm -f fusionforge.spec
(
    for i in $(sed -n 's/^%package plugin-//p' rpm/plugins); do
	sed -n -e '/^#/d' -e "/^%package plugin-$i/,/^$/p" rpm/plugins \
	    | grep -v ^$ \
	    | sed 's/Requires:\(.*\)/Requires: %{name}-common = %{version}-%{release},\1/'
	#echo "Group: Development/Tools"
	php utils/plugin_pkg_desc.php $i rpm
	cat <<-EOF
	%files plugin-$i -f plugin-$i.rpmfiles
	%post plugin-$i
	%{_datadir}/%{name}/post-install.d/common/plugin.sh $i configure
	%preun plugin-$i
	if [ \$1 -eq 0 ] ; then
		%{_datadir}/%{name}/post-install.d/common/plugin.sh $i remove
		%{_datadir}/%{name}/post-install.d/common/plugin.sh $i purge
	fi
	EOF
	echo
	echo
    done
) \
| sed \
    -e "s/@rpm_version@/$rpm_version/" \
    -e "s/@rpm_snapshot@/$rpm_snapshot/" \
    -e "s/@tarball_version@/$tarball_version/" \
    -e '/^@plugins@/ { ' -e 'ecat' -e 'd }' \
    rpm/fusionforge.spec.in > fusionforge.spec
chmod a-w fusionforge.spec
