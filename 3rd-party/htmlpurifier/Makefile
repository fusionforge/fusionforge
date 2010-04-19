DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

DEBIANLIST=1lenny
UBUNTULIST="1jaunty 1karmic 1lucid"

PKGDIR=php-htmlpurifier-4.0.0+dfsg1
PKGDSC=$(shell ls *.dsc | sort -V -r | head -1)
MINOR=$(shell grep ^Version $(PKGDSC) | head -1 | sed 's/.[^-]*-\(.*\)/\1/')
PARAM=PKGDIR=$(PKGDIR) svnrev=$(MINOR) DEBIANLIST=$(DEBIANLIST) UBUNTULIST=$(UBUNTULIST)

default: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) $@

$(PKGDIR):
	#dget http://ftp.fr.debian.org/debian/pool/main/p/php-htmlpurifier/php-htmlpurifier_4.0.0+dfsg1-1.dsc
	dpkg-source -x $(PKGDSC)

clean:
	rm -rf $(PKGDIR)
