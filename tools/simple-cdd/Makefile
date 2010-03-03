USRGRP=$(shell id -g)
DIST=lenny
DIST=squeeze

default: fusionforge

fusionforge: profiles/fusionforge.conf  profiles/fusionforge.preseed
	false
	[ ! -r /var/cache/debconf/passwords.dat ] && ( sudo chgrp $(USRGRP) /var/cache/debconf/passwords.dat ; sudo chmod g+r /var/cache/debconf/passwords.dat ) || true
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist $(DIST) --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist $(DIST) 

fusionforgefr:
	build-simple-cdd -g --profiles fusionforge --auto-profiles fusionforge --profiles-udeb-dist $(DIST) --debian-mirror http://ftp.fr.debian.org/debian --security-mirror http://security.debian.org/debian-security --dist $(DIST) --locale fr_FR --force-preseed --keyboard fr-latin9

profiles/fusionforge.conf: profiles/templates/fusionforge.conf
	cat profiles/templates/fusionforge.conf | sed 's/%DIST%/$(DIST)/g' > $@


profiles/fusionforge.preseed: profiles/templates/fusionforge.preseed
	cat profiles/templates/fusionforge.preseed | sed 's/%DIST%/$(DIST)/g' > $@

clean:
	rm -f profiles/fusionforge.conf profiles/fusionforge.preseed
