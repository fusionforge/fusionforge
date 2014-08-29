#! /bin/sh
# Install FusionForge packages from build.sh + dependencies

# Authors :
#  Roland Mas
#  Olivier BERGER <olivier.berger@it-sudparis.eu>
#  Sylvain Beucler

#set -x
set -e
export DEBIAN_FRONTEND=noninteractive

# fusionforge-plugin-scmbzr depends on loggerhead (>= 1.19~bzr477~),
# but wheezy only has 1.19~bzr461-1, so we need to manually "Backport"
# a more recent dependency
if grep ^7 /etc/debian_version && ! dpkg-query -s loggerhead >/dev/null 2>&1 ; then
    # install loggerhead with its dependencies
    # we need gdebi to make sure dependencies are installed too (simple dpkg -i won't)
    apt-get -y install gdebi-core wget
    wget -c http://snapshot.debian.org/archive/debian/20121107T152130Z/pool/main/l/loggerhead/loggerhead_1.19%7Ebzr477-1_all.deb
    gdebi --non-interactive loggerhead_1.19~bzr477-1_all.deb
fi

# Install locales-all which is a Recommends and not a Depends
if ! dpkg -l locales-all | grep -q ^ii ; then
    apt-get -y install locales-all
fi

# Install FusionForge packages
apt-get update
if dpkg-query -s fusionforge >/dev/null 2>&1; then
    # Already installed, upgrading
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y dist-upgrade
else
    # Initial installation
    UCF_FORCE_CONFFNEW=yes LANG=C DEBIAN_FRONTEND=noninteractive apt-get -y install fusionforge

    # Additional components for testsuite
    UCF_FORCE_CONFFNEW=yes apt-get install -y fusionforge-shell \
	fusionforge-plugin-scmgit fusionforge-plugin-scmsvn fusionforge-plugin-scmbzr \
	fusionforge-plugin-mediawiki fusionforge-plugin-moinmoin \
	fusionforge-plugin-blocks
fi

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../../func/db_reload.sh --backup; fi
