DISTDEBIAN=$(shell [ -f /etc/debian_version ] && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

DEBIANLIST="2squeeze 3sid"
UBUNTULIST="1karmic 1lucid"

PKGDIR=php-apache-log4php-2.0.0incubating
PKGDSC=$(shell ls *.dsc | sort -V -r | head -1)
MINOR=$(shell grep ^Version $(PKGDSC) | sed 's/.[^-]*-\(.*\)/\1/')
PARAM=PKGDIR=$(PKGDIR) svnrev=$(MINOR) DEBIANLIST=$(DEBIANLIST) UBUNTULIST=$(UBUNTULIST)

default: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) default
	@cat ../../Makefile.$(DIST) | grep '^.*:.*#$$' | sed 's/FUSIONFORGE/$(FUSIONFORGE)/' | sed 's/^\(.*:\).*#\(.*\)#$$/\1		\2/'

%: $(PKGDIR)
	@make -f Makefile.$(DIST) $(PARAM) $@

$(PKGDIR):
	dpkg-source -x $(PKGDSC)

clean:
	rm -rf $(PKGDIR)


apache-log4php-2.0.0-incubating-src.tar.gz:
	wget http://mirror.mkhelif.fr/apache/incubator/log4php/2.0.0/apache-log4php-2.0.0-incubating-src.tar.gz

php-apache-log4php_2.0.0incubating.orig.tar.gz:
	wget http://mirror.mkhelif.fr/apache/incubator/log4php/2.0.0/Apache_log4php-2.0.0-incubating-pear.tgz
	mv Apache_log4php-2.0.0-incubating-pear.tgz php-apache-log4php_2.0.0incubating.orig.tar.gz
