include ../../GNUmakefile.defaults

all:
	@echo "No default action"

install:
	install -d -m 00755 $(DESTDIR)$(data_path)/plugins/moinmoin/wikidata
	ln -nfs $(pkgdatadir)/plugins/moinmoin/wsgi/moin.wsgi			\
		$(DESTDIR)$(data_path)/plugins/moinmoin/wikidata/moin.wsgi
