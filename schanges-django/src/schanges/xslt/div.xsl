<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
               version="1.0"
                xmlns:tei="http://www.tei-c.org/ns/1.0">

<xsl:include href="form.xsl"/>

  <xsl:output method="xml"/>

  <xsl:template match="/">
    <div>    <!-- need div wrapper to make well-formed xml for eulcore -->
    <xsl:apply-templates select="tei:text"/>
    </div>
  </xsl:template>

  <xsl:template match="tei:div">
    <div class="letter">
      <!-- ignore docAuthor in letter mode -->
      <xsl:apply-templates/>
    </div>
  </xsl:template>


</xsl:stylesheet>
 