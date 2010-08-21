# $Id: Makefile 7436 2010-05-21 09:19:15Z vargenau $
#
# themes/MonoBook/Makefile
#
# minify CSS and JS
#

CSS_SRC = IE50Fixes.css  IE70Fixes.css   KHTMLFixes.css   commonPrint.css  rtl.css \
	  IE55Fixes.css  IEFixes.css     Opera6Fixes.css  main.css         wikiprintable.css \
	  IE60Fixes.css  IEMacFixes.css  Opera7Fixes.css  monobook.css     wikistandard.css
MAIN    = wikibits
JS_MAIN = wikibits.js
# JS_REST = IEFixes.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
