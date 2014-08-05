include ../GNUmakefile.defaults
LINGUAS=$(shell ls *.po | sed 's/\.po$$//')

all: update-gmo

# Compile translation strings catalogs
update-gmo:
	for l in $(LINGUAS); do \
		mkdir -p $$l/LC_MESSAGES; \
		echo -n "$$l: "; \
		msgfmt --statistics $$l.po -o $$l/LC_MESSAGES/fusionforge.mo; \
	done

clean:
	rm -rf $(LINGUAS)

install: all
	install -d -m 755 $(DESTDIR)/$(PREFIX)/share/locale/
	cp -a $(LINGUAS) $(DESTDIR)/$(PREFIX)/share/locale/
