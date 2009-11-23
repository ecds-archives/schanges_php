<?php

include_once("config.php");
include("common_functions.php");
include_once("lib/xmlDbConnection.class.php");

$id = $_REQUEST["id"];

$terms = $_REQUEST["keyword"];

$exist_args{"debug"} = false;
$xmldb = new xmlDbConnection($exist_args);

/*The query here should match wrappers and structure, sort of with the query in oai/xquery.xml*/

$for='for $art in /TEI.2//div2[@id="' . "$id" . '"]';
if ($terms != '') {$for .= "[. |= \"$terms\"]";}
$let='let $hdr := root($art)/TEI.2/teiHeader
let $prev := $art/preceding-sibling::div2[1]
let $next := $art/following-sibling::div2[1]
  let $issue := $art/..';
$return='return
<TEI>
{$hdr}
{$art}
<issueid>
{$issue/@id}
{$issue/head}
</issueid>
<prev>
{$prev/@id}
{$prev/@type}
{$prev/head}
{$prev/docDate}
</prev>
<next>
{$next/@id}
{$next/@type}
{$next/head}
{$next/docDate}
</next>
</TEI>';

$query = "declare option exist:serialize 'highlight-matches=all';";
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
