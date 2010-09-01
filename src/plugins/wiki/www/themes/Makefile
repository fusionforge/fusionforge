# $Id: Makefile 7664 2010-08-31 15:42:34Z vargenau $
#
# themes/Makefile
#
# minify themes
#

.PHONY: all css js

THEMES = default MonoBook wikilens Sidebar SpaceWiki blog MacOSX fusionforge \
	Portland shamino_com Crao Wordpress smaller Hawaiian

# http://developer.yahoo.com/yui/compressor/
YUICOMPRESSOR = java -jar default/yuicompressor-2.4.2.jar

all: 
	$(foreach dir,$(THEMES),make -C $(dir);)
