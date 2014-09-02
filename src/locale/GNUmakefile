# gettext message catalogs build and install
#
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

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
