#!/bin/bash
# Build FusionForge packages and create a local repo
#
# Copyright (C) 2011  Roland Mas
# Copyright (C) 2011  Olivier Berger - Institut Telecom
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
. $(dirname $0)/common-backports

# Debian and Fedora/CentOS/RHEL *package building* are so different
# that there's nothing to factour out, so they are in separate functions.

function build_deb {
	export DEBIAN_FRONTEND=noninteractive
	srcdir=$(dirname $0)
	# Install build dependencies
	apt-get -y install mini-dinstall dput devscripts fakeroot
	apt-get -y install build-essential \
		$(grep Build-Depends ${srcdir}/src/debian/control.in | sed -e 's/Build-Depends: //' -e 's/(.*)//')
    if grep -q ^8 /etc/debian_version; then
	apt-get -y install php5-cli  # debian/gen_control.sh
    else
	apt-get -y install php-cli  # debian/gen_control.sh
    fi

    # Populate a local Debian packages repository for APT managed with mini-dinstall
    rm -rf /usr/src/debian-repository
    mkdir -p /usr/src/debian-repository

    cat >/root/.mini-dinstall.conf <<-EOF | sed 's,@PATH@,$srcdir,g'
	[DEFAULT]
	archivedir = /usr/src/debian-repository
	archive_style = flat
	architectures = "all, amd64"
	
	verify_sigs = 0
	
	generate_release = 1
	release_signscript = @PATH@/autoinstall/mini-dinstall-sign.sh
	
	max_retry_time = 3600
	mail_on_success = false
	
	[local]
	EOF

    export GNUPGHOME=/usr/src/gnupg
    if [ ! -e $GNUPGHOME ]; then
        mkdir -m 700 $GNUPGHOME
        # Quick 'n Dirty hack to get entropy on VMs
        # https://bugs.launchpad.net/ubuntu/+source/gnupg/+bug/706011
        # (don't do this for a public repo!)
        apt-get install -y rng-tools
        echo HRNGDEVICE=/dev/urandom >> /etc/default/rng-tools
        service rng-tools restart
        gpg --batch --gen-key <<-EOF
	Key-Type: RSA
	Key-Length: 2048
	Subkey-Type: RSA
	Subkey-Length: 2048
	Name-Real: FusionForge
	Expire-Date: 0
	%no-protection
	%commit
	EOF
    fi
    gpg --export FusionForge -a > /usr/src/debian-repository/key.asc
    apt-key add /usr/src/debian-repository/key.asc
    
    mini-dinstall -bv
    
    # Configure debian package building tools so as to use the local repo
    if [ ! -f /root/.dput.cf ]; then
        cat > /root/.dput.cf <<-EOF
	[local]
	fqdn = localhost
	incoming = /usr/src/debian-repository/mini-dinstall/incoming 
	method = local
	run_dinstall = 0
	allow_unsigned_uploads = yes
	post_upload_command = mini-dinstall -bv
	allowed_distributions = local
	EOF
    fi
	
    if [ ! -f /root/.devscripts ]; then
        cat > /root/.devscripts <<-EOF
	DEBRELEASE_UPLOADER=dput
	DEBUILD_DPKG_BUILDPACKAGE_OPTS=-i
	EOF
    fi

    # Finally, build the FusionForge packages
    f=$(mktemp)
    cd $(dirname $0)/../src/
    cp -a debian/changelog $f

    version=$(dpkg-parsechangelog | sed -n 's/^Version: \([0-9.]\+\(\~\(rc\|beta\|alpha\)[0-9]\)\?\).*/\1/p')+autobuilt$(date +%Y%m%d%H%M)
    make dist VERSION=$version
    mv fusionforge-$version.tar.bz2 ../fusionforge_$version.orig.tar.bz2
    cd ..

    tar xf fusionforge_$version.orig.tar.bz2
    cd fusionforge-$version/
    debian/rules debian/control  # re-gen debian/control
    if gitid=$(git show --format="%h" -s 2> /dev/null) ; then
	msg="Autobuilt from Git revid $gitid."
    else
	msg="Autobuilt."
    fi
    dch --newversion $version-1 --distribution local --force-distribution "$msg"
    debuild -us -uc -tc  # using -tc so 'git status' is readable
    # Install built packages into the local repo
    debrelease -f local
    cd ..

    mv $f src/debian/changelog
    
    # Declare the repo so that packages become installable
    echo 'deb file:///usr/src/debian-repository local/' > /etc/apt/sources.list.d/local.list
    apt-get update
}
    

function build_rpm {
    # Install build dependencies
    yum makecache
    yum install -y make gettext tar bzip2 rpm-build  createrepo
    yum install -y php-cli  # rpm/gen_spec.sh
    
    # Build package
    cd $(dirname $0)/../src/
    base_version=$(make version)
    snapshot=$(date +%Y%m%d%H%M)
    version=$base_version+$snapshot
    rpm/gen_spec.sh $base_version $snapshot
    make dist VERSION=$version
    mkdir -p ../build/SOURCES/ ../build/SPECS/
    mv fusionforge-$version.tar.bz2 ../build/SOURCES/fusionforge-$version.tar.bz2
    chown -h root: ../build/SOURCES/fusionforge-$version.tar.bz2
    cp fusionforge.spec ../build/SPECS/
    rpmbuild ../build/SPECS/fusionforge.spec --define "_topdir $(pwd)/../build" -ba
    
    (cd ../build/RPMS/ && createrepo .)
    repopath=$(readlink  ../build/RPMS/)
    cat <<-EOF | sed 's,@PATH@,$repopath,g' > /etc/yum.repos.d/local.repo
	[local]
	name=local
	baseurl=file://@PATH@
	enabled=1
	gpgcheck=0
	EOF
}

function build_suse_rpm {
	suse_check_release
	suse_install_repos
	suse_install_rpms make gettext-runtime gettext-tools tar bzip2 rpm-build createrepo php7

	# Build package
	cd $(dirname $0)/../src/
	base_version=$(make version)
	snapshot=$(date +%Y%m%d%H%M)
	version=$base_version+$snapshot
	rpm/gen_spec.sh $base_version $snapshot
	make dist VERSION=$version
	mkdir -p ../build/SOURCES/ ../build/SPECS/
	mv fusionforge-$version.tar.bz2 ../build/SOURCES/fusionforge-$version.tar.bz2
	chown -h root: ../build/SOURCES/fusionforge-$version.tar.bz2
	cp fusionforge.spec ../build/SPECS/
	rpmbuild ../build/SPECS/fusionforge.spec --define "_topdir $(pwd)/../build" -ba

	(cd ../build/RPMS/ && createrepo .)
	repopath=$(readlink  ../build/RPMS/)
	cat <<-EOF | sed 's,@PATH@,$repopath,g' > /etc/zypp/repos.d/local.repo
	[local]
	name=local
	baseurl=file://@PATH@
	enabled=1
	gpgcheck=0
	EOF
}

if [ -e /etc/debian_version ]; then
	build_deb
elif [ -e /etc/redhat-release ]; then
	build_rpm
elif [[ ! -z `cat /etc/os-release | grep 'SUSE'` ]]; then
	build_suse_rpm
else
	echo "Automated package building is not supported for this distribution."
	echo "See https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Installing/FromSource"
	echo "for instructions"
fi
