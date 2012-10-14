# quick Mediawiki backport
MAJOR=1.19.2
MINOR=2

VERS=$(MAJOR)-$(MINOR)

include $(COWBUILDERCONFIG)

DSCFILE=http://ftp.de.debian.org/debian/pool/main/m/mediawiki/mediawiki_$(VERS).dsc
DIST=wheezy
ARCH:=$(shell dpkg-architecture -qDEB_BUILD_ARCH 2>/dev/null)
CHANGEFILEM=mediawiki_$(VERS)_$(ARCH).changes

$(BUILDRESULT)/$(CHANGEFILEM): $(BUILDPLACE)/mediawiki_$(VERS).dsc
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT) --buildplace $(BUILDPLACE) --build $(BUILDPLACE)/mediawiki_$(VERS).dsc
	reprepro -Vb ${REPOPATH} --ignore=wrongdistribution --ignore=missingfile include $(DIST) $@

pdebuild: $(BUILDPLACE)/mediawiki-$(MAJOR)
	cd $(BUILDPLACE)/mediawiki-$(MAJOR) ; pdebuild --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT)

mediawiki-$(MAJOR): $(BUILDPLACE)/mediawiki_$(VERS).dsc
	cd $(BUILDPLACE) ; dpkg-source -x mediawiki_$(VERS).dsc
	cd $(BUILDPLACE) ; sed -i -e "1s/unstable/$(DIST)/" $@/debian/changelog

$(BUILDPLACE)/mediawiki_$(VERS).dsc: $(BUILDPLACE)
	cd $(BUILDPLACE) ; dget -d $(DSCFILE)
