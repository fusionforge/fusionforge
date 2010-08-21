# $Id: Makefile 7353 2010-04-27 02:12:12Z vargenau $
#
# themes/Makefile
#
# minify themes
#

.PHONY: all css js

THEMES = default MonoBook wikilens Sidebar SpaceWiki blog MacOSX gforge \
	Portland shamino_com Crao Wordpress smaller Hawaiian

# http://developer.yahoo.com/yui/compressor/
YUICOMPRESSOR = java -jar default/yuicompressor-2.4.2.jar

all: 
	$(foreach dir,$(THEMES),make -C $(dir);)
