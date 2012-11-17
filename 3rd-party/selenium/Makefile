# use tests/scripts/manage-cowbuilder.sh before building#

include selenium.include

BUILDERDIR:=$(shell ../../tests/scripts/builder_get_config.sh BUILDERDIR)
DIST=wheezy
COWBUILDERCONFIG=$(BUILDERDIR)/config/$(DIST).config
include $(COWBUILDERCONFIG)
REPOPATH:=$(shell ../../tests/scripts/builder_get_config.sh REPOPATH)

ARCH:=$(shell dpkg-architecture -qDEB_BUILD_ARCH 2>/dev/null)
DSCFILE=selenium_$(VERS).dsc
CHANGEFILE=selenium_$(VERS)_$(ARCH).changes

$(BUILDRESULT)/$(CHANGEFILE): $(BUILDPLACE)/$(DSCFILE)
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --build $(BUILDPLACE)/$(DSCFILE)
	[ ! -d $(REPOPATH)/debian ] || reprepro -Vb $(REPOPATH)/debian --ignore=wrongdistribution --ignore=missingfile include $(DIST) $@

$(BUILDPLACE)/$(DSCFILE): $(BUILDPLACE)/selenium/selenium-server.jar
	cd $(BUILDPLACE) ; dpkg-source -b selenium

$(BUILDPLACE)/selenium/selenium-server.jar: $(BUILDPLACE)/selenium/debian
	[ -d $@ ] || wget -O - $(SELENIUMURL) > $@

$(BUILDPLACE)/selenium/debian:
	[ -d $@ ] || cp -r selenium $(BUILDPLACE)
