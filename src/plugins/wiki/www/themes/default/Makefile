# $Id: Makefile 7438 2010-05-21 09:31:51Z vargenau $
#
# themes/default/Makefile
#
# minify CSS and JS
#

CSS_SRC = IEFixes.css    phpwiki-printer.css       phpwiki.css \
	  highlight.css  phpwiki-modern.css        phpwiki-topbottombars.css  \
	  moacdropdown/css/dropdown.css
MAIN    = wikicommon
JS_MAIN = wikicommon.js  sortable.js ajax.js moacdropdown.js toolbar.js
JS_REST = ASCIIsvg.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
