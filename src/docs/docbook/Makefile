
#XSLTPROC = xsltproc -v
XSLTPROC = xsltproc
SRC = docbook/
TARGET = build/
XSL = xsl/
DOCBOOK_XSL = ${XSL}docbook
DB2LATEX_XSL = ${XSL}db2latex/
XSLFOP = /usr/share/xml/docbook/stylesheet/docbook-xsl/fo/
FOP = fop
#DEVNULL = >>/tmp/debug 2>&1
DEVNULL = &> /dev/null

BOOKS=gforge_manual
ARTICLES=administration_guide user_guide contribution_guide installation_guide

all: clean html html-chunk html-guide pdf

html: validate prepare
	##
	# Generating HTML output
	#
	@for book in ${BOOKS}; do \
		echo '# . Generating HTML output for book '$$book; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$book/$$book.plain.html ${XSL}book_html.xsl ${SRC}$$book.xml; \
		tar -czf ${TARGET}$$book/$$book.plain.tar.gz ${TARGET}$$book/$$book.plain.html; \
	done;

	@for article in ${ARTICLES}; do \
		echo '# . Generating HTML output for article '$$article; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$article/$$article.plain.html ${XSL}article_html.xsl ${SRC}$$article/$$article.xml; \
		tar -czf ${TARGET}$$article/$$article.plain.tar.gz ${TARGET}$$article/$$article.plain.html; \
	done;
	#
	# Done
	##

html-chunk: validate prepare
	##
	# Generating HTML chunked output
	#
	@for book in ${BOOKS}; do \
		echo '# . Generating HTML chunked output for book '$$book; \
		test -d ${TARGET}$$book/$$book || mkdir ${TARGET}$$book/$$book.chunk; \
		$(XSLTPROC) --xinclude --stringparam base.dir ${TARGET}$$book/$$book.chunk/ ${XSL}book_html_chunk.xsl ${SRC}$$book.xml ${DEVNULL}; \
		tar -czf ${TARGET}$$book/$$book.chunk.tar.gz ${TARGET}$$book/$$book.chunk; \
	done;

	@for article in ${ARTICLES}; do \
		echo '# . Generating HTML chunked output for article '$$article; \
		test -d ${TARGET}$$article/$$article || mkdir ${TARGET}$$article/$$article.chunk; \
		$(XSLTPROC) --xinclude --stringparam base.dir ${TARGET}$$article/$$article.chunk/ ${XSL}article_html_chunk.xsl ${SRC}$$article/$$article.xml ${DEVNULL}; \
		tar -czf ${TARGET}$$article/$$article.chunk.tar.gz ${TARGET}$$article/$$article.chunk; \
	done;
	#
	# Done
	##

# This guide is the online guide, it is generated as chunked document
# and saved in /usr/share/gforge/www/help/guide/
html-guide: $(SRC)/user_guide/user_guide.xml
	rm -f guide/ug_* guide/bk*
	$(XSLTPROC) --xinclude --stringparam use.id.as.filename 1 --stringparam chunk.section.depth 2 --stringparam chunker.output.indent yes --stringparam chunker.output.encoding UTF-8 --stringparam base.dir guide/ --stringparam html.stylesheet docbook.css ${XSL}article_html_chunk.xsl $<
	cp css/docbook.css guide/docbook.css
#	rm -f guide/images/*
#	cp -p images/* guide/images/

pdffop: validate prepare
	##
	# Generating PDF output
	#
	# Inspired from Alain Peyrat suggestion
	#xsltproc --xinclude -o tmp.fo $(XSL)/fo-stylesheet.xsl $(XML)/user_guide/user_guide_full.xml
	#$(FOP) -fo tmp.fo -pdf help_guide/user_guide.pdf
	@for book in ${BOOKS}; do \
		echo '# . Generating PDF output for book '$$book; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$book/$$book.fo ${XSLFOP}docbook.xsl ${SRC}$$book.xml ${DEVNULL}; \
		(cd ${TARGET}$$book; $(FOP) -fo $$book.fo -pdf $$book.pdf ${DEVNULL};); \
	done;

	@for article in ${ARTICLES}; do \
		echo '# . Generating PDF output for article '$$article; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$article/$$article.fo ${XSLFOP}docbook.xsl ${SRC}$$article/$$article.xml ${DEVNULL}; \
		(cd ${TARGET}$$article; $(FOP) -fo $$article.fo -pdf $$article.pdf ${DEVNULL};); \
	done;
	#
	# Done
	##

