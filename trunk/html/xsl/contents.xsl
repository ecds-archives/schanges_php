<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
 <xsl:template match="/">
<ul>
<xsl:apply-templates select="//div1" />
</ul>
</xsl:template>

<xsl:template match="div1">
<li><xsl:apply-templates select="head" />
<ul><xsl:apply-templates select="div2" />
</ul>
</li>
</xsl:template>
<xsl:template match="div2">
<li>
<xsl:element name="a">
<xsl:attribute name="href">browse.php?id=<xsl:value-of select="@id"/></xsl:attribute>
<xsl:value-of select="@type"/></xsl:element>:
<xsl:apply-templates select="head"/><br/>
<xsl:apply-templates select="docAuthor"/><br/>
<xsl:apply-templates select="docDate"/>
</li>
</xsl:template>
<xsl:template match="title">
<i><xsl:apply-templates /></i>
</xsl:template>




</xsl:stylesheet>