#! /bin/sh

# This script is intended to be used to mirror two gforge
# installations both based on the debian packages available
# at gforge.grazian.org

# The purpose of this script is to have a hot-standby gforge
# server ready to go if something bad happens to the primary
# system.

# NOTE WELL:  You need to alter your /etc/php4/apache2/php.ini
# config as follows:
# -pgsql.auto_reset_persistent = Off 
# +pgsql.auto_reset_persistent = On
# to avoid getting errors when you try to use the mirror.
# Author: David Partain

# SRCSERVER is the server you want to replicate from
# DSTSERVER is the server to which you are replicating
SRCSERVER=the.machine.you.want.to.mirror # e.g., gforge.company.com
DSTSERVER=the.mirror.machine             # e.g., gforge2.company.com
export SRCSERVER DSTSERVER

# Some global flags...  Customize as you wish
DO_DB=1		# sync DB. turn off with -d
DO_MAILMAN=1	# sync mailman. turn off with -m
DO_SVN=1	# sync subversion. turn off with -s
DO_CUSTOM=1	# sync custom stuff.  turn off with -z
# USE_SSH_ADD=0	# if you want to try to do things password-less (not there yet)
VERBOSE=0	# this set to 1 if you use -v, prints a bit to stdout
SEND_MAIL=1	# if you want a report sent. Set next value
REPORTEE=gforge-admin@invalid.email.address	# your email address

export DO_DB DO_MAILMAN DO_SVN DO_CUSTOM USE_SSH_ADD VERBOSE SEND_MAIL REPORTEE

LOGFILE=/tmp/gforge-mirror-log.$$.$RANDOM
GFORGEDIR=/var/lib/gforge
RSYNC_CMD='rsync -avz --delete -e ssh' # I find --progress annoying
RSYNC_RSH=ssh
DBNAME=gforge
DATE=`date +%F-%H-%M-%S`
DBDUMPFILE=/root/gforgedump.$RANDOM.$DATE
MAILMANDIR=/var/lib/mailman
export LOGFILE GFORGEDIR RSYNC_CMD RSYNC_RSH DBNAME MAILMANDIR DBDUMPFILE

################################################################
usage()
{
cat << _EOF_
usage: `basename $0` [-v] [-h]
	-v              : print some information to stdout
	-l              : do not send mail report
	-d              : sync the database
	-s              : do not sync subversion
	-m              : do not sync mailman
	-z              : do not sync local files
	-r emailaddress : send mail report to this email address
	-h              : prints this usage information
_EOF_
exit 1;
}

log()
{
  echo $@ >>  $LOGFILE
  if [ $VERBOSE == 1 ]
  then
    echo $@
  fi
}

if [ -f $LOGFILE ]
then
  echo "Huh?  $LOGFILE exists?  Now I am really confused."
  exit 1
fi

while [ $# -gt 0 ]; do
  case $1 in
    -h) usage;;
    -v) shift; VERBOSE=1;;
    -l) shift; SEND_MAIL=0;;
    -d) shift; DO_DB=0;;
    -s) shift; DO_SVN=0;;
    -z) shift; DO_CUSTOM=0;;
    -m) shift; DO_MAILMAN=0;;
    -r) shift
        REPORTEE=$1
        if [ -n "$REPORTEE" ]
	then
	  log "Sending mail to $REPORTEE"
	  shift
	else
	  usage
	fi;;
    *) usage;;
  esac;
done


################################################################
# Careful where you run this...
if [ `hostname --fqdn` != $DSTSERVER ]
then
	echo "Do not run this on `hostname --fqdn`!"
	echo "Only run this script on $DSTSERVER!"
	exit 1
fi

# You _really_ want all of this to happen without passwords.  Get
# things set up for doing rsync over ssh without passwords, but
# that's beyond the scope of this script. 
# Maybe something like this?
# don't really think this will work...
# if [ $USE_SSH_ADD == 1 ]
# then
# exec ssh-agent /bin/bash
# ssh-add
# fi

log "gforge mirror sync run on"
log `date`

