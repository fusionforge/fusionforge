#
# Regular cron jobs for the gforge-lists-mailman package
#

# Mailing-list creation
55 * * * * root [ -x /usr/lib/gforge/bin/create-mailing-lists.pl ] && /usr/lib/gforge/bin/create-mailing-lists.pl > /dev/null 2>&1
