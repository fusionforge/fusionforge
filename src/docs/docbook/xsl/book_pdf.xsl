<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="include/common_pdf.xsl" />
	<xsl:variable name="toc.section.depth">1</xsl:variable>
	<xsl:variable name="admon.graphics.path">../../xsl/db2latex/xsl/figures</xsl:variable>
	<xsl:variable name="latex.documentclass.book">a4paper,10pt,oneside</xsl:variable>

	<xsl:template match="book">
		<!-- book:1: generate.latex.book.preamble -->
		<xsl:call-template name="generate.latex.book.preamble"/>
		<!-- book:2: output title information     -->
		<xsl:text>\title{</xsl:text>
			<xsl:apply-templates select="title|bookinfo/title"/>
			<xsl:apply-templates select="subtitle|bookinfo/subtitle"/>
		<xsl:text>}&#10;</xsl:text>
		<!-- book:3: output author information     -->
		<xsl:text>\author{</xsl:text>
		<xsl:choose>
			<xsl:when test="bookinfo/authorgroup">
				<xsl:apply-templates select="bookinfo/authorgroup"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:for-each select="bookinfo">
					<xsl:call-template name="authorgroup"/>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>}&#10;</xsl:text>
		<!-- book:4: dump any preamble after author  -->
		<xsl:value-of select="$latex.book.afterauthor"/>
		<!-- book:5: set some counters  -->
		<xsl:text>&#10;\setcounter{tocdepth}{</xsl:text><xsl:value-of select="$toc.section.depth"/><xsl:text>}&#10;</xsl:text>
		<xsl:text>&#10;\setcounter{secnumdepth}{</xsl:text><xsl:value-of select="$section.depth"/><xsl:text>}&#10;</xsl:text>
		<!-- book:6: dump the begin document command  -->
		<xsl:value-of select="$latex.book.begindocument"/>
		<!-- book:7: include external Cover page if specified -->
		<xsl:if test="$latex.titlepage.file != ''">
			<xsl:text>&#10;\InputIfFileExists{</xsl:text><xsl:value-of select="$latex.titlepage.file"/>
			<xsl:text>}{\typeout{WARNING: Using cover page </xsl:text>
			<xsl:value-of select="$latex.titlepage.file"/>
			<xsl:text>}}</xsl:text>
		</xsl:if>
		<!-- book:7b: maketitle and set up pagestyle -->
		<xsl:value-of select="$latex.maketitle"/>
		<!-- book:8: - APPLY TEMPLATES -->
		<xsl:apply-templates select="bookinfo"/>
		<xsl:call-template name="toc" />
		<xsl:call-template name="content-templates-rootid"/>
		<!-- book:9:  call map.end -->
		<xsl:call-template name="map.end"/>
	</xsl:template>

	<xsl:template match="book/article">
		<!-- Get and output article title -->
		<xsl:variable name="article.title">
			<xsl:choose>
				<xsl:when test="./title"> 
					<xsl:apply-templates select="./title"/>
				</xsl:when>
				<xsl:when test="./articleinfo/title">
					<xsl:apply-templates select="./articleinfo/title"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="./artheader/title"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:text>\chapter{</xsl:text><xsl:value-of select="$article.title"/><xsl:text>}&#10;</xsl:text>
		<!-- Display date information -->
		<xsl:variable name="article.date">
			<xsl:apply-templates select="./artheader/date|./articleinfo/date"/>
		</xsl:variable>
		<xsl:if test="$article.date!=''">
			<xsl:text>{</xsl:text>
			<xsl:value-of select="$article.date"/>
			<xsl:text>}\par&#10;</xsl:text>
		</xsl:if>
		<!-- Display author information -->
		<xsl:text>{</xsl:text>
		<xsl:value-of select="$latex.book.article.header.style"/>
		<xsl:text>{</xsl:text>
		<xsl:choose>
			<xsl:when test="articleinfo/authorgroup">
				<xsl:apply-templates select="articleinfo/authorgroup"/>
			</xsl:when>
			<xsl:when test="artheader/authorgroup">
				<xsl:apply-templates select="artheader/authorgroup"/>
			</xsl:when>
			<xsl:when test="articleinfo/author">
				<xsl:for-each select="artheader">
					<xsl:call-template name="authorgroup"/>
				</xsl:for-each>
			</xsl:when>
			<xsl:when test="artheader/author">
				<xsl:for-each select="artheader">
					<xsl:call-template name="authorgroup"/>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="authorgroup"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:text>}}\par&#10;</xsl:text>
		<xsl:apply-templates select="artheader|articleinfo" mode="article.within.book"/>
		<xsl:call-template name="content-templates"/>
	</xsl:template>

</xsl:stylesheet>
