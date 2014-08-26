# postgresql-server
# php-cli php-pgsql
# httpd php
# nscd  # no unscd T-T

# get_news_notapproved.pl:
# perl perl-DBI perl-Text-Autoformat perl-Mail-Sendmail

# php-htmlpurifier-htmlpurifier  # fedora
# htmlpurifier  # pear
#   pear channel-discover htmlpurifier.org
#   pear install hp/HTMLPurifier
#   Note: htmlpurifier required in -common: group->forum->textsanitizer->htmlpurifier
# arc           # vendor/
# graphite      # vendor/
# php-pear-CAS  # epel
# php-simplepie # epel or common/rss/simplepie.inc

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

# mediawiki (provided by mediawiki119): EPEL

if rpm -q fusionforge >/dev/null ; then
    yum upgrade -y
else
    yum install -y fusionforge
    # -common fusionforge-db fusionforge-plugin-mediawiki fusionforge-web fusionforge-shell

    # Initial configuration
    forge_set_password admin myadmin

    # Backup the DB, so that it can be restored for the test suite
    su - postgres -c "pg_dumpall" > /root/dump
    service postgresql stop
    pgdir=/var/lib/postgresql
    if [ -e /etc/redhat-release ]; then pgdir=/var/lib/pgsql; fi
    if [ -d $pgdir.backup ]; then
        rm -fr $pgdir.backup
    fi
    cp -a --reflink=auto $pgdir $pgdir.backup
    service postgresql start
fi
