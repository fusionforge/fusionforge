include ../GNUmakefile.defaults
.PHONY: fusionforge.pot
LINGUAS=$(shell ls *.po | sed 's/\.po$$//')

all: update-gmo

# Compile translation strings catalogs
MO_FILES=$(patsubst %,%/LC_MESSAGES/fusionforge.mo,$(LINGUAS))
%/LC_MESSAGES/fusionforge.mo: %.po
	mkdir -p $$(dirname $@)
	msgfmt --statistics $< -o $@
update-gmo: $(MO_FILES)

fusionforge.pot:
	cd .. && find . -type f \( -name \*.php -or -name users -or -name projects \) \
	    | grep -v ^./plugins/wiki/www \
	    | LANG=C sort \
	    | xargs xgettext -d fusionforge -o locale/fusionforge.pot -L PHP --from-code=utf-8

# update-po: let the Translation Project / TP translators do the 'msgmerge'
# See https://fusionforge.org/plugins/mediawiki/wiki/fusionforge/index.php/Translations


clean:
	rm -rf $(LINGUAS)

install: all
	install -d -m 00755 $(DESTDIR)$(localedir)/
	$(CP_R) $(LINGUAS) $(DESTDIR)$(localedir)/

uninstall:
	rm -rf $(DESTDIR)$(localedir)/*/LC_MESSAGES/fusionforge.mo
