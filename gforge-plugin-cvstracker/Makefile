DESTDIR=
PLUGIN=cvstracker

nothing:
	echo "What do you want I do?"

clean:
	find | grep "~$$"	

import:
	cp -rf /usr/lib/gforge/plugins/cvstracker/bin/* bin/ 
	cp -rf /usr/lib/gforge/plugins/cvstracker/include/* include/
	cp -rf /usr/lib/gforge/plugins/cvstracker/lib/* lib/
	cp -rf /usr/share/gforge/www/plugins/cvstracker/* www/

install:
	cp -r bin/* $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/bin/
	cp -r include/* $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/include/
	cp -r lib/* $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/lib/
	cp -r etc/* $(DESTDIR)/etc/gforge/plugins/$(PLUGIN)/
	cp -r www/* $(DESTDIR)/usr/share/gforge/www/plugins/$(PLUGIN)/
	cp -r debian/cron.d /etc/cron.d/gforge-plugin-$(PLUGIN)
	install -m 0644 httpd.conf $(DESTDIR)/etc/gforge/httpd.d/50$(PLUGIN)
	find $(DESTDIR)/ -name CVS -type d | xargs rm -rf
	find $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/bin/ -type f | xargs chmod 0755
	find $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/include/ -type f | xargs chmod 0644
	find $(DESTDIR)/usr/lib/gforge/plugins/$(PLUGIN)/lib/ -type f | xargs chmod 0644
	find $(DESTDIR)/etc/gforge/plugins/$(PLUGIN)/ -type f | xargs chmod 0644
	find $(DESTDIR)/usr/share/gforge/www/plugins/$(PLUGIN)/ -type f | xargs chmod 0644
	/usr/lib/gforge/bin/fill-in-the-blanks.pl \
	$(DESTDIR)/etc/gforge/plugins/cvstracker/cvstracker.conf.tmpl \
	$(DESTDIR)/etc/gforge/plugins/cvstracker/cvstracker.conf \
        $(DESTDIR)/etc/gforge/gforge.conf
	chmod 0644 $(DESTDIR)/etc/gforge/plugins/cvstracker/cvstracker.conf
	$(DESTDIR)/usr/lib/gforge/plugins/cvstracker/bin/db-upgrade.pl
	$(DESTDIR)/usr/lib/gforge/bin/register-plugin cvstracker "cvstracker"	

uninstall:
	$(DESTDIR)/usr/lib/gforge/plugins/cvstracker/bin/db-delete.pl
	$(DESTDIR)/usr/lib/gforge/bin/unregister-plugin cvstracker "cvstracker"  
	rm -f /etc/cron.d/gforge-plugin-$(PLUGIN)
