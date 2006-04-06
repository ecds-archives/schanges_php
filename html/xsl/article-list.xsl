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
<xsl:variable name="query"><xsl:value-of
select="ino:response/xq:query"/></xsl:variable>

<!-- <xsl:variable name="total_count" select="count(//div1 |
//div2[figure])" /> -->

<xsl:output method="html"/>
<xsl:template match="/">

<!-- begin content -->
<xsl:element name="div">
<xsl:attribute name="class">contents</xsl:attribute>
  <xsl:apply-templates select="//result"/>
</xsl:element>
<xsl:call-template name="next-prev"/>
</xsl:template>

<xsl:template match="result">
  <xsl:element name="ul">
    <xsl:attribute name="class">contents</xsl:attribute>
  <xsl:element name="li">
    <xsl:value-of select="dccreator"/>, <xsl:element name="a">
      <xsl:attribute name="href">article.php?id=<xsl:value-of
      select="substring-after(dcidentifier,'cti-schangesfw-')"/>&amp;mdid=<xsl:value-of
      select="substring-after(//sibling/issueid/dcidentifier,'cti-schangesfw-') "/></xsl:attribute> <xsl:value-of
      select="dctitle"/>, </xsl:element> <!-- a -->
<xsl:value-of select="dcdescription"/>
    
  </xsl:element> <!-- li -->
  </xsl:element><!-- ul -->

</xsl:template>

<!-- generate next & previous links (if present) -->
<!-- note: all div2s, with id, head, and bibl are retrieved in a
     <siblings> node -->
 <!-- Use head not n attribute (normalized caps) for article title; if
      n is blank, label as untitled -->
<xsl:template name="cleantitle">
  <xsl:choose>
    <xsl:when test="head = ''">
      <xsl:text>[Untitled]</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="normalize-space(./head)"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="next-prev">
<xsl:variable name="main_id"><xsl:value-of
select="//issueid/dcidentifier"/></xsl:variable>
<!-- <xsl:message>DEBUG: main id is <xsl:value-of
select="//issueid/dcidentifier"/></xsl:message> -->
<!-- get the position of the current document in the siblings list -->
<xsl:variable name="position">
  <xsl:for-each
      select="//sibling/issueidlist">
	      <xsl:if test="dcidentifier=$main_id">
      <xsl:value-of select="position()"/>
	      </xsl:if>
  </xsl:for-each> 
</xsl:variable>
<!-- <xsl:message>Position = <xsl:value-of
select="$position"/></xsl:message> -->
<xsl:element name="table">
  <xsl:attribute name="width">100%</xsl:attribute>

<!-- display articles relative to position of current article -->

  <xsl:apply-templates select="//sibling/issueidlist[$position - 1]">
    <xsl:with-param name="mode">Previous</xsl:with-param>
  </xsl:apply-templates>

  <xsl:apply-templates select="//sibling/issueidlist[$position + 1]">
    <xsl:with-param name="mode">Next</xsl:with-param>
  </xsl:apply-templates>

</xsl:element> <!-- table -->
</xsl:template>

<!-- print next/previous link with title & summary information -->
<xsl:template match="sibling/issueidlist">
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
   <xsl:attribute name="href">articlelist.php?id=<xsl:value-of select="dcidentifier"/></xsl:attribute>
    <!-- use rel attribute to give next / previous information -->
    <xsl:attribute name="rel"><xsl:value-of select="$linkrel"/></xsl:attribute>
      <xsl:value-of select="dcdescription"/><!-- <xsl:call-template name="cleantitle"/> -->
  </xsl:element> <!-- a -->   
  </xsl:element> <!-- td -->
<!-- 
  <xsl:element name="td">
  <xsl:attribute name="valign">top</xsl:attribute>
  </xsl:element> 
--><!-- td -->
<!--  
  <xsl:element name="td">
  <xsl:attribute name="valign">top</xsl:attribute>
  <xsl:element name="font">
   <xsl:attribute name="size">-1</xsl:attribute> 
-->
<!-- for ILN  
<xsl:value-of select="bibl/biblScope[@type='volume']"/>,
  <xsl:value-of select="bibl/biblScope[@type='issue']"/>,
  <xsl:value-of select="bibl/biblScope[@type='pages']"/>.
  (<xsl:value-of select="bibl/extent"/>) -->
  </xsl:element> <!-- font -->
<!--
 </xsl:element> --> <!-- td -->
<!-- </xsl:element> --> <!-- tr -->
</xsl:template>


</xsl:stylesheet>
