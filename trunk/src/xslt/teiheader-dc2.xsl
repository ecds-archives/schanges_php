<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:dcterms="http://purl.org/dc/terms" version="1.0"
		xmlns:exist="http://exist.sourceforge.net/NS/exist"
		>
  <!-- This stylesheet creates Dublin core metadata for the article
  level page -->
  <xsl:output method="xml" omit-xml-declaration="yes"/>
  <xsl:variable name="baseurl">http://beck.library.emory.edu/</xsl:variable>
  <xsl:variable name="siteurl">southernchanges</xsl:variable>

  <xsl:template match="/">
    <dc>
      <xsl:apply-templates select="//TEI"/>
    <dc:type>Text</dc:type>
    <dc:format>text/xml</dc:format>
    </dc>
  </xsl:template>

  <xsl:template match="//TEI">
    <xsl:apply-templates select=".//div2"/>
    <xsl:apply-templates select=".//fileDesc"/>
  </xsl:template>

  <xsl:template match="div2">
    <xsl:element name="dc:title">
      <xsl:apply-templates select="head"/>
    </xsl:element>
      <xsl:for-each select=".//docAuthor">
    <xsl:element name="dc:creator">
	<xsl:apply-templates select="name/@reg"/>
    </xsl:element>
      </xsl:for-each> 
    <xsl:element name="dc:identifier">
      <xsl:value-of select="$baseurl"/><xsl:value-of
      select="$siteurl"/><xsl:text>/article.php?id=</xsl:text><xsl:apply-templates select="@id"/>
    </xsl:element>
  </xsl:template>


  <xsl:template match="fileDesc">
  <xsl:variable name="date">
    <xsl:apply-templates select=".//sourceDesc/bibl/date"/>
  </xsl:variable>
     <xsl:element name="dcterms:isPartOf">
      <xsl:value-of select="titleStmt/title"/><xsl:text>, </xsl:text><xsl:value-of select="$date"/> 
    </xsl:element>
    <xsl:element name="dcterms:isPartOf">
      <xsl:value-of select="$baseurl"/><xsl:value-of
      select="$siteurl"/>/article-list.php?id=<xsl:apply-templates
      select="issueid/@id"/>      
    </xsl:element>

    <xsl:element name="dc:contributor">
      <xsl:text>Southern Regional Council</xsl:text>
    </xsl:element>

    <xsl:element name="dc:contributor">
      <xsl:text>Lewis H. Beck Center</xsl:text>
    </xsl:element>

    <xsl:element name="dc:publisher">
      <xsl:value-of select="publicationStmt/publisher"/>
    </xsl:element>

    <xsl:element name="dcterms:issued">
      <xsl:apply-templates select="publicationStmt/date"/>
    </xsl:element>

  <!-- electronic publication date: Per advice of LA -->
    <xsl:element name="dcterms:created">
      <xsl:value-of select="sourceDesc/bibl/date/@value"/>
    </xsl:element>

    <xsl:element name="dc:rights">
      <xsl:apply-templates select="publicationStmt/availability/p"/>
    </xsl:element>

    <xsl:element name="dcterms:isPartOf">
      <xsl:apply-templates select="seriesStmt/title"/>
    </xsl:element>

    <xsl:element name="dcterms:isPartOf">
      <xsl:value-of select="$baseurl"/><xsl:value-of
      select="$siteurl"/>      
    </xsl:element>

    <xsl:element name="dc:source">
      <!-- process all elements, in this order. -->
      <xsl:apply-templates select="sourceDesc/bibl/title"/>
      <xsl:apply-templates select="sourceDesc/bibl/pubPlace"/>
      <xsl:apply-templates select="sourceDesc/bibl/publisher"/>
      <xsl:apply-templates select="sourceDesc/bibl/biblScope[@type='volume']"/>
      <xsl:apply-templates select="sourceDesc/bibl/biblScope[@type='issue']"/>
      <xsl:apply-templates select="sourceDesc/bibl/date"/>
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
  

<!-- handle multiple names -->
  <xsl:template name="multiname" match="name">
    <xsl:choose>
      <xsl:when test="position() = 1"></xsl:when>
  <xsl:when test="position() = last()">
        <xsl:text> and </xsl:text>
      </xsl:when>
    <xsl:otherwise>
	<xsl:text>, </xsl:text>
      </xsl:otherwise>
  </xsl:choose>
  <xsl:apply-templates/>
  </xsl:template>

<!-- add a space after titles in the head -->
  <xsl:template match="head/title">
    <xsl:apply-templates/><xsl:text> </xsl:text>
  </xsl:template>

<!-- make a line break a space in the head -->
  <xsl:template match="head/lb">
    <xsl:text> </xsl:text>
  </xsl:template>

  <!-- ignore for now; do these fit anywhere? -->
  <xsl:template match="publicationStmt//address/addrLine"/>
  <xsl:template match="publicationStmt/pubPlace"/>
  <xsl:template match="respStmt"/>
  <xsl:template match="profileDesc/langUsage/p"/>

  <!-- ignore these: encoding specific information -->
  <xsl:template match="div2/p"/>
  <xsl:template match="div2/div3"/>
  <xsl:template match="issue-id/head"/>
  <xsl:template match="issueid/head"/>
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
