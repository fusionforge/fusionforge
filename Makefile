DISTDEBIAN=$(shell grep -qi Debian /etc/issue && echo deb)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rpm)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rpm)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

switch: gforge-plugin-scmcvs gforge-plugin-scmsvn
	@echo "=========================================================================="
	@echo "Use one of the following target with "
	@echo "make -f Makefile.$(DIST) <target>"
	@echo "=========================================================================="
	@make -f Makefile.$(DIST)

all: gforge-plugin-scmcvs gforge-plugin-scmsvn
	@make -f Makefile.$(DIST) all
clean:
	@make -f Makefile.$(DIST) clean
	rm -f gforge-plugin-scmcvs gforge-plugin-scmsvn
cleanor:
	@make -f Makefile.$(DIST) cleanor
chris: gforge-plugin-scmcvs gforge-plugin-scmsvn
	@make -f Makefile.$(DIST) chris
chriss: gforge-plugin-scmcvs gforge-plugin-scmsvn
	@make -f Makefile.$(DIST) chriss
chrisc: gforge-plugin-scmcvs gforge-plugin-scmsvn
	@make -f Makefile.$(DIST) chrisc

gforge-plugin-scmcvs:
	ln -s gforge/plugins/scmcvs gforge-plugin-scmcvs
gforge-plugin-scmsvn:
	ln -s gforge/plugins/scmsvn gforge-plugin-scmsvn
