<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:dcterms="http://purl.org/dc/terms"
                version="1.0">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  <xsl:variable name="baseurl">http://beck.library.emory.edu/</xsl:variable>
  <xsl:variable name="siteurl">southernchanges</xsl:variable>

  <xsl:template match="/">
    <dc>
      <xsl:apply-templates select="//result"/>
<!--    <xsl:apply-templates select="//teiHeader"/>
    <xsl:apply-templates select="//issue-id"/>
    <xsl:apply-templates select="//article"/> -->
    <dc:type>Text</dc:type>
    <dc:format>text/xml</dc:format>
    </dc>
  </xsl:template>

  <xsl:template match="titleStmt/title">
    <xsl:element name="dc:title">
      <xsl:value-of select="."/>
    </xsl:element>
    <xsl:call-template name="issueid"/>
  </xsl:template>

  <xsl:template name="issueid" match="result//issue-id">
    <xsl:element name="dc:identifier">
      <xsl:value-of select="$baseurl"/><xsl:value-of
      select="$siteurl"/>/article-list.php?<xsl:value-of select="@id"/>      
    </xsl:element>
  </xsl:template>

  <xsl:template match="titleStmt/author">
    <xsl:element name="dc:creator">
      <xsl:text>Lewis H. Beck Center</xsl:text>
    </xsl:element>
  </xsl:template>

  <xsl:template match="titleStmt/editor">
    <xsl:element name="dc:contributor">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="publicationStmt/publisher">
    <xsl:element name="dc:publisher">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <!-- electronic publication date: is this the right date to use? -->
  <xsl:template match="publicationStmt/date">
    <xsl:element name="dc:date">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>


  <!-- ignore for now; do these fit anywhere? -->
  <xsl:template match="publicationStmt//address/addrLine"/>
  <xsl:template match="publicationStmt/pubPlace"/>
  <xsl:template match="respStmt"/>

  <xsl:template match="availability">
    <xsl:element name="dc:rights">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="seriesStmt/title">
    <xsl:element name="dcterms:isPartOf">
      <!-- fixme: should we specify isPartOf? -->
      <xsl:apply-templates/>
    </xsl:element>
    <xsl:element name="dc:identifier">
      <xsl:value-of select="$baseurl"/><xsl:value-of
      select="$siteurl"/>      
    </xsl:element>
  </xsl:template>



  <xsl:template match="sourceDesc/bibl">
    <xsl:element name="dc:source">
      <!-- process all elements, in this order. -->
     <!-- <xsl:apply-templates select="author"/> not using this -->
      <xsl:apply-templates select="title"/>
     <!-- <xsl:apply-templates select="editor"/> -->
      <xsl:apply-templates select="pubPlace"/>
      <xsl:apply-templates select="publisher"/>
      <xsl:apply-templates select="biblScope[@type='volume']"/>
      <xsl:apply-templates select="biblScope[@type='issue']"/>
      <xsl:apply-templates select="date"/>
      <!-- in case source is in plain text, without tags -->
    <!--  <xsl:apply-templates select="text()"/> -->
    </xsl:element>
  </xsl:template>

  <!-- formatting for bibl elements, to generate a nice citation. -->
 
  <xsl:template match="bibl/title"><xsl:apply-templates/>. </xsl:template>
  <xsl:template match="bibl/pubPlace">
          <xsl:apply-templates/>:  </xsl:template>
  <xsl:template match="bibl/publisher">
      <xsl:apply-templates/>, </xsl:template>
  <xsl:template
      match="bibl/biblScope[@type='volume']"><xsl:apply-templates/>, </xsl:template>
  <xsl:template
      match="bibl/biblScope[@type='issue']"><xsl:apply-templates/>, </xsl:template>
  <xsl:template match="bibl/date"><xsl:apply-templates/>. </xsl:template>
  

  <xsl:template match="article">
    <xsl:element name="dcterms:description.tableOfContents">
      <xsl:for-each select=".">
	<xsl:apply-templates select="name"/>, <xsl:apply-templates
	select="head"/>, <xsl:apply-templates select="docDate"/>.</xsl:for-each>
    </xsl:element>
      <xsl:call-template name="dcidentifier"/>
  </xsl:template>

<!-- FIXME: this template doesn't work -->
<!-- handle multiple names -->
  <xsl:template match="name">
    <xsl:choose>
      <xsl:when test="position() = 1"/>
  <xsl:when test="position() = last()">
        <xsl:text> and </xsl:text>
      </xsl:when>
    <xsl:otherwise>
	<xsl:text>, </xsl:text>
      </xsl:otherwise>
  </xsl:choose>
  </xsl:template>

<!-- add a space after titles in the head -->
  <xsl:template match="head/title">
    <xsl:apply-templates/><xsl:text> </xsl:text>
  </xsl:template>

<!-- FIXME: this one doesn't work -->
  <xsl:template name="dcidentifier">
    <xsl:element name="dc:identifier">
    <xsl:for-each select="article">
      <xsl:value-of select="$baseurl"/><xsl:value-of select="$siteurl"/><xsl:text>/article.php?id="</xsl:text><xsl:apply-templates select="@id"/><xsl:text>"</xsl:text>
    </xsl:for-each>
    </xsl:element>
  </xsl:template>
 


  <!-- ignore these: encoding specific information -->
  <xsl:template match="next"/>
  <xsl:template match="prev"/>
  <xsl:template match="encodingDesc/projectDesc"/>
  <xsl:template match="encodingDesc/tagsDecl"/>
  <xsl:template match="encodingDesc/refsDecl"/>
  <xsl:template match="encodingDesc/editorialDecl"/>
  <xsl:template match="revisionDesc"/>

  <!-- normalize space for all text nodes -->
  <xsl:template match="text()">
    <xsl:value-of select="normalize-space(.)"/>
  </xsl:template>

</xsl:stylesheet>
