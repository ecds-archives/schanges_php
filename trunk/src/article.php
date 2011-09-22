<?php

include_once("config.php");
include("common_functions.php");
include_once("lib/xmlDbConnection.class.php");

$id = $_REQUEST["id"];

$terms = $_REQUEST["keyword"];

$exist_args{"debug"} = true;
$xmldb = new xmlDbConnection($exist_args);

/*The query here should match wrappers and structure, sort of with the query in oai/xquery.xml*/

$for='for $art in /tei:TEI//tei:div2[@xml:id="' . "$id" . '"]';
if ($terms != '') {$for .= "[ft:query(.,  \"$terms\")]";}
$let='let $hdr := root($art)/tei:TEI/tei:teiHeader
let $issue := $art/..
let $currn := $art/@n
let $prev := (for $n in /tei:TEI//tei:div1[tei:div2/@xml:id="' . "$id" . '"]/tei:div2[@n < $currn]
order by $n/@n return $n)[last()]
let $next := (for $n in /tei:TEI//tei:div1[tei:div2/@xml:id="' . "$id" . '"]/tei:div2[@n > $currn]
order by $n/@n return $n)[1]';
$return='return
<TEI>
{$hdr}
{$art}
<issueid>
{$issue/@xml:id}
{$issue/tei:head}
</issueid>
<prev>
{$prev/@xml:id}
{$prev/@type}
{$prev/@n}
{$prev/tei:head}
{$prev/tei:docDate}
</prev>
<next>
{$next/@xml:id}
{$next/@type}
{$next/@n}
{$next/tei:head}
{$next/tei:docDate}
</next>
</TEI>';

$query = "declare namespace tei='http://www.tei-c.org/ns/1.0';
declare option exist:serialize 'highlight-matches=all';";
$query .= "$for $let $return";

// run the query 
$xmldb->xquery($query);

$header_xsl1 = "xslt/teiheader-dc2.xsl";
$header_xsl2 = "xslt/dc-htmldc.xsl";

//$xsl_params = array('mode' => "article");
$xmldb->xslTransform($header_xsl1);
$xmldb->xslTransformResult($header_xsl2);

html_head("Article", true);
$xmldb->printResult();
print "</head>";

include("web/xml/browse-head.xml");

print '<div class="content">';

print '<h2>Article</h2>';

$xsl_file = "xslt/article.xsl";
//$xsl_params = array('mode' => "flat");

$xmldb->xslTransform($xsl_file);
$xmldb->printResult();

?> 
   
</div>
   
<?php
  include("web/xml/footer.xml");
?>


</body>
</html>
