#
# Regular cron jobs for the gforge-shell-ldap package
#

# CVS/user/group update
0 * * * * root [ -x /usr/lib/gforge/bin/home-dirs.sh ] && /usr/lib/gforge/bin/home-dirs.sh > /dev/null 2>&1
