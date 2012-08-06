# $Id: Makefile 7688 2010-09-13 15:05:24Z vargenau $
#
# themes/default/Makefile
#
# minify CSS and JS
#

CSS_SRC = IEFixes.css    phpwiki-printer.css       phpwiki.css \
	  highlight.css  phpwiki-modern.css        phpwiki-topbottombars.css  \
	  toolbar.css \
	  moacdropdown/css/dropdown.css
MAIN    = wikicommon
JS_MAIN = wikicommon.js
JS_REST = ASCIIsvg.js sortable.js ajax.js moacdropdown.js toolbar.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
