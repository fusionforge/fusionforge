include GNUmakefile.defaults
VERSION:=$(shell cat VERSION)


## Step 1: build files
all:
	$(MAKE) -C locale/



## Step 2: install files
install: all install-base install-plugins

# Directories required for stand-alone plugin install
install-base-dirs:
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/plugins/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/post-install.d/
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
			-e 's,@apache_service@,$(apache_service),' \
			> $(DESTDIR)$(config_path)/config.ini; \
	fi

install-common: install-config install-plugin-authbuiltin
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install
	$(CP_R) VERSION bin common templates lib $(DESTDIR)$(pkgdatadir)/
	$(CP_R) cronjobs/scm $(DESTDIR)$(pkgdatadir)/cronjobs/  # no dedicated 'scm' package now
	$(CP_R) post-install.d/common $(DESTDIR)$(pkgdatadir)/post-install.d/
	$(CP_R) plugins/env.inc.php $(DESTDIR)$(pkgdatadir)/plugins/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_get_config $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_run_job $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_run_plugin_job $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_make_admin $(DESTDIR)$(prefix)/bin/
	ln -nfs $(pkgdatadir)/bin/forge_set_password $(DESTDIR)$(prefix)/bin/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(log_path)/

install-db-local: install-db
install-db: install-base-dirs
	$(CP_R) db $(DESTDIR)$(pkgdatadir)/
	$(CP_R) cronjobs/db  $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(CP_R) post-install.d/db  $(DESTDIR)$(pkgdatadir)/post-install.d/

install-web: install-base-dirs
	$(CP_R) vendor www $(DESTDIR)$(pkgdatadir)/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/docman/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/download/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/forum/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/tracker/
	$(CP_R) post-install.d/web  $(DESTDIR)$(pkgdatadir)/post-install.d/

install-shell: install-base-dirs
	$(CP_R) cronjobs/shell  $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(CP_R) post-install.d/shell  $(DESTDIR)$(pkgdatadir)/post-install.d/

install-mta-postfix: install-base-dirs
	$(CP_R) post-install.d/mta-postfix  $(DESTDIR)$(pkgdatadir)/post-install.d/

install-mta-exim4: install-base-dirs
	$(CP_R) post-install.d/mta-exim4  $(DESTDIR)$(pkgdatadir)/post-install.d/

install-lists: install-base-dirs
	$(CP_R) cronjobs/lists  $(DESTDIR)$(pkgdatadir)/cronjobs/

install-base: install-base-dirs install-common install-web install-db

PLUGINS=$(shell utils/list-valid-plugins.sh)
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
# not relying on 'prefix' for post-install (for no-install/dev setups)
fgc_source_path:=$(shell forge_get_config source_path 2>/dev/null)
post-install: post-install-base post-install-plugins
post-install-base:
	$(fgc_source_path)/post-install.d/common/common.sh
	$(fgc_source_path)/post-install.d/db/db.sh
	$(fgc_source_path)/post-install.d/web/configure.sh

post-install-shell:
	$(fgc_source_path)/post-install.d/shell/shell.sh configure

PLUGINS_INSTALLED=$(wildcard $(fgc_source_path)/plugins)
PLUGINS_POST_INSTALL_TARGETS=$(addprefix post-install-plugin-,$(PLUGINS_INSTALLED))
post-install-plugins: $(PLUGINS_POST_INSTALL_TARGETS)
post-install-plugin-%: $(fgc_source_path)/plugins/%/
	$(fgc_source_path)/post-install.d/common/plugin.sh $* configure


## TODO: make uninstall? make purge?


clean:
#	nothing for now
#	not removing pre-built .mo files in e.g. 'debclean'
dist-clean:
	$(MAKE) -C locale/ clean


## Developer tools

# tar.bz2 release
# depends: gettext tar
TAR_PREFIX=fusionforge-$(VERSION)
dist: all
	rm -rf $(TAR_PREFIX)/
	mkdir  $(TAR_PREFIX)/
	$(CP_R) $$(ls -A | grep -v '^\.git' | grep -v $(TAR_PREFIX)) $(TAR_PREFIX)/
	find $(TAR_PREFIX)/ -name '*~' -print0 | xargs -r0 rm
	tar cjf $(TAR_PREFIX).tar.bz2 $(TAR_PREFIX)/
	rm -rf $(TAR_PREFIX)/

version:
	@echo $(VERSION)
