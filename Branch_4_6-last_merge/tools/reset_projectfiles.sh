if [ "x$1" != "x" ]
then
	rm -rfi /var/lib/gforge/chroot/cvsroot/$1
	rm -rfi /var/lib/gforge/chroot/ftproot/pub/$1
	rm -rfi /var/lib/gforge/chroot/home/users/anoncvs_$1
	rm -fi /var/lib/gforge/cvstarballs/$1-cvsroot.tar.gz
	rm -rfi /var/lib/gforge/download/$1
	rm -fi /var/lib/gforge/tmp/$1.tar.gz
	rm -fi /var/lib/mailman/archives/public/$1-*
	rm -fi /var/lib/mailman/archives/private/$1-*
	rm -fi /var/lib/mailman/lists/$1-*/*
	rmdir /var/lib/mailman/lists/$1-*
else
	echo Usage: $0 projectname
	exit 1
fi
