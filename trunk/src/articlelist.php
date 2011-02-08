<?php

include_once("config.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_REQUEST["id"];

$exist_args{"debug"} = false;
$xmldb = new xmlDbConnection($exist_args);

//query for single issue list of articles
$query = 'declare namespace tei="http://www.tei-c.org/ns/1.0";
for $issue in /tei:TEI//tei:div1[@xml:id = "' . "$id" . '"]
let $hdr := root($issue)/tei:TEI/tei:teiHeader
let $curdate := $issue/tei:p/tei:date/@when
let $previd := (for $d in /tei:TEI//tei:div1[tei:p/tei:date/@when < $curdate]
    order by $d/tei:p/tei:date/@when return $d)[last()]
let $nextid := (for $c in /tei:TEI//tei:div1[tei:p/tei:date/@when > $curdate]
    order by $c/tei:p/tei:date/@when return $c)[1]

return <result>
<header>{$hdr}</header>
<issue-id>{$issue/@xml:id}
{$issue/tei:head}</issue-id>
    <prev>
    {$previd/@xml:id}
     <docdate>{$previd/tei:p/tei:date/@when}</docdate>
    {$previd/tei:head}
</prev>
<next>
{$nextid/@xml:id}
 <docdate>{$nextid/tei:p/tei:date/@when}</docdate>
  {$nextid/tei:head}
</next>
{for $a in $issue/tei:div2
	  order by xs:int($a/@n)
return
<article>
{$a/@xml:id}
{$a/@type}
{$a/tei:head}
{$a/tei:byline/tei:docAuthor/tei:name}
{$a/tei:docDate}
</article>
}

</result>
';

$xsl_file = "xslt/article-list.xsl";

// run the query 
$xmldb->xquery($query);
// metadata information for cataloging
$header_xsl1 = "xslt/teiheader-dc.xsl";
$header_xsl2 = "xslt/dc-htmldc.xsl";
//$header_params = array('mode' => "list");
$xmldb->xslTransform($header_xsl1);
$xmldb->xslTransformResult($header_xsl2);

html_head("Article Browse", true);
$xmldb->printResult();
print "</head>";
include("web/xml/browse-head.xml");

print '<div class="content">';

print '<h2>Articles</h2>';



$xmldb->xslTransform($xsl_file);
$xmldb->printResult();


?> 
   
</div>
   
<?php
  include("web/xml/footer.xml");
?>


</body>
</html>


