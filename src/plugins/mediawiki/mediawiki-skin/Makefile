MWSKINPATH=/usr/share/mediawiki/skins

all: FusionForge.php
allold: FusionForge.php fusionforge/main.css

FusionForge.php: FusionForge.php.renamed
	@echo 'Building $@'
	patch -p0 -o $@ FusionForge.php.renamed FusionForge.php.patch

FusionForge.php.renamed: $(MWSKINPATH)
	cp $(MWSKINPATH)/MonoBook.php FusionForge.php.reference
	cat $(MWSKINPATH)/MonoBook.php | sed 's/MonoBook/FusionForge/g' | sed 's/monobook/fusionforge/g' > $@
	

fusionforge/main.css: fusionforge/main.css.renamed
	@echo 'Building $@'
	patch -p0 -o $@ fusionforge/main.css.renamed fusionforge/main.css.patch

fusionforge/main.css.renamed: $(MWSKINPATH)
	cp $(MWSKINPATH)/monobook/main.css fusionforge/main.css.reference
	cat $(MWSKINPATH)/monobook/main.css | sed 's/MonoBook/FusionForge/g' | sed 's/monobook/fusionforge/g' > $@
	

$(MWSKINPATH):
	echo 'You must install mediawiki to get reference files'

cleanphp:
	rm -f FusionForge.php.renamed FusionForge.php.patch2

cleancss:
	rm -f fusionforge/main.css.renamed fusionforge/main.css.patch2

patchphp: FusionForge.php.renamed
	-diff -uN FusionForge.php.renamed FusionForge.php > FusionForge.php.patch2

patchcss: fusionforge/main.css.renamed
	-diff -uN fusionforge/main.css.renamed fusionforge/main.css > fusionforge/main.css.patch2
