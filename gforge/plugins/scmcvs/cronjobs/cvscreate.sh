#!/bin/sh
echo ""
echo "CVS Repository Tool"
echo "Portions (c)1999 SourceForge Development Team"
echo "The rest (c)2004 Guillaume Smet"
echo "Released under the GPL, 1999"
echo ""
echo ""

# if no arguments, print out help screen
if test $# -lt 4; then 
	echo "usage:"
	echo "  cvscreate.sh [repositoryname] [groupname] [isanonymousenabled] [ispserverenabled]"
	echo ""
	exit 1 
fi

repositoryname=$1
repositorypath=/cvsroot/$1
groupname=$2
isanonymousenabled=$3
ispserverenabled=$4

function setPserverAccess() {
	writers=""
	readers=""
	passwd=""
	if [[ $isanonymousenabled -eq 1 && $ispserverenabled -eq 1 ]] ; then
		readers="anonymous::anonymous"
		passwd="anonymous:\$1\$0H\$2/LSjjwDfsSA0gaDYY5Df/:anonymous"
	fi
	echo $writers > $repositorypath/CVSROOT/writers
	echo $readers > $repositorypath/CVSROOT/readers
	echo $passwd > $repositorypath/CVSROOT/passwd
}

function setRepositoryAccess() {
	if [ $isanonymousenabled -eq 1 ] ; then
		# make the repository user and group writable and world readable
		chmod 2775 $repositorypath
	else
		# make the repository user and group writable but not accessible to other users
		chmod 2770 $repositorypath
	fi
}

function createRepository() {
	mkdir $repositorypath
	mkdir -p /cvsroot/cvs-locks/$repositoryname
	chmod 755 /cvsroot/cvs-locks
	chmod 3777 /cvsroot/cvs-locks/$repositoryname
	setRepositoryAccess
	cvs -d$repositorypath init
	setPserverAccess
	echo "SystemAuth=yes" > $repositorypath/CVSROOT/config
	echo "LockDir=/cvsroot/cvs-locks/$repositoryname" >> $repositorypath/CVSROOT/config
	chmod 444 $repositorypath/CVSROOT/config
	echo "" > $repositorypath/CVSROOT/val-tags
	chmod 664 $repositorypath/CVSROOT/val-tags
	chown -R nobody:$groupname $repositorypath
}

if [ -d $repositorypath ] ; then
	echo "$repositoryname already exists."
	setRepositoryAccess
	setPserverAccess
	echo ""
else
	createRepository
fi
