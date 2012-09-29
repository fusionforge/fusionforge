# quick Mediawiki backport

DSCFILE=http://ftp.de.debian.org/debian/pool/main/m/mediawiki/mediawiki_1.19.2-1.dsc
DIST=wheezy

cowbuild: mediawiki_1.19.2-1.dsc
	sudo cowbuilder --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT) --build mediawiki_1.19.2-1.dsc

pdebuild: mediawiki-1.19.2
	cd mediawiki-1.19.2; pdebuild --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT)

mediawiki-1.19.2: mediawiki_1.19.2-1.dsc
	dpkg-source -x mediawiki_1.19.2-1.dsc
	sed -i -e "1s/unstable/$(DIST)/" $@/debian/changelog

mediawiki_1.19.2-1.dsc:
	dget -d $(DSCFILE)
