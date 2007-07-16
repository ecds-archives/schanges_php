<?php

include_once("config.php");
include("common_functions.php");
include_once("lib/xmlDbConnection.class.php");

$id = $_GET["id"];

//$issueid = $_GET["mdid"];

$terms = $_GET["term"];

$exist_args{"debug"} = true;
$xmldb = new xmlDbConnection($exist_args);

html_head("Article", true);


include("xml/browse-head.xml");

print '<div class="content">';
//$tamino->highlightInfo($terms);

print '<h2>Article</h2>';

$xsl_file = "article.xsl";
$xsl_params = array('mode' => "flat", "vol" => $vol);

//eXist query
/*$query='<sibling>
{for $b in /TEI.2//div1/div2[@id="' . "$id" . '"]
return $b}
{let $mdid := $b/..
return 
<issueid>
{$mdid/@id}
{$mdid/head}
</issueid>}
{let $c := /TEI.2//div1[@id="' . "$issueid" . '"]
for $a in $c/div2
return
<result>
{$a/@id}
{$a/@type}
{$a/head}
{$a/byline}
{$a/docDate}
</result>}
</sibling>';*/

$query='for $art in /TEI.2//div2[@id="' . "$id" . '"]
let $prev := $art/preceding-sibling::div2[1]
let $next := $art/following-sibling::div2[1]
let $issue := $art/..
return
<result>
{$art/@id}
{$art/@type}
{$art/head}
{$art/byline}
{$art/docDate}
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
</result>
';

// run the query 
$xmldb->xquery($query);
$xmldb->xslTransform($xsl_file, $xsl_params);
$xmldb->printResult();

?> 
   
</div>
   
<?php
  include("xml/footer.xml");
?>


</body>
</html>
