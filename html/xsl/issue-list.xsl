<?xml version="1.0" encoding="ISO-8859-1"?> 

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/TR/REC-html40" version="1.0"
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xq="http://metalab.unc.edu/xq/">


<xsl:param name="mode">article</xsl:param>
<!-- param for flat mode: all volumes or single volume -->
<xsl:param name="vol">all</xsl:param>
<xsl:variable name="mode_name">Browse</xsl:variable> 
<xsl:variable name="xslurl">&#x0026;_xslsrc=xsl:stylesheet/</xsl:variable>
<xsl:variable name="query"><xsl:value-of select="ino:response/xq:query"/></xsl:variable>
<!-- <xsl:variable name="total_count" select="count(//div1 |
//div2[figure])" /> -->

<xsl:output method="html"/>
<xsl:template match="/">

<!-- begin body -->
<xsl:element name="body">
  <xsl:apply-templates select="//result"/>
</xsl:element>
</xsl:template>

<xsl:template match="result">
  <xsl:element name="ul">
    <xsl:attribute name="class">contents</xsl:attribute>
  <xsl:element name="li">
    <xsl:element name="a">
      <xsl:attribute name="href">articlelist.php?id=<xsl:value-of
      select="@id"/>&amp;docdate=<xsl:value-of select="docdate/@value"/></xsl:attribute>
      <xsl:apply-templates select="head"/>
    </xsl:element> <!-- a -->
  </xsl:element> <!-- li -->
  </xsl:element> <!-- ul -->
</xsl:template> <!--result -->
</xsl:stylesheet>
