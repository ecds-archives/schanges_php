<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["id"];
$docdate = $_GET["docdate"];

$exist_args{"debug"} = false;
$xmldb = new xmlDbConnection($exist_args);

html_head("Article Browse", true);


include("xml/browse-head.xml");

print '<div class="content">';

print '<h2>Articles</h2>';

//query for single issue list of articles
$query = '<result>
{for $b in /TEI.2//div1[@id = "' . "$id" . '"]
return 
<issue-id>{$b/@id}
{$b/head}</issue-id>
}
{let $curdate := ("' . "$docdate" . '")
let $previd := (for $d in /TEI.2//div1[p/date/@value < $curdate]
    order by $d/p/date/@value
return $d)[last()]
return 
    <prev>
    {$previd/@id}
     <docdate>{$previd/p/date/@value}</docdate>
    {$previd/head}
</prev>
}
x{let $curdate := ("' . "$docdate" . '")
let $nextid := (for $c in /TEI.2//div1[p/date/@value > $curdate]
    order by $c/p/date/@value
return $c)[1]
return
<next>
{$nextid/@id}
 <docdate>{$nextid/p/date/@value}</docdate>
  {$nextid/head}
</next>}
{for $a in /TEI.2//div1[@id = "' . "$id" . '"]/div2
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
</result>';

$xsl_file = "article-list.xsl";

// run the query 
$xmldb->xquery($query);
$xmldb->xslTransform($xsl_file);
$xmldb->printResult();


?> 
   
</div>
   
<?php
  include("xml/footer.xml");
?>


</body>
</html>


