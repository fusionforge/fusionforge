<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<xsl:import href="docbook/html/docbook.xsl" />

<xsl:param name="toc.section.depth">2</xsl:param>
<xsl:param name="autotoc.label.separator" select="'. '"/>
<xsl:param name="generate.index" select="1"/>
<xsl:param name="generate.toc">
book toc,figure
chapter toc,figure
article toc,figure
section toc,figure
</xsl:param>

<xsl:param name="section.autolabel" select="1"/>
<xsl:param name="generate.section.toc.level" select="2"/>

</xsl:stylesheet>