################################################################
# gforge database stuff
# Note: user sessions are deleted from the database.
if [ $DO_DB == 1 ]
then
  log "################################################################"
  log "Replicating gforge database"

  ssh root@$SRCSERVER "su -s /bin/sh $DBNAME -c \"/usr/lib/postgresql/bin/pg_dump $DBNAME\"" > $DBDUMPFILE
  log "Backup of original db dump kept in $DBDUMPFILE.orig"
  [ -f $DBDUMPFILE ] && /bin/cp $DBDUMPFILE $DBDUMPFILE.orig
  [ -f $DBDUMPFILE ] && log "Adjusting $DBDUMPFILE" && perl -pi -e "s/connect - sourceforge/connect - gforge/" $DBDUMPFILE
  [ -f $DBDUMPFILE ] && log "Adjusting database for new site" && perl -pi -e "s/$SRCSERVER/$DSTSERVER/" $DBDUMPFILE
  log "running /usr/lib/gforge/bin/install-db.sh restore $DBDUMPFILE"
  /usr/lib/gforge/bin/install-db.sh restore $DBDUMPFILE >>$LOGFILE 2>&1
  log "su -s /bin/sh gforge -c /usr/lib/gforge/bin/db-upgrade.pl"
  su -s /bin/sh gforge -c /usr/lib/gforge/bin/db-upgrade.pl >>$LOGFILE 2>&1
  log "Removing user sessions from database"
  su -s /bin/sh - $DBNAME psql $DBNAME >> $LOGFILE 2>&1 <<-END
delete from user_session ;
END
fi

################################################################
# gforge-specific directories
log "################################################################"
log "Replicating gforge-specific directories"

log "rsync'ing $GFORGEDIR/download"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/download $GFORGEDIR/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/ftp"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/ftp $GFORGEDIR/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/tmp"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/tmp $GFORGEDIR/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/scmtarballs"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/scmtarballs $GFORGEDIR/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/scmsnapshots"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/scmsnapshots $GFORGEDIR/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/chroot/home"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/chroot/home $GFORGEDIR/chroot/ >>$LOGFILE 2>&1
log "rsync'ing $GFORGEDIR/chroot/cvsroot"
$RSYNC_CMD root@$SRCSERVER:$GFORGEDIR/chroot/cvsroot $GFORGEDIR/chroot/ >>$LOGFILE 2>&1
log "rsync'ing /var/log/gforge/"
$RSYNC_CMD root@$SRCSERVER:/var/log/gforge/ /var/log/gforge/ >>$LOGFILE 2>&1

################################################################
# Mailman replication
if [ $DO_MAILMAN == 1 ]
then
  log "################################################################"
  log "Replicating mailman directories"
  log "rsync'ing $MAILMANDIR/archives/"
  $RSYNC_CMD root@$SRCSERVER:$MAILMANDIR/archives/ $MAILMANDIR/archives/ >>$LOGFILE 2>&1
  log "rsync'ing $MAILMANDIR/data/"
  $RSYNC_CMD root@$SRCSERVER:$MAILMANDIR/data/ $MAILMANDIR/data/ >>$LOGFILE 2>&1
  log "rsync'ing $MAILMANDIR/lists/"
  $RSYNC_CMD root@$SRCSERVER:$MAILMANDIR/lists/ $MAILMANDIR/lists/ >>$LOGFILE 2>&1
  # Don't know if I should do this or not....
  # log "rsync'ing $MAILMANDIR/qfiles/"
  # $RSYNC_CMD root@$SRCSERVER:$MAILMANDIR/qfiles/ $MAILMANDIR/qfiles/ >>$LOGFILE 2>&1
fi

################################################################
# Subversion - we don't want the transactions directory, which are
# commits that are currently underway.  This seems relatively safe.
# See http://web.mit.edu/ghudson/info/fsfs for some relevant info.
if [ $DO_SVN == 1 ]
  then
  log "################################################################"
  log "Replicating subversion directories"
  log "rsync'ing $GFORGEDIR/chroot/svnroot/"
  $RSYNC_CMD --exclude=transactions root@$SRCSERVER:$GFORGEDIR/chroot/svnroot/ $GFORGEDIR/chroot/svnroot/ >>$LOGFILE 2>&1
fi

################################################################
# Local files - If you have customized things on the source
# gforge that you need to sync, put them here
if [ $DO_CUSTOM == 1 ]
  then
  log "################################################################"
  log "Replicating local customized directories"
  log "rsync'ing /usr/share/gforge/www/tools/"
  $RSYNC_CMD root@$SRCSERVER:/usr/share/gforge/www/tools/ /usr/share/gforge/www/tools/ >>$LOGFILE 2>&1
  log "rsync'ing /usr/share/gforge/www/static/"
  $RSYNC_CMD root@$SRCSERVER:/usr/share/gforge/www/static/ /usr/share/gforge/www/static/ >>$LOGFILE 2>&1
fi

################################################################
if [ $SEND_MAIL == 1 ]
  then
  if [ -n "$REPORTEE" ]
    then
    /usr/bin/Mail -s "gforge mirror log" $REPORTEE < $LOGFILE
    /bin/rm -f $LOGFILE
  else
    echo "Cannot send report - set REPORTEE in script"
    echo "Delete $LOGFILE manually"
  fi
else
  echo "Log of this mirroring run is in $LOGFILE"
fi
