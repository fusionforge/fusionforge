include ../../GNUmakefile.defaults

all:
	@echo "No default action"

install:
	install -d -m 755 $(DESTDIR)$(data_path)/plugins/moinmoin/wikidata
	ln -nfs $(shell forge_get_config source_path)/plugins/moinmoin/wsgi/moin.wsgi	\
		$(shell forge_get_config data_path)/plugins/moinmoin/wikidata/moin.wsgi
