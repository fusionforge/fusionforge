DISTDEBIAN:=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT:=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE:=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST:=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

PKGNAME:=jquery-goodies
MAJOR=4
MINOR=1
PKGDSC:=$(PKGNAME)_$(MAJOR)-$(MINOR).dsc
DEBMIRROR:=http://cdn.debian.org/debian
PKGDIR:=$(PKGNAME)-$(MAJOR)
PKGURL:=$(DEBMIRROR)/pool/main/j/jquery-goodies/$(PKGDSC)
PARAM:=PKGDIR=$(PKGDIR) svnrev=$(MINOR) PKGURL=$(PKGURL) PKGNAME=$(PKGNAME) MAJOR=$(MAJOR)

default: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) $@

$(PKGDIR):
	echo "Nothing to do for $@"

botbuild:
	@make -f Makefile.$(DIST) $(PARAM) r$(BUILDDIST)

botclean:
	@echo "NOT CLEANING"
	#@make -f Makefile.$(DIST) $(PARAM) botclean
