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

default: $(BUILDRESULT)/$(CHANGEFILE) dist

$(BUILDRESULT)/$(CHANGEFILE): $(BUILDPLACE)/$(DSCFILE)
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --build $(BUILDPLACE)/$(DSCFILE)

dist:
	reprepro -Vb $(REPOPATH)/debian --ignore=wrongdistribution --ignore=missingfile include $(DIST) $(BUILDRESULT)/$(CHANGEFILE)

$(BUILDPLACE)/$(DSCFILE): $(BUILDPLACE)/selenium_$(UPSTREAM).orig.tar.gz
	cd $(BUILDPLACE)/selenium ; dch -b -v $(VERS) -D unstable "Autobuilt from Selenium $(UPSTREAM) jar" ; cd .. ; dpkg-source -b selenium

$(BUILDPLACE)/selenium/selenium-server.jar: $(BUILDPLACE)/selenium/debian selenium.include
	[ -d $@ ] || wget -O - $(SELENIUMURL) > $@

$(BUILDPLACE)/selenium/debian:
	[ -d $@ ] || cp -r selenium $(BUILDPLACE)

$(BUILDPLACE)/selenium_$(UPSTREAM).orig.tar.gz: $(BUILDPLACE)/selenium/selenium-server.jar
	[ -d $@ ] || (cd $(BUILDPLACE) ; tar cvzf $@ selenium/selenium-server.jar)

clean:
	[ ! -d $(BUILDPLACE)/selenium ] || rm -rf $(BUILDPLACE)/selenium
