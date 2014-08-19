include GNUmakefile.defaults
VERSION:=$(shell cat VERSION)

## Step 1: build files
all:
	$(MAKE) -C locale/


## Step 2: install files
install: all install-core install-plugins

# Directories required for stand-alone plugin install
install-base-dirs:
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/plugins/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(config_path)/config.ini.d/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(config_path)/httpd.conf.d/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(config_path)/plugins/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(sysconfdir)/cron.d/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(data_path)/

install-config: install-base-dirs
#	Install initial configuration (without overwriting)
#	Required for post-install scripts
	$(CP_R) --no-clobber etc/config.ini.d/* $(DESTDIR)$(config_path)/config.ini.d/
	if [ ! -e $(DESTDIR)$(config_path)/config.ini ]; then \
		sed etc/config.ini \
			-e 's,@source_path@,$(pkgdatadir),' \
			-e 's,@gettext_path@,$(localedir),' \
			-e 's,@data_path@,$(data_path),' \
			-e 's,@config_path@,$(config_path),' \
			-e 's,@log_path@,$(log_path),' \
			-e 's,@apache_user@,$(apache_user),' \
			-e 's,@apache_group@,$(apache_group),' \
			> $(DESTDIR)$(config_path)/config.ini; \
	fi

install-core: install-base-dirs install-config
	$(CP_R) VERSION bin common cronjobs db post-install.d templates vendor www lib $(DESTDIR)$(pkgdatadir)/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/www/plugins/
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install
	$(INSTALL) -d -m 00755 $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_get_config $(DESTDIR)$(prefix)/bin/forge_get_config
	ln -nfs $(pkgdatadir)/bin/forge_run_jon $(DESTDIR)$(prefix)/bin/forge_run_job
	ln -nfs $(pkgdatadir)/bin/forge_run_plugin_job $(DESTDIR)$(prefix)/bin/forge_run_plugin_job
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/docman/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/download/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/tracker/

PLUGINS:=$(shell utils/list-valid-plugins.sh)
PLUGINS_INSTALL_TARGETS=$(addprefix install-plugin-,$(PLUGINS))
install-plugins: $(PLUGINS_INSTALL_TARGETS)

install-plugin-%: plugins/% install-base-dirs
#	Source files
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/plugins/$*/
	cd plugins/$* && $(CP_R) $$(ls | grep -v '^(etc|packaging)$$') $(DESTDIR)$(pkgdatadir)/plugins/$*/
#	Initial config files
	$(CP_R) plugins/$*/etc/$*.ini $(DESTDIR)$(config_path)/config.ini.d/; \
	if [ -d plugins/$*/etc/plugins/$* ]; then \
		$(CP_R) --no-clobber plugins/$*/etc/plugins/$* $(DESTDIR)$(config_path)/plugins/; \
	fi
	if [ -d plugins/$*/etc/httpd.conf.d ]; then \
		$(CP_R) --no-clobber plugins/$*/etc/httpd.conf.d/* $(DESTDIR)$(config_path)/httpd.conf.d/; \
	fi
	if [ -d plugins/$*/etc/cron.d ]; then \
		$(CP_R) --no-clobber plugins/$*/etc/cron.d/* $(DESTDIR)$(sysconfdir)/cron.d/; \
	fi
#	Custom install
	if [ -e plugins/$*/GNUmakefile ]; then $(MAKE) -C plugins/$*/ install; fi

uninstall:
	rm -rf $(DESTDIR)$(pkgdatadir)
	$(MAKE) -C locale/ uninstall
#	Not removing $(config_path) and $(data_path) on purpose


## Step 3: configure & update system
post-install: post-install-core post-install-plugins
post-install-core:
	$(pkgdatadir)/post-install.d/core.sh

PLUGINS_INSTALLED:=$(shell ls $(pkgdatadir)/plugins)
PLUGINS_POST_INSTALL_TARGETS=$(addprefix post-install-plugin-,$(PLUGINS_INSTALLED))
post-install-plugins: $(PLUGINS_POST_INSTALL_TARGETS)
post-install-plugin-%: $(pkgdatadir)/plugins/%/
	$(pkgdatadir)/post-install.d/plugin.sh $*

clean:
	$(MAKE) -C locale/ clean


## Developer tools

# tar.bz2 release
# depends: gettext tar pbzip2
TAR_PREFIX=fusionforge-$(VERSION)
dist: all
	rm -rf $(TAR_PREFIX)/
	mkdir  $(TAR_PREFIX)/
	$(CP_R) $$(ls -A | grep -v .git | grep -v $(TAR_PREFIX)) $(TAR_PREFIX)/
	find $(TAR_PREFIX)/ -name '*~' -print0 | xargs -r0 rm
	tar cf $(TAR_PREFIX).tar.bz2 --use-compress-prog=pbzip2 $(TAR_PREFIX)/
	rm -rf $(TAR_PREFIX)/

version:
	@echo $(VERSION)
