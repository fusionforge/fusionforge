#!/bin/sh

## The order these scripts are run in is CRITICAL
## DO NOT change their order. Add before, or add after
##
/usr/lib/sourceforge/bin/db_stats_prepare.pl $*
# /usr/lib/sourceforge/bin/db_stats_cvs_history.pl $*
/usr/lib/sourceforge/bin/db_stats_projects_nightly.pl $*
##
## END order sensitive section
##

/usr/lib/sourceforge/bin/db_stats_site_nightly.pl $*
