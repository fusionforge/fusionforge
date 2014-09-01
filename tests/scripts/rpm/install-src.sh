#! /bin/sh
# Install FusionForge from source

# Fedora/RHEL/CentOS version:
os_version=$(rpm -q --qf "%{VERSION}" $(rpm -q --whatprovides redhat-release))

if ! rpm -q fedora-release >/dev/null; then
    # EPEL - http://download.fedoraproject.org/pub/epel/6/i386/repoview/epel-release.html
    if ! rpm -q epel-release >/dev/null; then
	rpm -ivh http://fr2.rpmfind.net/linux/epel/6/i386/epel-release-6-8.noarch.rpm
    fi

    # Prepare manual backports
    cat <<'EOF' > /etc/yum.repos.d/fedora-source.repo
[fedora]
name=Fedora 20
failovermethod=priority
metalink=https://mirrors.fedoraproject.org/metalink?repo=fedora-20&arch=$basearch
enabled=0
gpgcheck=0
[fedora-source]
name=Fedora 20 - Source
failovermethod=priority
metalink=https://mirrors.fedoraproject.org/metalink?repo=fedora-source-20&arch=$basearch
enabled=0
gpgcheck=0
EOF
    yum install -y yum-utils  # yumdownloader
fi

if yum list libnss-pgsql >/dev/null 2>&1; then
    yum install -y libnss-pgsql
else
    # libnss-pgsql: id., plus http://yum.postgresql.org/8.4/redhat/rhel-5-x86_64/
    yumdownloader --enablerepo=fedora --source libnss-pgsql
    DEPS="gcc postgresql-devel xmlto"
    yum install -y $DEPS
    rpmbuild --rebuild libnss-pgsql-*.src.rpm
    yum remove -y $DEPS
    rpm -ivh ~/rpmbuild/RPMS/x86_64/libnss-pgsql-*.x86_64.rpm
fi

if yum list moin >/dev/null 2>&1; then
    yum install -y moin
else
    # moin: no available package for RHEL; though 'moin' is available in Fedora
    yumdownloader --enablerepo=fedora --source moin
    DEPS="python-devel"
    yum install -y $DEPS
    rpmbuild --rebuild moin-*.src.rpm
    yum remove -y $DEPS
    rpm -ivh ~/rpmbuild/RPMS/noarch/moin-*.noarch.rpm
fi

if yum list php-htmlpurifier-htmlpurifier >/dev/null 2>&1; then
    yum install -y php-htmlpurifier-htmlpurifier
else
    # moin: no available package for RHEL; though 'moin' is available in Fedora
    yumdownloader --enablerepo=fedora --source php-htmlpurifier-htmlpurifier
    DEPS="php-channel-htmlpurifier"  # for v4.3.0-6.fc20
    yum install -y $DEPS
    rpmbuild --rebuild php-htmlpurifier-htmlpurifier-*.src.rpm
    yum remove -y $DEPS
    yum install -y ~/rpmbuild/RPMS/noarch/php-htmlpurifier-htmlpurifier-*.noarch.rpm
fi

# TODO: postfix: rebuild from RHEL/CentOS sources with pgsql enabled,
# so we can test SSH

# Install FusionForge packages
yum install -y make gettext confget php-cli php-pgsql \
    httpd postgresql \
    subversion \
    mediawiki119 \
    moin mod_wsgi python-psycopg2
# TODO: replace python-subversion with non-bundled viewvc

cd /usr/src/fusionforge/src/
make
make install-base install-shell
make install-plugin-scmsvn install-plugin-blocks \
    install-plugin-mediawiki install-plugin-moinmoin \
    install-plugin-online_help
# adapt .ini configuration in /etc/fusionforge/config.ini.d/
make post-install-base post-install-plugin-scmsvn post-install-plugin-blocks \
    post-install-plugin-mediawiki post-install-plugin-moinmoin \
    post-install-plugin-online_help

# Dump clean DB
if [ ! -e /root/dump ]; then $(dirname $0)/../../func/db_reload.sh --backup; fi
