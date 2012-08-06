<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../db2latex/xsl/docbook.xsl"/>
	
	<xsl:output method="text" encoding="UTF-8" indent="yes"/>

	<xsl:variable name="latex.math.support">1</xsl:variable>
	<xsl:variable name="latex.use.babel">1</xsl:variable>
	<xsl:variable name="latex.use.hyperref">1</xsl:variable>
	<xsl:variable name="latex.use.fancyvrb">1</xsl:variable>
	<xsl:variable name="latex.use.fancyhdr">1</xsl:variable>
	<xsl:variable name="latex.use.fancybox">1</xsl:variable>
	<xsl:variable name="latex.ulink.protocols.relaxed">1</xsl:variable>
	<xsl:variable name="latex.use.subfigure">1</xsl:variable>
	<xsl:variable name="latex.pdf.support">1</xsl:variable>
	<xsl:variable name="latex.entities">catcode</xsl:variable>
	<xsl:variable name="latex.fontenc">T1</xsl:variable>
	<xsl:variable name="latex.book.preamble.pre">
		<xsl:text>\usepackage[scaled=0.9]{helvet}&#10;</xsl:text>
		<xsl:text>\usepackage[scaled=0.9]{courier}&#10;</xsl:text>
	</xsl:variable>
	<xsl:variable name="document.xml.language">en-US</xsl:variable>
	<xsl:variable name="ulink.show">1</xsl:variable>
	<xsl:variable name="latex.inputenc">utf8</xsl:variable>

<xsl:template match="authorgroup" name="authorgroup">
<xsl:param name="person.list" select="./author|./corpauthor|./othercredit|./editor"/>
<xsl:call-template name="person.name.list">
<xsl:with-param name="person.list" select="$person.list"/>
</xsl:call-template>
</xsl:template>


<xsl:template name="person.name.list">
<!-- Return a formatted string representation of the contents of
 the current element. The current element must contain one or
 more AUTHORs, CORPAUTHORs, OTHERCREDITs, and/or EDITORs.

 John Doe
 or
 John Doe and Jane Doe
 or
 John Doe, Jane Doe, and A. Nonymous
-->
<xsl:param name="person.list" select="author|corpauthor|othercredit|editor"/>
	<xsl:param name="person.count" select="count($person.list)"/>
	<xsl:param name="count" select="1"/>

	<xsl:call-template name="person.name">
		<xsl:with-param name="node" select="$person.list[position()=$count]"/>
	</xsl:call-template><xsl:if test="$count &lt; $person.count"><xsl:text> \and </xsl:text><xsl:call-template name="person.name.list">
		<xsl:with-param name="person.list" select="$person.list"/>
		<xsl:with-param name="person.count" select="$person.count"/>
		<xsl:with-param name="count" select="$count+1"/>
	</xsl:call-template>
</xsl:if>
</xsl:template><!-- person.name.list -->


</xsl:stylesheet>
