<?xml version="1.0" encoding="ISO-8859-1"?>  

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
	xmlns:html="http://www.w3.org/TR/REC-html40" 
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xql="http://metalab.unc.edu/xql/">


<xsl:include href="teihtml-tables.xsl"/>
 <xsl:output method="html"/>  

<xsl:template match="/"> 
  <xsl:apply-templates select="//div2" />

   <!-- links to next & previous titles (if present) -->
  <xsl:call-template name="next-prev" />

</xsl:template> 


<!-- print out the content-->
<xsl:template match="div2">
<!-- get everything under this node -->
  <xsl:apply-templates/> 
</xsl:template>

<!-- display the title -->
<xsl:template match="head">
  <xsl:element name="h1">
   <xsl:apply-templates />
  </xsl:element>
</xsl:template>

<xsl:template match="byline">
  <xsl:element name="i">
    <xsl:value-of select="."/>
  </xsl:element>
</xsl:template>
<xsl:template match="docDate">
<xsl:element name="p">
  <xsl:apply-templates/>
</xsl:element>
</xsl:template>
<xsl:template match="bibl">
<xsl:element name="p">
  <xsl:apply-templates/>
</xsl:element>
</xsl:template>

<xsl:template match="div3">
<xsl:choose>
  <xsl:when test="@type='About'">
    <xsl:element name="i">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:when>
</xsl:choose>
</xsl:template>

<xsl:template match="p/title">
  <xsl:element name="i">
    <xsl:apply-templates />
  </xsl:element>
</xsl:template>  

<xsl:template match="bibl/title">
  <xsl:element name="i">
    <xsl:apply-templates />
  </xsl:element>
</xsl:template>  

<xsl:template match="p">
  <xsl:element name="p">
    <xsl:apply-templates /> 
  </xsl:element>
</xsl:template>

<xsl:template match="q">
  <xsl:element name="blockquote">
    <xsl:apply-templates /> 
  </xsl:element>
</xsl:template>


<!-- convert rend tags to their html equivalents 
     so far, converts: center, italic, smallcaps, bold   -->
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
    <xsl:when test="@rend='bold'">
      <xsl:element name="b">
	<xsl:apply-templates/>
      </xsl:element>
    </xsl:when>
    <xsl:when test="@rend='smallcaps'">
      <xsl:element name="span">
        <xsl:attribute name="class">smallcaps</xsl:attribute>
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="lb">
  <xsl:element name="br" />
</xsl:template>

<xsl:template match="pb">
  <hr class="pb"/>
    <p class="pagebreak">
      Page <xsl:value-of select="@n"/>
</p>
</xsl:template>



<!-- generate next & previous links (if present) -->
<!-- note: all div2s, with id, head, and bibl are retrieved in a <siblings> node -->
<xsl:template name="next-prev">
<xsl:variable name="main_id"><xsl:value-of select="//result/dcidentifier"/></xsl:variable>
<!-- get the position of the current document in the siblings list -->
<xsl:variable name="position">
  <xsl:for-each select="//sibling/result">
    <xsl:if test="@id = $main_id">
      <xsl:value-of select="position()"/>
    </xsl:if>
  </xsl:for-each> 
</xsl:variable>

<xsl:element name="table">
  <xsl:attribute name="width">100%</xsl:attribute>

<!-- display articles relative to position of current article -->

  <xsl:apply-templates select="//sibling/result[$position - 1]">
    <xsl:with-param name="mode">Previous</xsl:with-param>
  </xsl:apply-templates>

  <xsl:apply-templates select="//sibling/result[$position + 1]">
    <xsl:with-param name="mode">Next</xsl:with-param>
  </xsl:apply-templates>

</xsl:element> <!-- table -->
</xsl:template>

<!-- print next/previous link with title & summary information -->
<xsl:template match="sibling/result">
<xsl:param name="mode"/>

<xsl:variable name="linkrel">
    <xsl:choose>
        <xsl:when test="$mode='Previous'">
            <xsl:text>prev</xsl:text>
        </xsl:when>
        <xsl:when test="$mode='Next'">
            <xsl:text>next</xsl:text>
        </xsl:when>
    </xsl:choose>
</xsl:variable>


<xsl:element name="tr">
 <xsl:element name="th">
  <xsl:attribute name="valign">top</xsl:attribute>
   <xsl:attribute name="align">left</xsl:attribute>
   <xsl:value-of select="concat($mode, ': ')"/>
 </xsl:element> <!-- th -->

 <xsl:element name="td">
  <xsl:attribute name="valign">top</xsl:attribute>
  <xsl:element name="a">
   <xsl:attribute name="href">browse.php?id=<xsl:value-of
		select="@id"/></xsl:attribute>
    <!-- use rel attribute to give next / previous information -->
    <xsl:attribute name="rel"><xsl:value-of select="$linkrel"/></xsl:attribute>
    <xsl:call-template name="cleantitle"/>
  </xsl:element> <!-- a -->   
  </xsl:element> <!-- td -->
 
  <xsl:element name="td">
  <xsl:attribute name="valign">top</xsl:attribute>
    <xsl:value-of select="./@type"/>
  </xsl:element> <!-- td -->
  
  <xsl:element name="td">
  <xsl:attribute name="valign">top</xsl:attribute>
  <xsl:element name="font">
   <xsl:attribute name="size">-1</xsl:attribute> 
   <xsl:value-of select="docDate"/>
<!-- for ILN  
<xsl:value-of select="bibl/biblScope[@type='volume']"/>,
  <xsl:value-of select="bibl/biblScope[@type='issue']"/>,
  <xsl:value-of select="bibl/biblScope[@type='pages']"/>.
  (<xsl:value-of select="bibl/extent"/>) -->
  </xsl:element> <!-- font -->

 </xsl:element> <!-- td -->
</xsl:element> <!-- tr -->

</xsl:template>

 <!-- Use n attribute (normalized caps) for article title; if n is blank, 
      label as untitled -->
<xsl:template name="cleantitle">
  <xsl:choose>
    <xsl:when test="@n = ''">
      <xsl:text>[Untitled]</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="normalize-space(./@n)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>



</xsl:stylesheet>