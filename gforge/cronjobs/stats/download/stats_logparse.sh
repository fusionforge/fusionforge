#!/bin/sh
#/**
#  *
#  * SourceForge: Breaking Down the Barriers to Open Source Development
#  * Copyright 1999-2001 (c) VA Linux Systems
#  * http://sourceforge.net
#  *
#  * @version   $Id$
#  *
#  */


## parse each logfile set 
/usr/lib/sourceforge/bin/stats_ftp_logparse.pl $*
/usr/lib/sourceforge/bin/stats_http_logparse.pl $*
/usr/lib/sourceforge/bin/stats_sum.pl $*

