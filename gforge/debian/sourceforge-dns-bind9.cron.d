#
# Regular cron jobs for the sourceforge-dns-bind9 package
#

# DNS Update
0 * * * * root [ -x /usr/lib/sourceforge/bin/install-dns.sh ] && /usr/lib/sourceforge/bin/install-dns.sh configure 2>&1 > /dev/null
