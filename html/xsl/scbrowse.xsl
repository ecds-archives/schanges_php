<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0"
	xmlns:html="http://www.w3.org/TR/REC-html40" 
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xql="http://metalab.unc.edu/xql/">

  <xsl:output method="html"/>

<xsl:include href="teihtml-tables.xsl" />
<xsl:template match="/">
    <xsl:apply-templates select="//div2" />

<!-- print out the content-->
<xsl:template match="div2">
<!-- get everything under this node -->
  <xsl:apply-templates/> 
</xsl:template>    

<!-- display the title -->
<xsl:template match="head">
  <xsl:element name="h1">
     <!-- explicitly colorize keywords in the title -->
   <xsl:call-template name="default"/>
  </xsl:element>
</xsl:template>

<xsl:template match="docAuthor">
  <xsl:element name="h2">
    <xsl:value-of select="." />
  </xsl:element>
</xsl:template>

<xsl:template match="docDate">
  <xsl:element name="h2">
    <xsl:value-of select="." />
  </xsl:element>
</xsl:template>

<xsl:template match="argument">
  <xsl:element name="p">  
  <xsl:element name="i">
    <xsl:apply-templates />
  </xsl:element>
</xsl:element>
</xsl:template>

<xsl:template match="p">
  <xsl:element name="p">
    <xsl:apply-templates/> 
  </xsl:element>
</xsl:template>

<xsl:template match="q">
  <xsl:element name="blockquote">
    <xsl:apply-templates/> 
  </xsl:element>
</xsl:template>

<!-- convert rend tags to their html equivalents 
     so far, converts: center, italic 		  -->
<xsl:template match="//*[@rend]">
  <xsl:choose>
    <xsl:when test="@rend='center'">
      <xsl:element name="center">
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:when>
    <xsl:when test="@rend='italic'">
      <xsl:element name="i">
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="lb">
  <xsl:element name="br" />
</xsl:template>

</xsl:stylesheet>
