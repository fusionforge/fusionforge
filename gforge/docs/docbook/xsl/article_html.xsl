<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<xsl:import href="docbook/html/docbook.xsl" />

<xsl:param name="toc.section.depth">2</xsl:param>
<xsl:param name="autotoc.label.separator" select="'. '"/>
<xsl:param name="generate.index" select="1"/>
<xsl:param name="generate.toc">
article toc,figure
section toc,figure
</xsl:param>

<xsl:output method="html"
            encoding="UTF-8"
            indent="no"/>

<xsl:param name="section.autolabel" select="1"/>
<xsl:param name="generate.section.toc.level" select="1"/>

</xsl:stylesheet>
