include GNUmakefile.defaults
VERSION=$(shell php -r 'require_once "common/include/env.inc.php"; require_once "common/include/FusionForge.class.php"; echo (new FusionForge())->software_version;')

all:
	make -C locale/

version:
	@echo $(VERSION)

install:
	install -d -m 755 $(DESTDIR)/$(PKGDATADIR)/
	cp -a bin common cronjobs db etc lib plugins utils vendor www $(DESTDIR)/$(PKGDATADIR)/
	make -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install

# tar.bz2 release
TAR_PREFIX=fusionforge-$(VERSION)
dist: all
	rm -rf $(TAR_PREFIX)/
	mkdir  $(TAR_PREFIX)/
	cp -a $$(ls -A | grep -v .git) $(TAR_PREFIX)/
	find $(TAR_PREFIX)/ -name '*~' -print0 | xargs -r0 rm
	tar cjf $(TAR_PREFIX).tar.bz2 $(TAR_PREFIX)/
	rm -rf $(TAR_PREFIX)/
 clean:
	make -C locale/ clean
