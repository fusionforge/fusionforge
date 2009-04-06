DISTDEBIAN=$(shell grep -qi Debian /etc/issue && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

ARCHIVE=$(CURDIR)/depot
BUILDRESULT=$(CURDIR)/result

VER=$(shell LANG=C grep '>software_version' gforge/common/include/FusionForge.class.php | cut -d\' -f2)
TAG=$(shell LANG=C svn log -r HEAD -l 1 | awk '{ if ($$1=="Tag-Release") print $$2}')
ifeq ($(TAG),)
	VERSION=fusionforge-$(VER)-$(shell LANG=C svn info | grep Revision | cut -d: -f2| sed 's/ //g')
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

buildall: buildtar
	-make -f Makefile.debian all
	-make -f Makefile.rh all

buildtar:
	rm -fr /tmp/$(VERSION)
	cd gforge; find . -type f -or -type l | grep -v '/.svn/' | grep -v '^./debian' | grep -v '^./deb-specific' | grep -v '^./rpm-specific' | grep -v '^./contrib' | grep -v '^./gforge.spec' | grep -v '^./README.setup' | grep -v '^./setup' | cpio -pdumB /tmp/$(VERSION)
	cd /tmp/$(VERSION); utils/manage-translations.sh build
	cd /tmp/; tar jcf $(BUILDRESULT)/$(VERSION).tar.bz2 $(VERSION)
	cd /tmp/$(VERSION); tar zxf $(ARCHIVE)/libphp-jpgraph_1.5.2.orig.tar.gz
	cd /tmp/$(VERSION); patch -p0 < $(ARCHIVE)/jpgraph-1.5.2-php5_and_liberation_fonts.patch
	cd /tmp/$(VERSION); mkdir jpgraph; mv jpgraph-1.5.2/src/* jpgraph; rm -fr jpgraph-1.5.2
	cd /tmp; tar jcf $(BUILDRESULT)/$(VERSION)-allinone.tar.bz2 $(VERSION)
	rm -fr /tmp/$(VERSION)

%:
	@make -f Makefile.$(DIST) $@
