VERSION=1.5.4
RPMBUILD=rpmbuild

clean:
	rm -fr /tmp/phpwiki-$(VERSION) /tmp/phpwiki-$(VERSION).tar.gz

/tmp/phpwiki-$(VERSION).tar.gz:
	rm -fr /tmp/phpwiki-$(VERSION)
	cp -a . /tmp/phpwiki-$(VERSION)
	cd /tmp; tar zcf /tmp/phpwiki-$(VERSION).tar.gz phpwiki-$(VERSION)
	rm -fr /tmp/phpwiki-$(VERSION)

rpm: /tmp/phpwiki-$(VERSION).tar.gz
	mkdir -p ~/rpmbuild/SOURCES/
	cp /tmp/phpwiki-$(VERSION).tar.gz ~/rpmbuild/SOURCES/
	$(RPMBUILD) -bb config/phpwiki.spec
