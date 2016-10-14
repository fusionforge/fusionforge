# FusionForge build system
#
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

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
	$(INSTALL) -d -m 00755 $(DESTDIR)$(sysconfdir)/logrotate.d/
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
			-e 's,@ssh_service@,$(ssh_service),' \
			> $(DESTDIR)$(config_path)/config.ini; \
	fi

install-common: install-config install-plugin-authbuiltin install-plugin-globalactivity
	$(MAKE) -C locale DESTDIR=$(DESTDIR) PREFIX=$(PREFIX) install
	$(CP_R) VERSION bin common templates $(DESTDIR)$(pkgdatadir)/
	chmod 755 $(DESTDIR)$(pkgdatadir)/bin/ssh_akc.php
	$(INSTALL) -d -m 00755 $(DESTDIR)$(config_path)/custom/
	$(CP_R) post-install.d/common $(DESTDIR)$(pkgdatadir)/post-install.d/
	$(CP_R) plugins/env.inc.php $(DESTDIR)$(pkgdatadir)/plugins/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(bindir)
	ln -nfs $(pkgdatadir)/bin/forge_get_config $(DESTDIR)$(bindir)
	ln -nfs $(pkgdatadir)/bin/forge_run_job $(DESTDIR)$(bindir)
	ln -nfs $(pkgdatadir)/bin/forge_run_plugin_job $(DESTDIR)$(bindir)
	ln -nfs $(pkgdatadir)/bin/forge_make_admin $(DESTDIR)$(bindir)
	ln -nfs $(pkgdatadir)/bin/forge_set_password $(DESTDIR)$(bindir)
	$(INSTALL) -d -m 00755 $(DESTDIR)$(sysconfdir)/init.d/
	sed 's,@bindir@,$(bindir),' etc/init.d/fusionforge-systasksd \
		> $(DESTDIR)$(sysconfdir)/init.d/fusionforge-systasksd
	chmod 755 $(DESTDIR)$(sysconfdir)/init.d/fusionforge-systasksd
	$(INSTALL) -d -m 00755 $(DESTDIR)$(log_path)/
	if [ ! -e $(DESTDIR)$(sysconfdir)/logrotate.d/fusionforge-common ]; then \
		sed 's,@log_path@,$(log_path),g' etc/logrotate.d/fusionforge-common \
			> $(DESTDIR)$(sysconfdir)/logrotate.d/fusionforge-common; \
	fi

install-db-local: install-db
install-db: install-base-dirs
	$(CP_R) db $(DESTDIR)$(pkgdatadir)/
	$(CP_R) cronjobs/db $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(CP_R) post-install.d/db $(DESTDIR)$(pkgdatadir)/post-install.d/
	if [ ! -e $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-db ]; then \
		sed 's,@bindir@,$(bindir),' etc/cron.d/fusionforge-db \
			> $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-db; \
	fi

install-web: install-base-dirs
	$(CP_R) vendor www $(DESTDIR)$(pkgdatadir)/
	$(CP_R) --no-clobber etc/httpd.conf* $(DESTDIR)$(config_path)
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/docman/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/download/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/forum/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/forum/pending/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/tracker/
	$(INSTALL) -d -m 00700 $(DESTDIR)$(data_path)/rss/
	$(CP_R) post-install.d/web $(DESTDIR)$(pkgdatadir)/post-install.d/
	if [ ! -e $(DESTDIR)$(sysconfdir)/logrotate.d/fusionforge-web ]; then \
		sed 's,@log_path@,$(log_path),g' etc/logrotate.d/fusionforge-web \
			> $(DESTDIR)$(sysconfdir)/logrotate.d/fusionforge-web; \
	fi
	if [ ! -e $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-web ]; then \
		sed 's,@bindir@,$(bindir),' etc/cron.d/fusionforge-web \
			> $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-web; \
	fi
#	TODO: multiple servers support?
#	(e.g. install scm, lists, vhosts and vhosts-extra separately)

install-web-vhosts: install-base-dirs
	$(CP_R) cronjobs/web-vhosts $(DESTDIR)$(pkgdatadir)/cronjobs/
#	TODO: only install 40-vhosts-extra.conf and httpd.vhosts with this module

install-scm: install-base-dirs
	$(CP_R) cronjobs/scm $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(sysconfdir)/xinetd.d/
	$(CP_R) etc/xinetd.d/fusionforge-scm $(DESTDIR)$(sysconfdir)/xinetd.d/
	if [ ! -e $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-scm ]; then \
		sed 's,@bindir@,$(bindir),' etc/cron.d/fusionforge-scm \
			> $(DESTDIR)$(sysconfdir)/cron.d/fusionforge-scm; \
	fi

install-shell: install-base-dirs
	$(CP_R) cronjobs/shell $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(CP_R) post-install.d/shell $(DESTDIR)$(pkgdatadir)/post-install.d/

install-mta-postfix: install-base-dirs
	$(CP_R) post-install.d/mta-postfix $(DESTDIR)$(pkgdatadir)/post-install.d/

install-mta-exim4: install-base-dirs
	$(CP_R) post-install.d/mta-exim4 $(DESTDIR)$(pkgdatadir)/post-install.d/

