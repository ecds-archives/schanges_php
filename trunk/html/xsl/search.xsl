<?xml version="1.0" encoding="ISO-8859-1"?> 

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/TR/REC-html40" version="1.0"
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xql="http://metalab.unc.edu/xql/">

<xsl:include href="ilnshared.xsl"/>

<xsl:param name="term">0</xsl:param>
<xsl:param name="term2">0</xsl:param>
<xsl:param name="term3">0</xsl:param>

<!-- construct string to pass search term values to browse via url -->
<xsl:variable name="term_string"><xsl:if test="$term != 0">&amp;term=<xsl:value-of select="$term"/></xsl:if><xsl:if test="$term2 != 0">&amp;term2=<xsl:value-of select="$term2"/></xsl:if><xsl:if test="$term3 != 0">&amp;term3=<xsl:value-of select="$term3"/></xsl:if></xsl:variable>

<xsl:output method="html"/>  

<xsl:template match="/"> 
    <!-- returning at the div2 (article/illustration) level -->
    <!-- pull out table of contents information -->
    <xsl:apply-templates select="//div2" />
</xsl:template> <!-- / -->

</xsl:stylesheet>
