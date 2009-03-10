USRGRP=$(shell id -g)

default: fusionforge

fusionforge:
	[ ! -r /var/cache/debconf/passwords.dat ] && ( sudo chgrp $(USRGRP) /var/cache/debconf/passwords.dat ; sudo chmod g+r /var/cache/debconf/passwords.dat ) || true
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist lenny --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist lenny 

fusionforgefr:
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist lenny --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist lenny --locale fr_FR --force-preseed --keyboard fr-latin9
