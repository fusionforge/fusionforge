include GNUmakefile.defaults
VERSION=$(shell cat VERSION)

## Step 1: build files
all:
	$(MAKE) -C locale/

version:
	@echo $(VERSION)


## Step 2: install files
install: all install-core install-plugins

base-dirs:
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/
	install -d -m 755 $(DESTDIR)$(config_path)/httpd.conf.d/
	install -d -m 755 $(DESTDIR)$(config_path)/plugins/
	install -d -m 755 $(DESTDIR)$(sysconfdir)/cron.d/

install-core: base-dirs
	cp -a VERSION bin common cronjobs db etc lib templates vendor www $(DESTDIR)$(pkgdatadir)/
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install

PLUGINS=$(shell utils/list-enabled-plugins.sh)
PLUGINS_INSTALL_TARGETS=$(addprefix install-plugin-,$(PLUGINS))
install-plugins: $(PLUGINS_INSTALL_TARGETS)

install-plugin-%: plugins/% base-dirs
#	Source files
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/$*
	cd plugins/$* && cp -a $$(ls | grep -v '^(etc|packaging)$$') $(DESTDIR)$(pkgdatadir)/plugins/
#	Config files
	if [ -d plugins/$*/etc/plugins/$* ]; then \
		cp -a plugins/$*/etc/plugins/$* $(DESTDIR)$(config_path)/plugins/; \
	fi
	if [ -d plugins/$*/etc/httpd.conf.d ]; then \
		cp -a plugins/$*/etc/httpd.conf.d/* $(DESTDIR)$(config_path)/httpd.conf.d/; \
	fi
	if [ -d plugins/$*/etc/cron.d ]; then \
		cp -a plugins/$*/etc/cron.d/* $(DESTDIR)$(sysconfdir)/cron.d/; \
	fi
#	Custom install
	if [ -e plugins/$*/GNUmakefile ]; then $(MAKE) -C plugins/$*/ install; fi


## Step 3: configure & update system
post-install: post-install-plugins
PLUGINS_POST_INSTALL_TARGETS=$(addprefix post-install-plugin-,$(PLUGINS))
post-install-plugins: $(PLUGINS_POST_INSTALL_TARGETS)
post-install-plugin-%: install-plugin-%
#	Run plugin-specific DB install/upgrade
	$(pkgdatadir)/bin/upgrade-db.php $*
#	Restart apache if there is some change in config
#	TODO: manage this with manage-apache-config.sh
	if [ -f $(config_path)/httpd.conf.d/plugin-$*.inc ]; then
	    service $(httpd_service) reload
	fi
#	Run plugin-specific install
	if [ -f $(pkgdatadir)/plugins/bin/install.sh ]; then
	    $(pkgdatadir)/plugins/bin/install.sh configure
	fi


## Developer tools

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
