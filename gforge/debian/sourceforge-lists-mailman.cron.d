#
# Regular cron jobs for the sourceforge-lists-mailman package
#

# Mailing-list creation
55 * * * * root [ -x /usr/lib/sourceforge/bin/create-mailing-lists.pl ] && /usr/lib/sourceforge/bin/create-mailing-lists.pl 2>&1 > /dev/null
