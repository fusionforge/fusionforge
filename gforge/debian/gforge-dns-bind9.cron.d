#
# Regular cron jobs for the gforge-dns-bind9 package
#

# DNS Update
0 * * * * root [ -x /usr/lib/gforge/bin/install-dns.sh ] && /usr/lib/gforge/bin/install-dns.sh configure > /dev/null 2>&1
