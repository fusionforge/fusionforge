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
./stats_ftp_logparse.pl $*
./stats_http_logparse.pl $*
./stats_sum.pl $*

