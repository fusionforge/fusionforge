DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

ARCHIVE=$(CURDIR)/depot
BUILDRESULT=$(CURDIR)/result

VER=$(shell LANG=C grep '>software_version' gforge/common/include/FusionForge.class.php | cut -d\' -f2)
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
	VERSION=fusionforge-$(VER)-$(ID)
else
	VERSION=fusionforge-$(VER)
endif

switch:
	@echo "=========================================================================="
	@echo "Use one of the following target with "
	@echo "make -f Makefile.$(DIST) <target>"
	@echo "=========================================================================="
	@make -f Makefile.$(DIST)

check:
	cd tests ; php AllTests.php

buildtar:
	rm -fr /tmp/$(VERSION)
	cd gforge; find . -type f -or -type l | grep -v '/.svn/' | grep -v '^./debian' | grep -v '^./deb-specific' | grep -v '^./rpm-specific' | grep -v '^./contrib' | grep -v '^./gforge.spec' | grep -v '^./README.setup' | grep -v '^./setup' | cpio -pdumB --quiet /tmp/$(VERSION)
	cd /tmp/$(VERSION); utils/manage-translations.sh build
	cd /tmp/; tar jcf $(BUILDRESULT)/$(VERSION).tar.bz2 $(VERSION)
	cd /tmp/$(VERSION); tar zxf $(ARCHIVE)/libphp-jpgraph_1.5.2.orig.tar.gz
	cd /tmp/$(VERSION); patch -p0 < $(ARCHIVE)/jpgraph-1.5.2-php5_and_liberation_fonts.patch
	cd /tmp/$(VERSION); mkdir jpgraph; mv jpgraph-1.5.2/src/* jpgraph; rm -fr jpgraph-1.5.2
	cd /tmp; tar jcf $(BUILDRESULT)/$(VERSION)-allinone.tar.bz2 $(VERSION)
	rm -fr /tmp/$(VERSION)

build-unit-tests:
	mkdir -p $(BUILDDIR)/reports/coverage
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit.xml --coverage-clover $(BUILDDIR)/reports/coverage/clover.xml --coverage-html $(BUILDDIR)/reports/coverage/ AllTests.php
	cp $(BUILDDIR)/reports/phpunit.xml $(BUILDDIR)/reports/phpunit.xml.org; xalan -in $(BUILDDIR)/reports/phpunit.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit.xml

build-full-tests:
	find $(BUILDDIR)/build/packages -type f -exec rm -f  {} \;
	mkdir -p $(BUILDDIR)/reports/coverage
	phpdoc --title 'API Documentation' -ue on -t $(BUILDDIR)/apidocs -d gforge/common -tb '/usr/share/php/data/phpUnderControl/data/phpdoc' -o HTML:Phpuc:phpuc
	-phpcs --tab-width=4 --standard=PEAR --report=checkstyle gforge/common > $(BUILDDIR)/reports/checkstyle.xml
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit.xml --coverage-clover $(BUILDDIR)/reports/coverage/clover.xml --coverage-html $(BUILDDIR)/reports/coverage/ AllFullTests.php
	cp $(BUILDDIR)/reports/phpunit.xml $(BUILDDIR)/reports/phpunit.xml.org; xalan -in $(BUILDDIR)/reports/phpunit.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit.xml
	cd tests; phpunit --log-junit $(BUILDDIR)/reports/phpunit-selenium.xml TarCentos52Tests.php
	cp $(BUILDDIR)/reports/phpunit-selenium.xml $(BUILDDIR)/reports/phpunit-selenium.xml.org; xalan -in $(BUILDDIR)/reports/phpunit-selenium.xml.org -xsl fix_phpunit.xslt -out $(BUILDDIR)/reports/phpunit-selenium.xml


gforge/plugins/mediawiki/mediawiki-skin/FusionForge.php:
	cd gforge/plugins/mediawiki/mediawiki-skin ; make

%: gforge/plugins/mediawiki/mediawiki-skin/FusionForge.php
	@make -f Makefile.$(DIST) $@
