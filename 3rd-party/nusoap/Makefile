DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

DEBIANLIST=1lenny
UBUNTULIST="1jaunty 1karmic"

PKGDIR=nusoap-0.7.3
PKGDSC=$(shell ls *.dsc | sort -V -r | head -1)
MINOR=$(shell grep ^Version $(PKGDSC) | head -1 | sed 's/.[^-]*-\(.*\)/\1/')
PARAM=PKGDIR=$(PKGDIR) svnrev=$(MINOR) DEBIANLIST=$(DEBIANLIST) UBUNTULIST=$(UBUNTULIST)

default: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) $@

$(PKGDIR):
	#apt-get source nusoap
	dpkg-source -x $(PKGDSC)

clean:
	rm -rf $(PKGDIR)
