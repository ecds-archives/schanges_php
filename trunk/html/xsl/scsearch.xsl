<?xml version="1.0" encoding="ISO-8859-1"?> 

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/TR/REC-html40" version="1.0"
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xq="http://metalab.unc.edu/xq/">
<!-- adapting lincolnweb search.xsl to schanges AH 3-17-2006 -->

<!-- search terms for highlighting.  Should be in format:
     term1|term2|term3|term4  -->
<xsl:param name="term_list"/>
<xsl:param name="selflink"/>	<!-- link to self, for single document kwic link -->
<xsl:param name="mode"/>		<!-- kwic -->

<!-- generate an addendum to the url, in the form of:
     &term[]=string1&term[]=string2 etc. 
     This string should be appended to the browse (sermon.php)  url.  -->
<xsl:variable name="term_string">
  <xsl:call-template name="highlight-params">
    <xsl:with-param name="str"><xsl:value-of
    select="$term_list"/></xsl:with-param>
   </xsl:call-template>
 <!-- This is working (AH 4-12-06)
<xsl:if test="$term_list"><xsl:message>Term list is <xsl:value-of
select="$term_list"/></xsl:message></xsl:if> -->
</xsl:variable>

<xsl:output method="xml" omit-xml-declaration="yes"/>  

<xsl:template match="/"> 
    <xsl:choose>
      <xsl:when test="$mode = 'kwic'">
        <!-- don't put in a table, use # of matches -->
        <xsl:apply-templates select="//div2" mode="kwic"/>
      </xsl:when>
      <xsl:when test="//div2/matches">
        <xsl:element name="table">
          <xsl:attribute name="class">searchresults</xsl:attribute>
	  <xsl:element name="tr">
	    <xsl:element name="th">
              <xsl:attribute name="class">tip</xsl:attribute>
		To view keyword in context results for a single
		document, click on "view context" link.
            </xsl:element>
	    <xsl:element name="th">number of matches:</xsl:element>
	  </xsl:element>
          <xsl:apply-templates select="//div2" mode="count"/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="//div2" />
      </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- put article title in a table in order to align matches count off to the side -->
<xsl:template match="div2" mode="count">
  <xsl:element name="tr">
    <xsl:element name="td">
      <xsl:apply-templates select="."/>
    </xsl:element>
    <xsl:element name="td">
      <xsl:attribute name="class">count</xsl:attribute>
	<!-- number of matches for a search -->
        <xsl:apply-templates select="matches" mode="count"/>
    </xsl:element>
      <xsl:element name="td">
        <xsl:attribute name="class">link</xsl:attribute>
  	  <xsl:element name="a">  
    	    <xsl:attribute name="href"><xsl:value-of
	    select="$selflink"/>&amp;id=<xsl:value-of
	    select="@id"/>&amp;kwic=true&amp;mdid=<xsl:value-of select="issueid/@id"/></xsl:attribute> 
    	view context 
  	</xsl:element>  <!-- a -->
      </xsl:element> <!-- td -->
  </xsl:element>
</xsl:template>


<!-- kwic results -->
<xsl:template match="div2" mode="kwic">
<!-- enclose article title & number of  matches in a table  -->
  <xsl:element name="table">
   <xsl:attribute name="class">kwicsearchresults</xsl:attribute> 
    <xsl:element name="tr">
      <xsl:element name="td">
        <xsl:element name="a">
	  <xsl:attribute name="href">article.php?&amp;id=<xsl:value-of
	  select="@id"/>&amp;kwic=true&amp;mdid=<xsl:value-of select="issueid/@id"/><xsl:value-of select="$term_string"/></xsl:attribute><xsl:apply-templates
	  select="head"/>
	</xsl:element><!-- a --><br/>
	<xsl:apply-templates select="byline"/>, <xsl:apply-templates select="docDate"/>
      </xsl:element>
      <xsl:element name="td">
        <xsl:attribute name="class">count</xsl:attribute>
	<!-- number of matches for a search -->
        <xsl:apply-templates select="matches" mode="kwic"/>
      </xsl:element> <!-- td -->
    </xsl:element> <!-- tr -->
  </xsl:element> <!-- table -->
  <!-- now display context -->
  <xsl:apply-templates select="context"/>
