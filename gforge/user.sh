#!/bin/sh

#
##
#   this script goes through and converts the user table to users sitewide
#   #   this is being done for database portability reasons
#   #
#   #
find . -type f -exec perl -pi -e 's/util_send_message/util_send_message/gi' {} \;

