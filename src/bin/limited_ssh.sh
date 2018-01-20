#!/bin/bash

# -------------------------------------------------------------------
# Copyright (C) 2006 by Intevation GmbH
# Author(s):
# Sascha Wilde <wilde@intevation.de>
# Mathias Gebbe <mgebbe@intevation.de>

# Copyright 2018, Franck Villaume - TrivialDev

# This program is free software under the GNU GPL (>=v2)
# Read the file COPYING coming with the software for details.
# -------------------------------------------------------------------
# Only allow well defined actions...

# WARNING:
# This script does by no means enforce hard security policies. As
# long as users are allowed to install PHP scripts on this server it
# would be pointless anyway..!

COMMAND=`echo "$SSH_ORIGINAL_COMMAND" | cut -f1 -d' '`

RSYNC=/usr/bin/rsync
RSYNC_BASE_DIR=$(forge_get_config groupdir_prefix)

SVN=/usr/bin/svnserve
SVN_BASE_DIR=$(forge_get_config repos_path scmsvn)

HG=/usr/bin/hg
HG_BASE_DIR=$(forge_get_config repos_path scmhg)

GIT=/usr/bin/git
GIT_BASE_DIR=$(forge_get_config repos_path scmgit)

LOG="logger -t limited_ssh -p local0.info"
CHAR_WHITELIST="\\\\a-zA-Z0-9 \"\!\?~+()[]{}'/@%,._-"

normalize-path()
# $1 => base-path
# $2 => path relative to basepath
{
  NEWPATH=`readlink -f "$1/$2"` || \
    NEWPATH=`readlink -f "$1"/$(dirname "$2")`/$(basename "$2") || \
    exit 1
  TRAIL=`echo $2 | sed 's/.*\(.\)$/\1/' | tr -cd /`
  NEWPATH="$NEWPATH$TRAIL"
  echo "$NEWPATH" | grep "$1" || exit 1
}

fail()
{
  $LOG -s "FAILED: $1"
  exit 1
}

$LOG "Called by $USER with: $SSH_ORIGINAL_COMMAND"
case "$COMMAND" in
  rsync)
    # Remove the -L] option
   SSH_ORIGINAL_COMMAND=`echo "$SSH_ORIGINAL_COMMAND" \
           | sed -e 's/ \(-.*\)L/ \1/'`

    # check for evil characters:
    [ -z `echo "$SSH_ORIGINAL_COMMAND" | \
      tr -d "$CHAR_WHITELIST"` ] || \
      fail "illegal characters in command"

    # extract options and destination path and last char from path
    roptions=`echo "$SSH_ORIGINAL_COMMAND" \
              | sed -n 's/rsync \([^.]*\)\([^/]*\).*/\1\2/p'`

    rpath=`echo "$SSH_ORIGINAL_COMMAND" \
           | sed -n 's/rsync [^.]*.*[^.]* \.[ \t]*\(.*\)/\1/p'`

    $LOG "option: $roptions and path: $rpath"
    newpath=$(normalize-path "$RSYNC_BASE_DIR" "$rpath") \
      || fail "illegal path \"$rpath\""

    # don't let them fool us:
    echo "$roptions" | grep -q -- '--server' || fail "illegal operation"

    EXEC="$RSYNC $roptions $newpath"
    ;;
  svnserve)
    EXEC="$SVN -t -r $SVN_BASE_DIR"
    ;;
  hg)
    # do hg ssh
    # check if all starts with hg -R "/"hg/ and test if this exist under $HG_BASE_DIR
    hpath=`echo "$SSH_ORIGINAL_COMMAND" | \
           sed -n 's/hg -R \/*hg\/\([^ ]*\).*/\1/p'`
    $LOG "hpath: ".$HG_BASE_DIR/$hpath
    if [ "$hpath" == "" ] || [ ! -d "$HG_BASE_DIR/$hpath" ]
       then
          fail "Repository not found"
    fi
    EXEC="$HG -R $HG_BASE_DIR/$hpath serve --stdio"
    ;;
  git)
    # do git ssh
    gpath=`echo "$SSH_ORIGINAL_COMMAND"  | cut -d' ' -f2 | sed -e "s/'//g"`
    $LOG "command: $COMMAND"
    EXEC="$COMMAND $GIT_BASE_DIR/$gpath"

    ;;
   *)
    fail "operation not permitted"
    ;;
esac

$LOG "Executing: $EXEC"
eval exec $EXEC
