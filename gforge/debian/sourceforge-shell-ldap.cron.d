#
# Regular cron jobs for the sourceforge-shell-ldap package
#

# CVS/user/group update
0 * * * * root [ -x /usr/lib/sourceforge/bin/update-user-group-cvs.sh ] && /usr/lib/sourceforge/bin/update-user-group-cvs.sh 2>&1 > /dev/null
