DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

PKGDIR1=mailman
PKGDIR2=forumml
PKGDIR3=hudson
PKGDIR=mailman

MINOR=$(shell head -1 $(PKGDIR)/debian/changelog | sed 's/.*(.*-\([^-]*\)).*/\1/')

PARAM=PKGDIR=$(PKGDIR) svnrev=$(MINOR)

def:
	@echo "#==============================================#"
	@echo "Use make PKGDIR=<$(PKGDIR1)|$(PKGDIR2)|$(PKGDIR3)> <target>"	
	@#make -f Makefile.$(DIST) $(PARAM) default
	@echo "target=default displays all params"
	@echo "other available targets are: "
	@echo "#==============================================#"
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

default:
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%:
	@make -f Makefile.$(DIST) $(PARAM) $@
