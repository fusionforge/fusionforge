all: allgf allcvs allsvn
clean: cleangf cleancvs cleansvn
allgf: cleangf build upload
allcvs: cleancvs buildcvs uploadcvs
allsvn: cleansvn buildsvn uploadsvn
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
cleangf:
	rm -f gforge*.deb sourceforge*.deb gforge*.changes gforge*.upload gforge*.build gforge*.dsc gforge*[^g].tar.gz gforge*.diff.gz gforge*.asc
build:
	cd gforge ; debclean; dch ;find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-$(gfversion)
	cd gforge-$(gfversion); debuild ; fakeroot debian/rules clean
	rm -rf gforge-$(gfversion)
upload:
	dput $(where) gforge*.changes

orig:
	cd gforge ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-$(gfversion)
	tar cvzf gforge_$(gfversion).orig.tar.gz gforge-$(gfversion)
	rm -rf gforge-$(gfversion)
#
# CVS PLUGIN
#
cleancvs:
	rm -f gforge-plugin-scmcvs*deb gforge-plugin-scmcvs*upload gforge-plugin-scmcvs*build gforge-plugin-scmcvs*dsc gforge-plugin-scmcvs*[^g].tar.gz gforge-plugin-scmcvs*asc gforge-plugin-scmcvs*changes
buildcvs:
	cd gforge-plugin-scmcvs ; debclean;dch; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	cd gforge-plugin-scmcvs-$(cvsversion); debuild ; fakeroot debian/rules clean
uploadcvs:
	dput $(where) gforge-plugin-scmcvs*changes
origcvs:
	cd gforge-plugin-scmcvs ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmcvs-$(cvsversion)
	tar cvzf gforge-plugin-scmcvs_$(cvsversion).orig.tar.gz gforge-plugin-scmcvs-$(cvsversion)

#
# SVN PLUGIN
#
cleansvn:
	rm -f gforge-plugin-scmsvn*deb gforge-plugin-scmsvn*upload gforge-plugin-scmsvn*build gforge-plugin-scmsvn*dsc gforge-plugin-scmsvn*[^g].tar.gz gforge-plugin-scmsvn*asc gforge-plugin-scmsvn*changes
buildsvn:
	cd gforge-plugin-scmsvn ; debclean; dch; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmsvn-$(svnversion)
	cd gforge-plugin-scmsvn-$(svnversion); debuild ; fakeroot debian/rules clean
uploadsvn:
	dput $(where) gforge-plugin-scmsvn*changes
origsvn:
	cd gforge-plugin-scmsvn ; debclean; find . -type f | grep -v '/CVS/' | grep -v rpm-specific | grep -v contrib | cpio -pdumvB ../gforge-plugin-scmsvn-$(svnversion)
	tar cvzf gforge-plugin-scmsvn_$(svnversion).orig.tar.gz gforge-plugin-scmsvn-$(svnversion)
	rm -rf gforge-plugin-scmsvn-$(svnversion)

#
# PHPDOCUMENTOR
#
phpdoc: phpdocumentor_get phpdocumentor_unapck $(documentor_path)/$(documentor_vers)/patched gforge/docs/phpdoc/docs
	
phpdocumentor_get:
	[ ! -f $(documentor_path)/$(documentor_vers).tar.gz ] && cd $(documentor_path) && wget http://heanet.dl.sourceforge.net/sourceforge/phpdocu/$(documentor_vers).tar.gz || true
phpdocumentor_unapck:
	[ ! -d $(documentor_path)/$(documentor_vers) ] && cd $(documentor_path) && tar xvzf $(documentor_vers).tar.gz || true
$(documentor_path)/$(documentor_vers)/patched:
	cd $(documentor_path)/ && patch -p2 < $(CURDIR)/gforge/docs/phpdoc/manageclass.patch && touch $(documentor_path)/$(documentor_vers)/patched 
gforge/docs/phpdoc/docs:
	cd gforge/docs/phpdoc/ && ./makedoc.sh


