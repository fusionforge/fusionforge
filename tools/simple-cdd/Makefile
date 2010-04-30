USRGRP=$(shell id -g)
DIST=lenny
DIST=squeeze

in_svn_repo:= $(wildcard .svn/)
ifeq ($(strip $(in_svn_repo)),)
in_bzr_repo:= $(wildcard ../../.bzr/)
ifeq ($(strip $(in_bzr_repo)),)
        svnrev=unknown
else
        svnrev=bzr$(shell bzr revno)
endif
else
        svnrev=$(shell LANG=C svn info 2>&1 | grep Revision | cut -d: -f2| sed 's/ //g')
endif

default: fusionforgemulti

fusionforge: passsetup /usr/bin/build-simple-cdd 
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist $(DIST) --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist $(DIST) 

fusionforgemulti: passsetup /usr/bin/build-simple-cdd
	build-simple-cdd -g --profiles fusionforge,x-basic,x-gnome,autopart --profiles-udeb-dist $(DIST) --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist $(DIST) --conf profiles/fusionforge.conf

fusionforgefr: clean /usr/bin/build-simple-cdd
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist $(DIST) --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist $(DIST) --locale fr_FR --force-preseed --keyboard fr-latin9

passsetup:
	[ ! -r /var/cache/debconf/passwords.dat ] && ( sudo chgrp $(USRGRP) /var/cache/debconf/passwords.dat ; sudo chmod g+r /var/cache/debconf/passwords.dat ) || true

/usr/bin/build-simple-cdd:
	sudo apt-get install simple-cdd

test:
	. profiles/fusionforge.conf ; env ; echo "cd_img=${cd_img}"
