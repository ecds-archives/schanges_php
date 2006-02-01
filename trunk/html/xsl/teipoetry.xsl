<?xml version="1.0" encoding="ISO-8859-1"?>  

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
	xmlns:html="http://www.w3.org/TR/REC-html40" 
	xmlns:ino="http://namespaces.softwareag.com/tamino/response2" 
	xmlns:xql="http://metalab.unc.edu/xql/">

<!-- templates to transform & display TEI poetry -->


<!-- default indentation if number of spaces is not specified -->
<xsl:param name="defaultindent">5</xsl:param>	  

<xsl:template match="p">
  <p><xsl:apply-templates/></p>
</xsl:template>

<xsl:template match="argument">
 <xsl:element name="p">
  <xsl:attribute name="class">argument</xsl:attribute>
   <xsl:apply-templates />
 </xsl:element>  <!-- p -->
</xsl:template>

<xsl:template match="epigraph">
 <xsl:element name="p">
  <xsl:attribute name="class">epigraph</xsl:attribute>
     <xsl:apply-templates/>
 </xsl:element>  <!-- p -->
</xsl:template>


<xsl:template match="head">
  <xsl:element name="p">
   <xsl:attribute name="align"><xsl:value-of select="@rend"/></xsl:attribute>
    <xsl:attribute name="class">head</xsl:attribute>
    <xsl:apply-templates/>
  </xsl:element>  <!-- p -->
</xsl:template>

<!-- subheading -->
<xsl:template match="lg/head">
  <xsl:element name="p">
    <xsl:attribute name="class">subhead</xsl:attribute>
	  <xsl:apply-templates />
  </xsl:element>  <!-- p -->
</xsl:template>

<xsl:template match="trailer">
  <xsl:element name="p">
    <xsl:attribute name="class">trailer</xsl:attribute>
      <xsl:apply-templates />
  </xsl:element>  <!-- p -->
</xsl:template>

<xsl:template match="byline">
  <xsl:element name="p">
    <xsl:attribute name="class">byline</xsl:attribute>
    <xsl:apply-templates />
  </xsl:element>  <!-- p -->
</xsl:template>

<!-- dedication -->
<xsl:template match="dedicat">
  <xsl:element name="p">
    <xsl:attribute name="class">dedication</xsl:attribute>
    <xsl:apply-templates/>
  </xsl:element> <!-- p -->
</xsl:template>

<xsl:template match="lb">
<br/>
</xsl:template>

<!-- line group -->
<xsl:template match="lg">
  <xsl:element name="p">
     <xsl:attribute name="class"><xsl:value-of select="@type"/></xsl:attribute>
    <xsl:apply-templates />
  </xsl:element>
</xsl:template>

<!-- line  -->
<!--   Indentation should be specified in format rend="indent#", where # is
       number of spaces to indent.  --> 
<xsl:template match="l">
  <!-- retrieve any specified indentation -->
  <xsl:if test="@rend">
  <xsl:variable name="rend">
    <xsl:value-of select="./@rend"/>
  </xsl:variable>
  <xsl:variable name="indent">
     <xsl:choose>
       <xsl:when test="$rend='indent'">		
	<!-- if no number is specified, use a default setting -->
         <xsl:value-of select="$defaultindent"/>
       </xsl:when>
       <xsl:otherwise>
         <xsl:value-of select="substring-after($rend, 'indent')"/>
       </xsl:otherwise>
     </xsl:choose>
  </xsl:variable>
   <xsl:call-template name="indent">
     <xsl:with-param name="num" select="$indent"/>
   </xsl:call-template>
 </xsl:if>

  <xsl:apply-templates/>
  <xsl:element name="br"/>
</xsl:template>

<xsl:template match="q">
  <p>
   <xsl:choose>
     <xsl:when test="@rend='indent'">
      <blockquote><xsl:apply-templates/></blockquote>
     </xsl:when>
     <xsl:otherwise>
	<xsl:apply-templates/>
     </xsl:otherwise>
   </xsl:choose>
  </p>
</xsl:template>



<!-- convert rend tags to their html equivalents 
     so far, converts: center, italic, bold, smallcaps   -->
<!-- Note: this template has a lower priority so as not to override
     specific templates (e.g., head, l) that happen to also have rend
     attributes. -->
<xsl:template match="//*[@rend]" priority="-0.25">
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
    <xsl:when test="@rend='smallcaps' or @rend='smallcap'">
      <xsl:element name="span">
        <xsl:attribute name="class">smallcaps</xsl:attribute>
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<!-- recursive template to indent by inserting non-breaking spaces -->
<xsl:template name="indent">
  <xsl:param name="num">0</xsl:param>
  <xsl:variable name="space">&#160;</xsl:variable>

  <xsl:value-of select="$space"/>

  <xsl:if test="$num > 1">
    <xsl:call-template name="indent">
       <xsl:with-param name="num" select="$num - 1"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>


</xsl:stylesheet>
