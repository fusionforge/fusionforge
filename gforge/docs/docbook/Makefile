SRC = docbook/
TARGET = build/
XSL = xsl/
DOCBOOK_XSL = ${XSL}docbook/
DB2LATEX_XSL = ${XSL}db2latex/

BOOKS=gforge_manual
ARTICLES=administration_guide user_guide contribution_guide installation_guide

all: clean html html-chunk pdf

html: validate prepare
	##
	# Generating HTML output
	#
	@for book in ${BOOKS}; do \
		echo '# . Generating HTML output for book '$$book; \
		xsltproc --xinclude --output ${TARGET}$$book/$$book.plain.html ${XSL}book_html.xsl ${SRC}$$book.xml; \
		tar -czf ${TARGET}$$book/$$book.plain.tar.gz ${TARGET}$$book/$$book.plain.html; \
	done;
	
	@for article in ${ARTICLES}; do \
		echo '# . Generating HTML output for article '$$article; \
		xsltproc --xinclude --output ${TARGET}$$article/$$article.plain.html ${XSL}article_html.xsl ${SRC}$$article/$$article.xml; \
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
		xsltproc --xinclude --stringparam base.dir ${TARGET}$$book/$$book.chunk/ ${XSL}book_html_chunk.xsl ${SRC}$$book.xml &> /dev/null; \
		tar -czf ${TARGET}$$book/$$book.chunk.tar.gz ${TARGET}$$book/$$book.chunk; \
	done;

	@for article in ${ARTICLES}; do \
		echo '# . Generating HTML chunked output for article '$$article; \
		test -d ${TARGET}$$article/$$article || mkdir ${TARGET}$$article/$$article.chunk; \
		xsltproc --xinclude --stringparam base.dir ${TARGET}$$article/$$article.chunk/ ${XSL}article_html_chunk.xsl ${SRC}$$article/$$article.xml &> /dev/null; \
		tar -czf ${TARGET}$$article/$$article.chunk.tar.gz ${TARGET}$$article/$$article.chunk; \
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
		xsltproc --xinclude --output ${TARGET}$$book/$$book.tex ${XSL}book_pdf.xsl ${SRC}$$book.xml &> /dev/null; \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex &> /dev/null;); \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex &> /dev/null;); \
		(cd ${TARGET}$$book; pdflatex -interaction=batchmode $$book.tex &> /dev/null;); \
		(cd ${TARGET}$$book; rm -f *.aux *.glo *.idx *.log *.out *.tex *.toc &> /dev/null;); \
	done;
	
	@for article in ${ARTICLES}; do \
		echo '# . Generating PDF output for article '$$article; \
		xsltproc --xinclude --output ${TARGET}$$article/$$article.tex ${XSL}article_pdf.xsl ${SRC}$$article/$$article.xml &> /dev/null; \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex &> /dev/null;); \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex &> /dev/null;); \
		(cd ${TARGET}$$article; pdflatex -interaction=batchmode $$article.tex &> /dev/null;); \
		(cd ${TARGET}$$article; rm -f *.aux *.glo *.idx *.log *.out *.tex *.toc &> /dev/null;); \
	done;
	#
	# Done
	##

prepare:
	##
	# Preparing build environment
	#
	@test -d ${TARGET} || mkdir ${TARGET};
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
		if (xmllint --xinclude --noout --postvalid ${SRC}$$book.xml &> /dev/null); then \
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
		if (xmllint --xinclude --noout --postvalid ${SRC}$$article/$$article.xml &> /dev/null); then \
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
	# Done
	##
