# postgresql-server
# php-cli php-pgsql
# httpd php
# nscd  # no unscd T-T

# get_news_notapproved.pl:
# perl perl-DBI perl-Text-Autoformat perl-Mail-Sendmail

# htmlpurifier  # pear
#   pear channel-discover htmlpurifier.org
#   pear install hp/HTMLPurifier
#   Note: htmlpurifier requires in -common: group->forum->textsanitizer->htmlpurifier
# arc           # vendor/
# graphite      # vendor/
# php-pear-CAS  # epel
# php-simplepie # epel or common/rss/simplepie.inc

# EPEL6
# rpm -ivh http://mirror.ibcp.fr/pub/epel/6/i386/epel-release-6-8.noarch.rpm

# moin: no available package for RHEL; though 'moin' is available in Fedora
# libnss-pgsql: id., plus http://yum.postgresql.org/8.4/redhat/rhel-5-x86_64/
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

yumdownloader --enablerepo=fedora --source libnss-pgsql
DEPS="python-devel"
yum install -y $DEPS
rpmbuild --rebuild moin-*.src.rpm
yum remove -y $DEPS
rpm -ivh ~/rpmbuild/RPMS/noarch/moin-1.9.7-2.el6.noarch.rpm

yumdownloader --enablerepo=fedora --source libnss-pgsql
DEPS="gcc postgresql-devel xmlto"
yum install -y $DEPS
rpmbuild --rebuild libnss-pgsql-*.src.rpm
yum remove -y $DEPS
rpm -ivh ~/rpmbuild/RPMS/x86_64/libnss-pgsql-1.5.0-0.9.beta.el6.x86_64.rpm


# mediawiki (provided by mediawiki119): EPEL


yum install fusionforge-common fusionforge-db fusionforge-plugin-mediawiki fusionforge-web fusionforge-shell
