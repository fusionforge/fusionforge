DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

PKGDIR=mailman-2.1.13
PKGDSC=$(shell ls *.dsc | sort -V -r | head -1)
MINOR=$(shell grep ^Version $(PKGDSC) | sed 's/.[^-]*-\(.*\)/\1/')

PARAM=PKGDIR=$(PKGDIR) svnrev=$(MINOR)

default: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) $@

$(PKGDIR):
	dpkg-source -x $(PKGDSC)

clean:
	rm -rf $(PKGDIR)
