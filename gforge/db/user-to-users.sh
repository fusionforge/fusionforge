#!/bin/sh

#
#
#   this script goes through and converts the user table to users sitewide
#   this is being done for database portability reasons
#
#
find . -type f -exec perl -pi -e 's/(=|^|      |,| |"){1}user(,|\.|"| |$|      ){1}/\1users\2/gi' {} \;
