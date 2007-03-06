#
# Regular cron jobs for the gforge-shell-postgresql package
#

# SCM/user/group update
0 * * * * root [ -x /usr/lib/gforge/bin/update-user-group-cvs.sh ] && /usr/lib/gforge/bin/update-user-group-cvs.sh > /dev/null 2>&1
