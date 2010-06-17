<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="include/common_pdf.xsl" />
 	<xsl:variable name="toc.section.depth">2</xsl:variable>
	<xsl:variable name="admon.graphics.path">../../xsl/db2latex/xsl/figures</xsl:variable>
	<xsl:variable name="latex.documentclass.article">a4paper,10pt,oneside</xsl:variable> 

	<xsl:template match="article">
		<xsl:call-template name="generate.latex.article.preamble"/>
		<xsl:text>&#10;\setcounter{tocdepth}{</xsl:text><xsl:value-of select="$toc.section.depth"/><xsl:text>}&#10;</xsl:text>
		<xsl:text>&#10;\setcounter{secnumdepth}{</xsl:text><xsl:value-of select="$section.depth"/><xsl:text>}&#10;</xsl:text>
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
		<xsl:text>\title{</xsl:text>
		<xsl:value-of select="$latex.article.title.style"/>
		<xsl:text>{</xsl:text>
		<xsl:value-of select="$article.title"/>
		<xsl:text>}}&#10;</xsl:text>
		<!-- Display date information -->
		<xsl:variable name="article.date">
			<xsl:apply-templates select="./artheader/date|./articleinfo/date"/>
		</xsl:variable>
		<xsl:if test="$article.date!=''">
			<xsl:text>\date{</xsl:text>
			<xsl:value-of select="$article.date"/>
			<xsl:text>}&#10;</xsl:text>
		</xsl:if>
		<!-- Display author information -->
		<xsl:text>\author{</xsl:text>
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
		<xsl:text>}&#10;</xsl:text>
		<!-- Display  begindocument command -->
		<xsl:call-template name="map.begin"/>
		<xsl:value-of select="$latex.maketitle"/>
		<xsl:apply-templates select="artheader|articleinfo" mode="standalone.article"/>
		<xsl:call-template name="toc"/>
		<xsl:call-template name="content-templates-rootid"/>
		<xsl:call-template name="map.end"/>
	</xsl:template>	

</xsl:stylesheet>
