# $Id: Makefile 7662 2010-08-31 15:12:14Z vargenau $
#
# themes/fusionforge/Makefile
#
# minify CSS and JS
#

CSS_SRC = fusionforge.css fusionforge-print.css fusionforge-autonumbering.css fusionforge-rereading.css fusionforge-fullscreen.css
MAIN = wikilens
JS_MAIN = wikilens.js

CSS_FILES = $(patsubst %.css,%-min.css,$(CSS_SRC))

all	: css js

include ../Makefile.global
