list:
	@echo ======================================================================================
	@echo '=                    Available target are listed below                               ='
	@echo ======================================================================================
	@cat Makefile | grep '^.*:.*#$$' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

all: allgf allcvs allsvn           # Build gforge and svn and cvs plugins #
clean: cleangf cleancvs cleansvn   # Clean gforge and svn and cvs plugins #
allor: orig origcvs origsvn        # Build gforge and svn and cvs orig tarballs #
cleanor:                           # Clean all gforge orig tarballs #
	rm -f gforge*orig.tar.gz
allgf: orig cleangf build          # Build gforge #
allcvs: origcvs cleancvs buildcvs  # Build cvs plugins #
allsvn: origsvn cleansvn buildsvn  # Build svn plugins #
allup: upload uploadcvs uploadsvn  # Upload all using dput and optional where=<server> #

dchcmd=dch -i
dchcmd=dch
dchcmd=""
debuildopts=-us -uc
gfversion=$(shell head -1 gforge/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
cvsversion=$(shell head -1 gforge-plugin-scmcvs/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
svnversion=$(shell head -1 gforge-plugin-scmsvn/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
where=g-rouille
where=mercure
where=local
documentor_path=/tmp
documentor_vers=phpdocumentor-1.3.0rc3

#
# GFORGE
#
cleangf:		# Clean debian files of gforge build                         #
	@rm -f gforge*.deb sourceforge*.deb gforge*.changes gforge*.upload gforge*.build gforge*.dsc gforge*[^g].tar.gz gforge*.diff.gz gforge*.asc
	@echo cleangf Done
build:			# Build debian gforge packages                               #
	cd gforge ; debclean; $(dchcmd) ;find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | grep -v docs/phpdoc/docs | cpio -pdumvB ../gforge-$(gfversion)
	cd gforge-$(gfversion); debuild $(debuildopts); fakeroot debian/rules clean
	rm -rf gforge-$(gfversion)
upload:			# Upload gforge packages on where=<server> using dput        #
	dput $(where) gforge*.changes

orig: gforge_$(gfversion).orig.tar.gz                                 # Make gforge orig file                                      #
gforge_$(gfversion).orig.tar.gz:
	cd gforge ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | grep -v docs/phpdoc/docs | cpio -pdumvB ../gforge-$(gfversion)
	tar cvzf gforge_$(gfversion).orig.tar.gz gforge-$(gfversion)
	rm -rf gforge-$(gfversion)
#
# CVS PLUGIN
#
cleancvs:               # Clean debian files of gforge-plugin-scmcvs build           #
	@rm -f gforge-plugin-scmcvs*deb gforge-plugin-scmcvs*upload gforge-plugin-scmcvs*build gforge-plugin-scmcvs*dsc gforge-plugin-scmcvs*[^g].tar.gz gforge-plugin-scmcvs*asc gforge-plugin-scmcvs*changes
	@echo cleancvs Done
buildcvs:               # Build debian gforge-plugin-scmcvs package                  #
	cd gforge-plugin-scmcvs ; debclean;$(dchcmd); find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	cd gforge-plugin-scmcvs-$(cvsversion); debuild $(debuildopts); fakeroot debian/rules clean
	rm -rf gforge-plugin-scmcvs-$(cvsversion)
uploadcvs:              # Upload gforge-plugin-scmcvs on where=<server> using dput   #
	dput $(where) gforge-plugin-scmcvs*changes
origcvs: gforge-plugin-scmcvs_$(cvsversion).orig.tar.gz               # Make gforge-plugin-scmcvs orig file                        #
gforge-plugin-scmcvs_$(cvsversion).orig.tar.gz:
	cd gforge-plugin-scmcvs ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	tar cvzf gforge-plugin-scmcvs_$(cvsversion).orig.tar.gz gforge-plugin-scmcvs-$(cvsversion)
	rm -rf gforge-plugin-scmcvs-$(cvsversion)

#
# SVN PLUGIN
#
cleansvn:               # Clean debian files of gforge-plugin-scmcvs build           #
	@rm -f gforge-plugin-scmsvn*deb gforge-plugin-scmsvn*upload gforge-plugin-scmsvn*build gforge-plugin-scmsvn*dsc gforge-plugin-scmsvn*[^g].tar.gz gforge-plugin-scmsvn*asc gforge-plugin-scmsvn*changes
	@echo cleansvn Done
buildsvn:               # Build debian gforge-plugin-scmsvn package                  #
	cd gforge-plugin-scmsvn ; debclean; $(dchcmd); find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmsvn-$(svnversion)
	cd gforge-plugin-scmsvn-$(svnversion); debuild $(debuildopts); fakeroot debian/rules clean
	rm -rf gforge-plugin-scmsvn-$(svnversion)
uploadsvn:              # Upload gforge-plugin-scmsvn on where=<server> using dput   #
	dput $(where) gforge-plugin-scmsvn*changes
origsvn: gforge-plugin-scmsvn_$(svnversion).orig.tar.gz               # Make gforge-plugin-scmsvn orig file                        #
gforge-plugin-scmsvn_$(svnversion).orig.tar.gz:
	cd gforge-plugin-scmsvn ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmsvn-$(svnversion)
	tar cvzf gforge-plugin-scmsvn_$(svnversion).orig.tar.gz gforge-plugin-scmsvn-$(svnversion)
	rm -rf gforge-plugin-scmsvn-$(svnversion)

#
# PHPDOCUMENTOR
#
phpdoc: phpdocumentor_get phpdocumentor_unpack $(documentor_path)/$(documentor_vers)/patched gforge/docs/phpdoc/docs # Get phpdocumentor, install phpdocumentor, build gforge phpdoc     #
	
phpdocumentor_get:
	[ ! -f $(documentor_path)/$(documentor_vers).tar.gz ] && cd $(documentor_path) && wget http://heanet.dl.sourceforge.net/sourceforge/phpdocu/$(documentor_vers).tar.gz || true
phpdocumentor_unpack:
	[ ! -d $(documentor_path)/$(documentor_vers) ] && cd $(documentor_path) && tar xvzf $(documentor_vers).tar.gz || true
$(documentor_path)/$(documentor_vers)/patched:
	cd $(documentor_path)/ && patch -p2 < $(CURDIR)/gforge/docs/phpdoc/manageclass.patch && touch $(documentor_path)/$(documentor_vers)/patched 
gforge/docs/phpdoc/docs:
	cd gforge/docs/phpdoc/ && ./makedoc.sh

#
# Developper personal use
#
chris:
	make allgf dchcmd="dch -i" debuildopts=""
	make upload 
chrisc:
	make allcvs dchcmd="dch -i" debuildopts=""
	make uploadcvs
chriss:
	make allsvn dchcmd="dch -i" debuildopts=""
	make uploadsvn