install-lists: install-base-dirs
	$(CP_R) cronjobs/lists $(DESTDIR)$(pkgdatadir)/cronjobs/
	$(CP_R) post-install.d/lists $(DESTDIR)$(pkgdatadir)/post-install.d/
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/lists/

install-base: install-base-dirs install-common install-web install-db install-scm

# Select only "valid" plugins for 'make install'
PLUGINS=$(shell grep -l '^plugin_status\s=\svalid' plugins/*/etc/*.ini | sed 's,plugins/\([^/]*\)/.*,\1,')
PLUGINS_INSTALL_TARGETS=$(addprefix install-plugin-,$(PLUGINS))
install-plugins: $(PLUGINS_INSTALL_TARGETS)

install-plugin-%: plugins/% install-base-dirs
#	Source files
	$(INSTALL) -d -m 00755 $(DESTDIR)$(pkgdatadir)/plugins/$*/
	cd plugins/$* && $(CP_R) $$(ls | grep -v '^(etc|packaging)$$') $(DESTDIR)$(pkgdatadir)/plugins/$*/
#	Initial config files
	$(CP_R) --no-clobber plugins/$*/etc/$*.ini $(DESTDIR)$(config_path)/config.ini.d/;
	if [ -d plugins/$*/etc/plugins/$* ]; then \
		$(CP_R) --no-clobber plugins/$*/etc/plugins/$* $(DESTDIR)$(config_path)/plugins/; \
	fi
	if [ -d plugins/$*/etc/httpd.conf.d/ ]; then \
		$(CP_R) --no-clobber plugins/$*/etc/httpd.conf.d/ \
			$(DESTDIR)$(config_path); \
	fi
	for i in $$(ls plugins/$*/etc/cron.d/* 2>/dev/null); do \
		if [ ! -e $(DESTDIR)$(sysconfdir)/cron.d/$$(basename $$i) ]; then \
			sed 's,@bindir@,$(bindir),' $$i \
				> $(DESTDIR)$(sysconfdir)/cron.d/$$(basename $$i); \
		fi; \
	done
#	Custom install
	if [ -e plugins/$*/GNUmakefile ]; then $(MAKE) -C plugins/$*/ install; fi

uninstall:
	rm -rf $(DESTDIR)$(pkgdatadir)
	$(MAKE) -C locale/ uninstall
#	Not removing $(config_path) and $(data_path) on purpose



## Step 3: configure & update system
# not relying on 'prefix' for post-install (for no-install/dev setups)
post_source_path:=$(shell forge_get_config source_path 2>/dev/null)
post_plugins_path:=$(shell forge_get_config plugins_path 2>/dev/null)
post-install:
	$(post_source_path)/bin/post-install.sh
post-install-base: post-install-common post-install-db post-install-web post-install-scm
post-install-common:
	$(post_source_path)/post-install.d/common/common.sh configure
post-install-db:
	$(post_source_path)/post-install.d/db/db.sh configure
post-install-web:
	$(post_source_path)/post-install.d/web/web.sh configure
post-install-scm:
#	nothing yet

post-install-shell:
	$(post_source_path)/post-install.d/shell/shell.sh configure
post-install-mta-postfix:
	$(post_source_path)/post-install.d/mta-postfix/mta-postfix.sh configure
post-install-mta-exim4:
	$(post_source_path)/post-install.d/mta-exim4/mta-exim4.sh configure
post-install-lists:
	$(post_source_path)/post-install.d/lists/lists.sh configure

PLUGINS_INSTALLED:=$(shell [ -d "$(post_plugins_path)" ] && cd $(post_plugins_path) && find * -maxdepth 0 -type d)
PLUGINS_POST_INSTALL_TARGETS=$(addprefix post-install-plugin-,$(PLUGINS_INSTALLED))
# Not depending on PLUGINS_POST_INSTALL_TARGETS: too many dependencies and non-production plugins
post-install-plugins:
	@echo "Please select which plugin you want to configure (if needed):"
	@for i in $(PLUGINS_INSTALLED); do \
		echo "  make post-install-plugin-$$i"; \
	done
post-install-plugin-%: $(post_plugins_path)/%/
	$(post_source_path)/post-install.d/common/plugin.sh $* configure

uninstall-plugin-%: $(post_plugins_path)/%/
	$(post_source_path)/post-install.d/common/plugin.sh $* remove
#	$(post_source_path)/post-install.d/common/plugin.sh $* uninstall


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
	mkdir $(TAR_PREFIX)/
	$(CP_R) $$(ls -A | grep -v '^\.git' | grep -v $(TAR_PREFIX)) $(TAR_PREFIX)/
	find $(TAR_PREFIX)/ -name '*~' -print0 | xargs -r0 rm
	find $(TAR_PREFIX)/ -type d -print0 | xargs -r0 chmod g-s  # rpmlint non-standard-dir-perm
	rm -f $(TAR_PREFIX)/debian/control $(TAR_PREFIX)/fusionforge.spec
	echo $(VERSION) > $(TAR_PREFIX)/VERSION  # if using e.g. make dist VERSION=5.3.50+20141027
	tar cjf $(TAR_PREFIX).tar.bz2 $(TAR_PREFIX)/
	rm -rf $(TAR_PREFIX)/

version:
	@echo $(VERSION)
