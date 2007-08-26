DISTDEBIAN=$(shell grep -qi Debian /etc/issue && echo debian)
DISTREDHAT=$(shell grep -qi 'Red Hat' /etc/issue && echo rh)
DISTSUSE=$(shell grep -qi 'SuSE' /etc/issue && echo rh)
DIST=$(DISTDEBIAN)$(DISTREDHAT)$(DISTSUSE)

switch:
	@echo "=========================================================================="
	@echo "Use one of the following target with "
	@echo "make -f Makefile.$(DIST) <target>"
	@echo "=========================================================================="
	@make -f Makefile.$(DIST)

%:
	@make -f Makefile.$(DIST) $@
