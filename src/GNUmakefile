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
#	TODO: httpd.conf.d

install-core: install-base-dirs install-config
	cp -a VERSION bin common cronjobs db lib templates vendor www $(DESTDIR)$(pkgdatadir)/
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install

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
post-install: post-install-plugins
	if ! getent passwd $(user); then useradd $(user) -s /bin/false -d $(data_path); fi
	-service postgresql initdb  # RH
	service postgresql start
	su - postgres -c 'createuser -SdR fusionforge'
	su - postgres -c "createdb --template template0 --encoding UNICODE $(database_name)"
	if su - postgres -c "createlang -l fusionforge" | grep plpgsql > /dev/null; then \
		su - postgres -c "createlang plpgsql $(database_name)"; \
	fi
#	TODO: use DB credentials rather than assuming user==database_user
#	su - postgres -c "psql -c \"ALTER ROLE fusionforge PASSWORD 'fusionforge'\""
	su $(database_user) -s /bin/bash -c 'psql $(database_name) < $(pkgdatadir)/db/1-fusionforge-init.sql'
	(echo "[core]"; echo "database_user=$(database_user)") > $(config_path)/config.ini.d/secrets.ini
	$(pkgdatadir)/bin/upgrade-db.php

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
