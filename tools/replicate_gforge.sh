#! /bin/sh
# This is the server you want to replicate
# You must have ssh root@servertoreplicate working
OLDSFSERVER=servertoreplicate
# This is the db name on the remote server sourceforge if this is an old sf 2.5/2.6 server
# gforge for a more recent gforge server
#OLDDB=sourceforge
OLDDB=gforge
export OLDDB
NEWDB=gforge
export NEWDB
# By default I do nothing 
# Sync the db
DO_DB=0
# Get remote db data
GET_REMOTE=0
# Sync remote files
SYNC_FILE=0
export SYNC_FILE

if [ $DO_DB == 1 ] 
then 
	if [ GET_REMOTE == 1 ]
	then
		ssh root@$OLDSFSERVER "su -s /bin/sh $OLDDB -c \"/usr/lib/postgresql/bin/pg_dump $OLDDB\"" > /root/db_dump_$OLDDB
		[ -f /root/db_dump_$OLDDB ] && echo "Adjusting /root/db_dump.tar" && perl -pi -e "s/connect - sourceforge/connect - gforge/" /root/db_dump_$OLDDB
	fi
	/usr/lib/gforge/bin/install-db.sh restore /root/db_dump_$OLDDB
	/usr/lib/gforge/bin/db-upgrade.pl
fi

if [ $SYNC_FILE == 1 ] 
then 
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/download /var/lib/gforge/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/ftp /var/lib/gforge/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/tmp /var/lib/gforge/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/cvstarballs /var/lib/gforge/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/chroot/home /var/lib/gforge/chroot/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/lib/$OLDDB/chroot/cvsroot /var/lib/gforge/chroot/
	rsync -avz --delete -e ssh --progress root@$OLDSFSERVER:/var/log/$OLDDB/ /var/log/gforge/
fi
