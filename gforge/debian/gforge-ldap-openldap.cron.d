#
# Regular cron jobs for the gforge-ldap-openldap package
#

# CVS/user/group update
0 * * * * root [ -x /usr/lib/gforge/bin/update-user-group-cvs.sh ] && /usr/lib/gforge/bin/update-user-group-cvs.sh > /dev/null 2>&1
