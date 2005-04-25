#
# Regular cron jobs for the gforge-shell-ldap package
#

# CVS/user/group update
30 * * * * root [ -x /usr/lib/gforge/bin/install-ldap.sh ] && /usr/lib/gforge/bin/install-ldap.sh update > /dev/null 2>&1