pdf: validate prepare
	##
	# Generating PDF output
	#
	@for book in ${BOOKS}; do \
		echo '# . Generating PDF output for book '$$book; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$book/$$book.tex ${XSL}book_pdf.xsl ${SRC}$$book.xml ${DEVNULL}; \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex ${DEVNULL};); \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex ${DEVNULL};); \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex ${DEVNULL};); \
		(cd ${TARGET}$$book; rm -f *.aux *.glo *.idx *.log *.out *.tex *.toc ${DEVNULL};); \
	done;

	@for article in ${ARTICLES}; do \
		echo '# . Generating PDF output for article '$$article; \
		$(XSLTPROC) --xinclude --output ${TARGET}$$article/$$article.tex ${XSL}article_pdf.xsl ${SRC}$$article/$$article.xml ${DEVNULL}; \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex ${DEVNULL};); \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex ${DEVNULL};); \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex ${DEVNULL};); \
		(cd ${TARGET}$$article; rm -f *.aux *.glo *.idx *.log *.out *.tex *.toc ${DEVNULL};); \
	done;
	#
	# Done
	##

prepare:
	##
	# Preparing build environment
	#
	@test -d ${TARGET} || mkdir -p ${TARGET};
	@for book in ${BOOKS}; do \
		test -d ${TARGET}$$book || mkdir ${TARGET}$$book; \
	done;
	@for article in ${ARTICLES}; do \
		test -d ${TARGET}$$article || mkdir ${TARGET}$$article; \
	done;
	# Done
	##

validate:
	##
	# Validating documents
	#
	@for book in ${BOOKS}; do \
		if (xmllint --xinclude --noout --postvalid ${SRC}$$book.xml ${DEVNULL}); then \
			echo '# . Validating book '$$book' : [ OK ]'; \
		else \
			echo '# . Validating book '$$book' : [ ERROR ]'; \
			echo '#'; \
			echo '# ERROR : please check your docbook documents using xmllint first'; \
			echo '##'; \
			exit 1; \
		fi; \
	done;
	@for article in ${ARTICLES}; do \
		if (xmllint --xinclude --noout --postvalid ${SRC}$$article/$$article.xml ${DEVNULL}); then \
			echo '# . Validating article '$$article' : [ OK ]'; \
		else \
			echo '# . Validating article '$$article' : [ ERROR ]'; \
			echo '#'; \
			echo '# ERROR : please check your docbook documents using xmllint first'; \
			echo '##'; \
			exit 1; \
		fi; \
	done;
	#
	# Done
	##

clean:
	##
	# Cleaning build environment
	#
	@-rm -rf $(TARGET)*
	@-rm -rf guide/
	# Done
	##

clobber: clean
	##
	# Hard cleaning build environment
	#
	@[ -L xsl/docbook ] && rm xsl/docbook || true
	@[ -L xsl/db2latex/xsl ] && rm xsl/db2latex/xsl || true
	@[ -d xsl/db2latex ] && rmdir xsl/db2latex || true
	# Done
	##

debian: debianhtml debianpdf

debianhtml:
	@echo "=====> Intalling necessary packages"
	@sudo apt-get install docbook-xml docbook-xsl libxml2-utils xsltproc
	@echo "=====> Link to docbook"
	@if [ ! -L $(DOCBOOK_XSL) ]; then \
		if [ -d /usr/share/xml/docbook/stylesheet/nwalsh ]; then \
			ln -s /usr/share/xml/docbook/stylesheet/nwalsh $(DOCBOOK_XSL) ; \
		elif [ -d /usr/share/xml/docbook/stylesheet/docbook-xsl ]; then \
			ln -s /usr/share/xml/docbook/stylesheet/docbook-xsl $(DOCBOOK_XSL) ; \
		fi; \
	else \
		echo "docbook link already done" ; \
	fi
	@make html

debianpdfold:
	@echo "=====> Intalling necessary packages"
	@sudo apt-get install db2latex-xsl texlive-latex-recommended # Was tetex-extra latex-ucs
	@echo "Making db2latex dir"
	@[ ! -d xsl/db2latex ] && mkdir xsl/db2latex || echo "db2latex dir already done"
	@echo "=====> Link to docbook"
	@[ ! -L xsl/db2latex/xsl ] && ln -s /usr/share/xml/docbook/stylesheet/db2latex/latex xsl/db2latex/xsl || echo "docbook link already done"
	@make pdf

debianpdf:
	@echo "=====> Intalling necessary packages"
	@sudo apt-get install xsltproc fop
	@make pdffop

#xsltproc --xinclude -o tmp.fo $(XSL)/fo-stylesheet.xsl $(XML)/user_guide/user_guide_full.xml
#$(FOP) -fo tmp.fo -pdf help_guide/user_guide.pdf
