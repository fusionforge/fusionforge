list:
	@echo ======================================================================================
	@echo '=                    Available target are listed below                               ='
	@echo ======================================================================================
	@cat Makefile | grep '^.*:.*#$$' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

all: allgf allcvs allsvn           # Build gforge and svn and cvs plugins #
clean: cleangf cleancvs cleansvn   # Clean gforge and svn and cvs plugins #
allgf: cleangf build               # Build gforge #
allcvs: cleancvs buildcvs          # Build cvs plugins #
allsvn: cleansvn buildsvn          # Build svn plugins #
allup: upload uploadcvs uploadsvn  # Upload all using dput and optional where=<server> #


gfversion=$(shell head -1 gforge/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
cvsversion=$(shell head -1 gforge-plugin-scmcvs/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
svnversion=$(shell head -1 gforge-plugin-scmsvn/debian/changelog | sed 's/.*(\(.*\)-.*).*/\1/')
where=local
where=g-rouille
where=mercure
documentor_path=/tmp
documentor_vers=phpdocumentor-1.3.0rc3

#
# GFORGE
#
cleangf:		# Clean debian files of gforge build                         #
	rm -f gforge*.deb sourceforge*.deb gforge*.changes gforge*.upload gforge*.build gforge*.dsc gforge*[^g].tar.gz gforge*.diff.gz gforge*.asc
build:			# Build debian gforge packages                               #
	cd gforge ; debclean; dch ;find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-$(gfversion)
	cd gforge-$(gfversion); debuild ; fakeroot debian/rules clean
	rm -rf gforge-$(gfversion)
upload:			# Upload gforge packages on where=<server> using dput        #
	dput $(where) gforge*.changes

orig:                   # Make gforge orig file                                      #
	cd gforge ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-$(gfversion)
	tar cvzf gforge_$(gfversion).orig.tar.gz gforge-$(gfversion)
	rm -rf gforge-$(gfversion)
#
# CVS PLUGIN
#
cleancvs:               # Clean debian files of gforge-plugin-scmcvs build           #
	rm -f gforge-plugin-scmcvs*deb gforge-plugin-scmcvs*upload gforge-plugin-scmcvs*build gforge-plugin-scmcvs*dsc gforge-plugin-scmcvs*[^g].tar.gz gforge-plugin-scmcvs*asc gforge-plugin-scmcvs*changes
buildcvs:               # Build debian gforge-plugin-scmcvs package                  #
	cd gforge-plugin-scmcvs ; debclean;dch; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	cd gforge-plugin-scmcvs-$(cvsversion); debuild ; fakeroot debian/rules clean
uploadcvs:              # Upload gforge-plugin-scmcvs on where=<server> using dput   #
	dput $(where) gforge-plugin-scmcvs*changes
origcvs:                # Make gforge-plugin-scmcvs orig file                        #
	cd gforge-plugin-scmcvs ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	tar cvzf gforge-plugin-scmcvs_$(cvsversion).orig.tar.gz gforge-plugin-scmcvs-$(cvsversion)

#
# SVN PLUGIN
#
cleansvn:               # Clean debian files of gforge-plugin-scmcvs build           #
	rm -f gforge-plugin-scmsvn*deb gforge-plugin-scmsvn*upload gforge-plugin-scmsvn*build gforge-plugin-scmsvn*dsc gforge-plugin-scmsvn*[^g].tar.gz gforge-plugin-scmsvn*asc gforge-plugin-scmsvn*changes
buildsvn:               # Build debian gforge-plugin-scmsvn package                  #
	cd gforge-plugin-scmsvn ; debclean; dch; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmsvn-$(svnversion)
	cd gforge-plugin-scmsvn-$(svnversion); debuild ; fakeroot debian/rules clean
uploadsvn:              # Upload gforge-plugin-scmsvn on where=<server> using dput   #
	dput $(where) gforge-plugin-scmsvn*changes
origsvn:                # Make gforge-plugin-scmsvn orig file                        #
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


