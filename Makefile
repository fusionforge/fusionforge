DISTDEBIAN=$(shell grep -qi Debian /etc/issue && echo deb)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rpm)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rpm)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

switch:
	@echo "=========================================================================="
	@echo "Use one of the following target with "
	@echo "make -f Makefile.$(DIST) <target>"
	@echo "=========================================================================="
	@make -f Makefile.$(DIST)
