# $Id: Makefile 7833 2011-01-13 15:06:45Z vargenau $
#
# themes/MonoBook/Makefile
#
# minify CSS and JS
#

CSS_SRC = IE70Fixes.css   commonPrint.css  rtl.css \
	  IEFixes.css     main.css         wikiprintable.css \
	  IE60Fixes.css  monobook.css     wikistandard.css
MAIN    = wikibits
JS_MAIN = wikibits.js
# JS_REST = IEFixes.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
