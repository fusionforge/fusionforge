# $Id: Makefile 7442 2010-05-21 10:11:55Z vargenau $
#
# themes/gforge/Makefile
#
# minify CSS and JS
#

CSS_SRC = gforge.css gforge-print.css gforge-autonumbering.css gforge-rereading.css gforge-fullscreen.css
MAIN = wikilens
JS_MAIN = wikilens.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
