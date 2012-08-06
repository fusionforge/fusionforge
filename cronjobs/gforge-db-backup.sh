#!/bin/sh

# This script does personal backups to a rsync backup server. You will end up
# with a 7 day rotating incremental backup. The incrementals will go
# into subdirectories named after the day of the week, and the current
# full backup goes into a directory called "current"

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

# directory to backup
BDIR="/var/lib/gforge/chroot /var/lib/mailman /etc"
PATTERNS="mailman postgresql exim4 gforge"

DEST="/var/lib/gforge/backup"

# BACKUPDIR=`date --date yesterday +%A`
BACKUPDIR=`date +%A`
# BACKUPDIR=`date --date tomorrow +%A`
# BACKUPDIR=`date --date "2 days" +%A`
OPTS="--force --ignore-errors --delete --backup --backup-dir=$DEST/$BACKUPDIR -a"

[ ! -d ${DEST} ] && mkdir ${DEST}
[ ! -d ${DEST}/postgres ] && mkdir ${DEST}/postgres
[ ! -d ${DEST}/debconf ] && mkdir ${DEST}/debconf

echo "Backuping data from $BDIR"
# the following line clears the last weeks incremental directory
[ -d $DEST/emptydir ] || mkdir $DEST/emptydir
rsync --delete -a $DEST/emptydir/ $DEST/$BACKUPDIR/
rmdir $DEST/emptydir

# now the actual transfer
rsync $OPTS $BDIR $DEST/current

export FUSIONFORGE_NO_PLUGINS=true
COMPRESSOR=$(/usr/share/gforge/bin/forge_get_compressor)
EXTENSION=$(/usr/share/gforge/bin/forge_get_compressed_extension)

echo "Dumping database"
su -s /bin/bash postgres -c "pg_dump -F c -d gforge" | $COMPRESSOR > ${DEST}/postgres/gforge.dump${EXTENSION}

echo "Dumping debconf keys"
for PAT in $PATTERNS
do
        debconf-copydb configdb stdout -c Name:stdout -c Driver:Pipe -c InFd:none \
                --pattern='^'${PAT}'/' > ${DEST}/debconf/${PAT}.txt
        chmod 0700 ${DEST}/debconf/${PAT}.txt
done
