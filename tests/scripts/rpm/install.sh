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

# mediawiki (provided by mediawiki119): EPEL


yum install fusionforge-common fusionforge-db fusionforge-plugin-mediawiki fusionforge-web fusionforge-shell
