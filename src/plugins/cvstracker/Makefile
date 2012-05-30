DESTDIR=
PLUGIN=cvstracker

nothing:
	echo "What do you want I do?"

clean:
	find | grep "~$$"	

import:
	cp -rf /usr/share/gforge/plugins/cvstracker/bin/* bin/ 
	cp -rf /usr/share/gforge/plugins/cvstracker/common/* common/
	cp -rf /usr/share/gforge/plugins/cvstracker/db/* db/
	cp -rf /usr/share/gforge/www/plugins/cvstracker/* www/

install:
	cp -r bin/* $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/bin/
	cp -r common/* $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/common/
	cp -r db/* $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/db/
	cp -r etc/* $(DESTDIR)/etc/gforge/plugins/$(PLUGIN)/
	cp -r www/* $(DESTDIR)/usr/share/gforge/www/plugins/$(PLUGIN)/
	cp -r debian/cron.d /etc/cron.d/gforge-plugin-$(PLUGIN)
	install -m 0644 httpd.conf $(DESTDIR)/etc/gforge/httpd.d/50$(PLUGIN)
	find $(DESTDIR)/ -name CVS -type d | xargs rm -rf
	find $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/bin/ -type f | xargs chmod 0755
	find $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/common/ -type f | xargs chmod 0644
	find $(DESTDIR)/usr/share/gforge/plugins/$(PLUGIN)/db/ -type f | xargs chmod 0644
	find $(DESTDIR)/etc/gforge/plugins/$(PLUGIN)/ -type f | xargs chmod 0644
	find $(DESTDIR)/usr/share/gforge/www/plugins/$(PLUGIN)/ -type f | xargs chmod 0644
	chmod 0644 $(DESTDIR)/etc/gforge/plugins/cvstracker/cvstracker.conf
	$(DESTDIR)/usr/share/gforge/plugins/cvstracker/bin/db-upgrade.pl
	$(DESTDIR)/usr/share/gforge/bin/register-plugin cvstracker "cvstracker"	

uninstall:
	$(DESTDIR)/usr/share/gforge/plugins/cvstracker/bin/db-delete.pl
	$(DESTDIR)/usr/share/gforge/bin/unregister-plugin cvstracker "cvstracker"  
	rm -f /etc/cron.d/gforge-plugin-$(PLUGIN)
