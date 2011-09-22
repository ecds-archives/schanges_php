<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.openarchives.org/OAI/2.0/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:xq="http://metalab.unc.edu/xql"
  xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
  version="1.0">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  <xsl:param name="prefix"/>

  <!-- need unqualified dublin core for Primo -->
  <xsl:param name="qualified">false</xsl:param>

  <xsl:include href="../xslt/teiheader-dc2.xsl"/>
  <xsl:include href="xmldbOAI/xsl/response.xsl"/>


  <!-- list identifiers : header information only -->
  <xsl:template match="TEI" mode="ListIdentifiers">
    <xsl:call-template name="header"/>
  </xsl:template>

  <!-- get or list records : full information (header & metadata) -->
  <xsl:template match="TEI">
    <record>
      <xsl:call-template name="header"/>
      <metadata>
        <oai_dc:dc>
          <xsl:apply-templates select=".//tei:div2"/>
	  <xsl:apply-templates select=".//tei:fileDesc"/>

          <!--        <dc:identifier>PURL</dc:identifier> -->
          <dc:type>Text</dc:type>
          <dc:format>text/xml</dc:format>
<!--	  <xsl:call-template name="identifier"/> -->
        </oai_dc:dc>
      </metadata>
    </record>
  </xsl:template>

  <xsl:template name="header">
    <xsl:element name="header">            
    <xsl:element name="identifier">
      <!-- identifier prefix is passed in as a parameter; should be defined in config file -->
      <xsl:value-of select="concat($prefix, .//tei:div2/@xml:id)" /> 
    </xsl:element>
    <xsl:element name="datestamp">
      <xsl:value-of select=".//tei:div2/LastModified"/>
    </xsl:element>

    <!-- not using sets for SC at this time -->
    <!-- get setSpec names (must match what is in config.xml) -->
    <!-- type : article or review? -->
    <!-- FIXME: fix when we know what sets are for schanges -->
    <!-- <setSpec><xsl:value-of select="concat(@type,
    's')"/></setSpec> -->
    <!-- volume # -->
   <!-- <xsl:apply-templates select="bibl/biblScope[@type='volume']"
   mode="set"/> -->

  </xsl:element>
</xsl:template>


<!-- convert volume # from biblScope into correct setSpec -->
<!--
<xsl:template match="biblScope[@type='volume']" mode="set">
  <xsl:variable name="num">
    <xsl:choose>
      <xsl:when test="contains(., ' ')">
        <xsl:value-of select="substring-after(., 'vol. ')"/>        
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="substring-after(., 'vol.')"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:call-template name="setSpec">
    <xsl:with-param name="name" select="concat('Volume ', $num)"/>
  </xsl:call-template>
</xsl:template>
-->
<!-- article title -->
<!-- this should be in teiheader-dc2.xsl 
<xsl:template match="div2/head">
  <xsl:element name="dc:title"><xsl:value-of select="."/></xsl:element>
</xsl:template> -->

<!-- source = original publication information -->
<!-- this should be in teiheader-dc2.xsl 
<xsl:template match="div2/bibl">
  <xsl:element name="dc:source">
    <xsl:value-of select="title"/>, <xsl:value-of
    select="biblScope[@type='volume']"/>, <xsl:value-of
    select="biblScope[@type='issue']"/>, <xsl:value-of
    select="biblScope[@type='pages']"/>.</xsl:element> -->
    <!-- pick up the date -->
<!--    <xsl:apply-templates select="date"/> 
  </xsl:template> -->


  <!-- article date -->
<!-- this should be in teiheader-dc2.xsl 
  <xsl:template match="div2/docDate">
    <xsl:element name="dc:date"><xsl:value-of
    select="@value"/></xsl:element>
    <xsl:element name="dc:source"><xsl:value-of select="."/></xsl:element>
  </xsl:template> -->

  <!-- contributor -->
<!-- this should be in teiheader-dc2.xsl 
  <xsl:template match="div2/docAuthor">
    <xsl:element name="dc:contributor"><xsl:value-of select="name/@reg"/></xsl:element>
  </xsl:template> -->

  <!-- publisher -->
<!-- this should be in teiheader-dc2.xsl 
  <xsl:template match="publicationStmt">
    <xsl:element name="dc:publisher">  <xsl:value-of select="publisher"/>, <xsl:value-of
    select="pubPlace"/>. <xsl:value-of select="date"/>: <xsl:value-of
    select="address/addrLine"/>.</xsl:element> -->
    <!-- pick up rights statement -->
<!--    <xsl:apply-templates/>
  </xsl:template> -->

  <!-- rights -->
<!-- this should be in teiheader-dc2.xsl 
  <xsl:template match="availability">
    <xsl:element name="dc:rights"><xsl:value-of select="p"/></xsl:element>
  </xsl:template> -->

  <!-- subject --> 
<!-- this is not the subject
  <xsl:template match="seriesStmt/title">
    <xsl:element name="dc:subject"><xsl:value-of select="."/></xsl:element>
  </xsl:template> -->

<!-- dc:description was here -->

<!-- identifier -->
<!-- Note: this url is not yet firmly in place, but eventually it should be ... -->
<!-- this should be in teiheader-dc2.xsl 
<xsl:template name="identifier">
  <xsl:element
    name="dc:identifier">http://beck.library.emory.edu/southernchanges/article.php?id=<xsl:value-of select="@id"/></xsl:element>
</xsl:template> -->


<!-- default: ignore anything not explicitly selected (but do process
     child nodes) -->
<!-- DEBUG:testing
<xsl:template match="node()">
  <xsl:apply-templates/>
</xsl:template>
-->
<!-- DEBUG:testing
<xsl:template match="text()|@*"/>
-->

<!-- hide tamino messages -->
<!-- Don't need this?
<xsl:template match="xq:result|message|cursor"/>
-->


</xsl:stylesheet>
