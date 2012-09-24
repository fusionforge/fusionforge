# quick Mediawiki backport

DSCFILE=http://ftp.de.debian.org/debian/pool/main/m/mediawiki/mediawiki_1.19.2-1.dsc
DIST=wheezy


default: mediawiki-1.19.2

mediawiki-1.19.2: mediawiki_1.19.2-1.dsc
	dpkg-source -x mediawiki_1.19.2-1.dsc
	sed -i -e "1s/unstable/$(DIST)/" $@/debian/changelog
	cd $@; pdebuild --configfile $(COWBUILDERCONFIG) --buildresult $(BUILDRESULT)

mediawiki_1.19.2-1.dsc:
	dget -d $(DSCFILE)
