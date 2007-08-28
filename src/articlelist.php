<?php

include_once("config.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_REQUEST["id"];

$exist_args{"debug"} = true;
$xmldb = new xmlDbConnection($exist_args);

//query for single issue list of articles
$query = 'for $issue in /TEI.2//div1[@id = "' . "$id" . '"]
let $hdr := root($issue)/TEI.2/teiHeader
let $curdate := $issue/p/date/@value
let $previd := (for $d in /TEI.2//div1[p/date/@value < $curdate]
    order by $d/p/date/@value return $d)[last()]
let $nextid := (for $c in /TEI.2//div1[p/date/@value > $curdate]
    order by $c/p/date/@value return $c)[1]

return <result>
<header>{$hdr}</header>
<issue-id>{$issue/@id}
{$issue/head}</issue-id>
    <prev>
    {$previd/@id}
     <docdate>{$previd/p/date/@value}</docdate>
    {$previd/head}
</prev>
<next>
{$nextid/@id}
 <docdate>{$nextid/p/date/@value}</docdate>
  {$nextid/head}
</next>
{for $a in $issue/div2
	  order by xs:int($a/@n)
return
<article>
{$a/@id}
{$a/@type}
{$a/head}
{$a/byline/docAuthor/name}
{$a/docDate}
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


