#
# Regular cron jobs for the sourceforge-lists-mailman package
#

# Mailing-list creation
55 * * * * root [ -x /usr/lib/sourceforge/bin/create-mailing-lists.pl ] && /usr/lib/sourceforge/bin/create-mailing-lists.pl > /dev/null 2>&1
