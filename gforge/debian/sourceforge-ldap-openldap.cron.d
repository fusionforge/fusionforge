#
# Regular cron jobs for the sourceforge-ldap-openldap package
#

# CVS/user/group update
0 * * * * root [ -x /usr/lib/sourceforge/bin/update-user-group-cvs.sh ] && /usr/lib/sourceforge/bin/update-user-group-cvs.sh > /dev/null 2>&1
