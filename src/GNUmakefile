include GNUmakefile.defaults
VERSION=$(shell cat VERSION)

all:
	$(MAKE) -C locale/

version:
	@echo $(VERSION)

install: all install-core install-plugins

install-core:
	install -d -m 755 $(DESTDIR)/$(PKGDATADIR)/
	install -d -m 755 $(DESTDIR)/$(PKGDATADIR)/plugins/
	cp -a VERSION bin common cronjobs db etc lib vendor www $(DESTDIR)/$(PKGDATADIR)/
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install

PLUGINS=$(shell utils/list-enabled-plugins.sh)
PLUGINS_INSTALL_TARGETS=$(addprefix install-plugin-,$(PLUGINS))
install-plugins: $(PLUGINS_INSTALL_TARGETS)

install-plugin-%: plugins/%
	cp -a plugins/$* $(DESTDIR)/$(PKGDATADIR)/plugins/; \
	if [ -e "plugins/$*/GNUmakefile" ]; then $(MAKE) -C plugins/$*/ install; fi

# tar.bz2 release
# depends: gettext tar bzip2
TAR_PREFIX=fusionforge-$(VERSION)
dist: all
	rm -rf $(TAR_PREFIX)/
	mkdir  $(TAR_PREFIX)/
	cp -a $$(ls -A | grep -v .git | grep -v $(TAR_PREFIX)) $(TAR_PREFIX)/
	find $(TAR_PREFIX)/ -name '*~' -print0 | xargs -r0 rm
	tar cjf $(TAR_PREFIX).tar.bz2 $(TAR_PREFIX)/
	rm -rf $(TAR_PREFIX)/
 clean:
	$(MAKE) -C locale/ clean


# TODO: not sure this belongs here
rpm: dist
	mkdir -p build/SOURCES/
	mv $(TAR_PREFIX).tar.bz2 build/SOURCES/
	chown root: build/SOURCES/$(TAR_PREFIX).tar.bz2
	rpmbuild fusionforge.spec --define "_topdir $(pwd)/build" -bb

deb:
	debuild
