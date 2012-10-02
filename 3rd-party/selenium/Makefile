#
# http://seleniumhq.org/
# http://seleniumhq.org/projects/remote-control/
# http://selenium.googlecode.com/svn/trunk/remote/server/
# see tests/func/README.TXT
#

S_VERSION=2.21.0
DIST=wheezy
ARCH:=$(shell dpkg-architecture -qDEB_BUILD_ARCH 2>/dev/null)

DSCFILE=selenium_$(S_VERSION)-1.dsc
CHANGEFILE=selenium_$(S_VERSION)-1_$(ARCH).changes


run: selenium-server.jar
	LANG=C java -jar selenium-server.jar -interactive

selenium-server.jar: Makefile
	wget -O - http://selenium.googlecode.com/files/selenium-server-standalone-$(S_VERSION).jar > $@

selenium/selenium-server.jar: Makefile
	wget -O - http://selenium.googlecode.com/files/selenium-server-standalone-$(S_VERSION).jar > $@

svnexport:
	svn export http://selenium.googlecode.com/svn/trunk/remote/server selenium-server
	#svn export http://selenium.googlecode.com/svn/trunk selenium

getpackage:
	sudo apt-get install maven-debian-helper

getselenium: selenium-server.jar

clean:
	rm -f selenium/selenium-server.jar selenium-server.jar

botbuild: getselenium

botclean:
	@echo "Nothing to do"

buildpackage: $(BUILDRESULT)/$(CHANGEFILE)

$(DSCFILE): selenium/selenium-server.jar
	dpkg-source -b selenium

$(BUILDRESULT)/$(CHANGEFILE): $(DSCFILE)
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT) --build $(DSCFILE)

