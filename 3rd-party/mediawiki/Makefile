# quick Mediawiki backport
# use tests/scripts/manage-cowbuilder.sh before building
MAJOR=1.19.2
MINOR=2

VERS=$(MAJOR)-$(MINOR)

BUILDERDIR:=$(shell ../../tests/scripts/builder_get_config.sh BUILDERDIR)
DIST=wheezy
COWBUILDERCONFIG=$(BUILDERDIR)/config/$(DIST).config
include $(COWBUILDERCONFIG)
REPOPATH:=$(shell ../../tests/scripts/builder_get_config.sh REPOPATH)

DSCFILE=mediawiki_$(VERS).dsc
DSCURL=http://ftp.de.debian.org/debian/pool/main/m/mediawiki/$(DSCFILE)
DIST=wheezy
ARCH:=$(shell dpkg-architecture -qDEB_BUILD_ARCH 2>/dev/null)
CHANGEFILE=mediawiki_$(VERS)_$(ARCH).changes

default: $(BUILDRESULT)/$(CHANGEFILE) dist

$(BUILDRESULT)/$(CHANGEFILE): $(BUILDPLACE)/$(DSCFILE)
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --build $(BUILDPLACE)/$(DSCFILE)

dist:
	reprepro -Vb $(REPOPATH)/debian --ignore=wrongdistribution --ignore=missingfile include $(DIST) $(BUILDRESULT)/$(CHANGEFILE)

$(BUILDPLACE)/$(DSCFILE): $(BUILDPLACE)
	cd $(BUILDPLACE) ; dget -d $(DSCURL)