</xsl:template>

<!-- # of matches within an article -->
<xsl:template match="matches"> 
  <!-- do nothing in normal mode, for now -->  
</xsl:template>

<!-- # of matches within an article -->
<xsl:template match="matches" mode="count"> 
  <xsl:element name="a">  
    <xsl:attribute name="href"><xsl:value-of select="$selflink"/>&amp;id=<xsl:value-of select="../@id"/>&amp;kwic=true</xsl:attribute> 
    <xsl:value-of select="total"/>
  </xsl:element><br/>
  
  <span class="term-totals">
    <xsl:apply-templates select="term"/>
  </span>
</xsl:template>

<xsl:template match="term">
  <xsl:value-of select="text()"/> : <xsl:apply-templates
  select="count"/><xsl:text>  </xsl:text>
</xsl:template>

<!-- # of matches within an article -->
<xsl:template match="matches" mode="kwic"> 
  <xsl:element name="b">	<!-- bold -->
    <xsl:value-of select="total"/> match<xsl:if test="total &gt; 1">es</xsl:if><xsl:text>  </xsl:text>
  </xsl:element>
  <span class="term-totals">
    <xsl:apply-templates select="term"/>
  </span>
</xsl:template>


<!-- keyword in context -->
<xsl:template match="context/p">
  <p class="kwic"> 
   <xsl:apply-templates/>
  </p>
</xsl:template>
<!-- ignore figure, figure descriptions -->
<xsl:template match="context//figure"/>


<xsl:template match="div2">
 <p>
  <a><xsl:attribute name="href">article.php?id=<xsl:value-of
  select="@id"/>&amp;mdid=<xsl:value-of select="issueid/@id"/><xsl:value-of select="$term_string"/></xsl:attribute>
  <xsl:value-of select="head"/></a><br/>
  <xsl:apply-templates select="byline/docAuthor/name"/>, <xsl:value-of select="docDate"/>.
 </p>
</xsl:template>

<xsl:template match="byline/docAuthor/name">
    <xsl:choose>
      <xsl:when test="position() = 1"/>
      <xsl:when test="position() = last()">
        <xsl:text> and </xsl:text>
      </xsl:when>
      <xsl:otherwise>
	<xsl:text> </xsl:text>
      </xsl:otherwise>
  </xsl:choose>
    <xsl:apply-templates />
</xsl:template>



<xsl:template match="total"/>

<xsl:template match="match">
  <span class="match"><xsl:apply-templates/></span>
</xsl:template>

<xsl:template match="hi">
  <xsl:choose>
    <xsl:when test="@rend = 'italic'">
      <i><xsl:apply-templates/></i>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="title">
  <i><xsl:apply-templates/></i>
</xsl:template>   


<xsl:template name="highlight-params">
  <xsl:param name="str"/>
  <xsl:choose>
    <xsl:when test="contains($str, '|')">
       <xsl:text>&amp;term[]=</xsl:text><xsl:value-of select="substring-before($str, '|')"/>
       <xsl:call-template name="highlight-params">
         <xsl:with-param name="str"><xsl:value-of select="substring-after($str, '|')"/></xsl:with-param>
       </xsl:call-template>
    </xsl:when>
    <xsl:when test="string-length($str) = 0">
  	<!-- empty string or not set; do nothing -->
    </xsl:when>
    <xsl:otherwise>
       <xsl:text>&amp;term[]=</xsl:text><xsl:value-of select="$str"/>
    </xsl:otherwise>
  </xsl:choose>

</xsl:template>

</xsl:stylesheet>
