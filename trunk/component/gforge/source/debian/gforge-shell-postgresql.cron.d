#
# Regular cron jobs for the gforge-shell-postgresql package
#

# SCM/user/group update
0 * * * * root [ -x /usr/lib/gforge/bin/update-user-group-ssh.sh ] && /usr/lib/gforge/bin/update-user-group-ssh.sh > /dev/null 2>&1
