include GNUmakefile.defaults
VERSION:=$(shell cat VERSION)

## Step 1: build files
all:
	$(MAKE) -C locale/


## Step 2: install files
install: all install-core install-plugins

# Directories required for stand-alone plugin install
install-base-dirs:
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/
	install -d -m 755 $(DESTDIR)$(config_path)/config.ini.d/
	install -d -m 755 $(DESTDIR)$(config_path)/httpd.conf.d/
	install -d -m 755 $(DESTDIR)$(config_path)/plugins/
	install -d -m 755 $(DESTDIR)$(sysconfdir)/cron.d/

# Initial values for generating secrets.ini
database_host=127.0.0.1
database_port=5432
database_name=fusionforge
database_user=fusionforge
database_password:=$(shell (head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -b-32)
session_key:=$(shell (head -c100 /dev/urandom; date +"%s:%N") | md5sum | cut -b-32)

install-config: install-base-dirs
#	Install initial configuration without overwriting
	cp -a --no-clobber etc/config.ini.d/* $(DESTDIR)$(config_path)/config.ini.d/
	if [ ! -e $(DESTDIR)$(config_path)/config.ini ]; then \
		sed templates/config.ini \
			-e 's,@source_path@,$(pkgdatadir),' \
			-e 's,@data_path@,$(data_path),' \
			-e 's,@config_path@,$(config_path),' \
			-e 's,@log_path@,$(log_path),' \
			> $(DESTDIR)$(config_path)/config.ini; \
	fi
	if [ ! -e $(DESTDIR)$(config_path)/config.ini.d/secrets.ini ]; then \
		sed templates/secrets.ini \
			-e 's,@database_host@,$(database_host),' \
			-e 's,@database_port@,$(database_port),' \
			-e 's,@database_name@,$(database_name),' \
			-e 's,@database_user@,$(database_user),' \
			-e 's,@database_password@,$(database_password),' \
			-e 's,@session_key@,$(session_key),' \
			> $(DESTDIR)$(config_path)/config.ini.d/secrets.ini; \
	fi
#	TODO: httpd.conf.d

install-core: install-base-dirs install-config
	cp -a VERSION bin common cronjobs db lib templates vendor www $(DESTDIR)$(pkgdatadir)/
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install
	ln -nfs $(pkgdatadir)/bin/forge_get_config $(DESTDIR)$(prefix)/bin/forge_get_config
	ln -nfs $(pkgdatadir)/bin/forge_run_jon $(DESTDIR)$(prefix)/bin/forge_run_job
	ln -nfs $(pkgdatadir)/bin/forge_run_plugin_job $(DESTDIR)$(prefix)/bin/forge_run_plugin_job

PLUGINS:=$(shell utils/list-enabled-plugins.sh)
PLUGINS_INSTALL_TARGETS=$(addprefix install-plugin-,$(PLUGINS))
install-plugins: $(PLUGINS_INSTALL_TARGETS)

install-plugin-%: plugins/% install-base-dirs
#	Source files
	install -d -m 755 $(DESTDIR)$(pkgdatadir)/plugins/$*
	cd plugins/$* && cp -a $$(ls | grep -v '^(etc|packaging)$$') $(DESTDIR)$(pkgdatadir)/plugins/
#	Initial config files
	cp -a plugins/$*/etc/$*.ini $(DESTDIR)$(config_path)/config.ini.d/; \
	if [ -d plugins/$*/etc/plugins/$* ]; then \
		cp -a --no-clobber plugins/$*/etc/plugins/$* $(DESTDIR)$(config_path)/plugins/; \
	fi
	if [ -d plugins/$*/etc/httpd.conf.d ]; then \
		cp -a --no-clobber plugins/$*/etc/httpd.conf.d/* $(DESTDIR)$(config_path)/httpd.conf.d/; \
	fi
	if [ -d plugins/$*/etc/cron.d ]; then \
		cp -a --no-clobber plugins/$*/etc/cron.d/* $(DESTDIR)$(sysconfdir)/cron.d/; \
	fi
#	Custom install
	if [ -e plugins/$*/GNUmakefile ]; then $(MAKE) -C plugins/$*/ install; fi

uninstall:
	rm -rf $(DESTDIR)$(pkgdatadir)
	$(MAKE) -C locale/ uninstall
#	Not removing $(config_path) and $(data_path) on purpose


## Step 3: configure & update system
post-install: post-install-user post-install-db post-install-plugins
post-install-user:
	if ! getent passwd $(user) >/dev/null; then useradd $(user) -s /bin/false -d $(data_path); fi
post-install-db:
	post-install.d/db-configure.sh
	post-install.d/db-populate.sh
	$(pkgdatadir)/bin/upgrade-db.php

PLUGINS_POST_INSTALL_TARGETS=$(addprefix post-install-plugin-,$(PLUGINS))
post-install-plugins: $(PLUGINS_POST_INSTALL_TARGETS)
post-install-plugin-%:
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

clean:
	$(MAKE) -C locale/ clean


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

version:
	@echo $(VERSION)
