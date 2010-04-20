DIRLIST=db2latex-xsl htmlpurifier mailman nusoap php-apache-log4php php-mail-mbox

%:
	@for dir in $(DIRLIST); do \
        cd $(CURDIR)/$$dir ; $(MAKE) $@ ; \
	done

.PHONY:$(DIRLIST)

