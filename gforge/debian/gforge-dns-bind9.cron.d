#
# Regular cron jobs for the gforge-dns-bind9 package
#

# DNS Update
0 * * * * root [ -f /var/lib/gforge/bind/dns.head ] && [ -x /usr/lib/gforge/bin/dns_conf.pl ] && /usr/lib/gforge/bin/dns_conf.pl &&  /usr/sbin/invoke-rc.d bind9 reload  &>/dev/null
