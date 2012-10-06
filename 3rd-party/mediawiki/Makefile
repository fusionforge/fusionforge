# quick Mediawiki backport
MAJOR=1.19.2
MINOR=2

VERS=$(MAJOR)-$(MINOR)

DSCFILE=http://ftp.de.debian.org/debian/pool/main/m/mediawiki/mediawiki_$(VERS).dsc
DIST=wheezy
ARCH:=$(shell dpkg-architecture -qDEB_BUILD_ARCH 2>/dev/null)
CHANGEFILEM=mediawiki_$(VERS)_$(ARCH).changes

$(BUILDRESULT)/$(CHANGEFILEM): mediawiki_$(VERS).dsc
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT) --build mediawiki_1.19.2-1.dsc

pdebuild: mediawiki-$(MAJOR)
	cd mediawiki-1.19.2; pdebuild --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT)

mediawiki-$(MAJOR): mediawiki_$(VERS).dsc
	dpkg-source -x mediawiki_$(VERS).dsc
	sed -i -e "1s/unstable/$(DIST)/" $@/debian/changelog

mediawiki_$(VERS).dsc:
	dget -d $(DSCFILE)
