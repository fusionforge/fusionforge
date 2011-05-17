#
# This Makefile may be used to create packages for distributions
#

DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

ARCHIVE=$(CURDIR)/depot
#ifeq ($(BUILDDIR),)
#	BUILDDIR=builddir
#endif
BUILDRESULT=$(CURDIR)/result

DOXYGEN=doxygen

VER=$(shell LC_ALL=C sed -n '/>software_version/s/^.*'\''\([0-9.]*\)'\''.*$$/\1/p' src/common/include/FusionForge.class.php)
in_svn_repo:= $(wildcard .svn/)
ifeq ($(strip $(in_svn_repo)),)
	ID=unknown
	URL=unknown
	TAG=unknown
else
	ID=$(shell LANG=C svnversion)
	URL=$(shell LANG=C svn info | grep 'Root:' | awk '{print $$3}')
	TAG=$(shell LANG=C svn log $(URL) -r $(ID) -l 1 2>/dev/null | awk '{ if ($$1=="Tag-Release") print $$1}')
endif
ifeq ($(TAG),)
	VERSION_ID=$(VER)-$(ID)
	VERSION=fusionforge-$(VER)-$(ID)
else
	VERSION_ID=$(VER)
	VERSION=fusionforge-$(VER)
endif

switch:
	@echo "=========================================================================="
	@echo "We have detected that your are running a '$(DIST)' distribution."
	@echo "Use one of the following targets with "
	@echo "$$ make -f Makefile.$(DIST) <target>"
	@echo "=========================================================================="
	@$(MAKE) -f Makefile.$(DIST)

check:
	## To run test in verbose mode :
	#cd tests ; phpunit --verbose unit; phpunit --verbose code; 
	cd tests ; php AllTests.php | perl -p -e '$$e=1 if /FAILURE/ ; END { exit 1 if $$e }'

checkfull:
	## To run test in verbose mode :
	#cd tests ; phpunit --verbose unit; phpunit --verbose code; phpunit --verbose build
	cd tests ; php AllFullTests.php

checkdebtools:
	sudo apt-get install php5-cli phpunit php-htmlpurifier pcregrep moreutils createrepo xalan #ubuntu-keyring

buildtar: $(BUILDRESULT)
	rm -fr /tmp/$(VERSION)
	cd src; find . -type f -or -type l | grep -v '/.svn/' | grep -v '^./debian' | grep -v '^./deb-specific' | grep -v '^./rpm-specific' | grep -v '^./contrib' | grep -v '^./fusionforge.spec' | cpio -pdumB --quiet /tmp/$(VERSION)
	cd /tmp/$(VERSION); utils/manage-translations.sh build
	cd /tmp/; tar jcf $(BUILDRESULT)/$(VERSION).tar.bz2 $(VERSION)
	rm -fr /tmp/$(VERSION)

$(BUILDRESULT):
	mkdir $(BUILDRESULT)

build-unit-tests:
	mkdir -p $(BUILDDIR)/reports/coverage
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit.xml --coverage-clover $(BUILDDIR)/reports/coverage/clover.xml --coverage-html $(BUILDDIR)/reports/coverage/ AllTests.php
	cp $(BUILDDIR)/reports/phpunit.xml $(BUILDDIR)/reports/phpunit.xml.org; xalan -in $(BUILDDIR)/reports/phpunit.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit.xml

build-doc:
	$(DOXYGEN) src/docs/fusionforge.doxygen
	$(DOXYGEN) src/plugins/wiki/www/doc/phpwiki.doxygen

build-full-tests:
	mkdir -p $(BUILDDIR)/build/packages $(BUILDDIR)/reports/coverage
	find $(BUILDDIR)/build/packages -type f -exec rm -f  {} \;
	-phpcs --tab-width=4 --standard=PEAR --report=checkstyle src/common > $(BUILDDIR)/reports/checkstyle.xml
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit.xml --coverage-clover $(BUILDDIR)/reports/coverage/clover.xml --coverage-html $(BUILDDIR)/reports/coverage/ AllFullTests.php
	cp $(BUILDDIR)/reports/phpunit.xml $(BUILDDIR)/reports/phpunit.xml.org; xalan -in $(BUILDDIR)/reports/phpunit.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit.xml
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit-selenium.xml TarCentos52Tests.php
	cp $(BUILDDIR)/reports/phpunit-selenium.xml $(BUILDDIR)/reports/phpunit-selenium.xml.org; xalan -in $(BUILDDIR)/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit-selenium.xml


src/plugins/mediawiki/mediawiki-skin/FusionForge.php:
	$(MAKE) -C src/plugins/mediawiki/mediawiki-skin

%: src/plugins/mediawiki/mediawiki-skin/FusionForge.php
	$(MAKE) -f Makefile.$(DIST) $@

wslink: /etc/apache2/mods-enabled/userdir.load
	[ -d ~/public_html ] || mkdir ~/public_html
	[ -L ~/public_html/ws ] || ln -s $(CURDIR) ~/public_html/ws

/etc/apache2/mods-enabled/userdir.load:
	sudo a2enmod userdir
