<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

//$id = $_GET["id"];

$exist_args{"debug"} = false;
$xmldb = new xmlDbConnection($exist_args);

html_head("Browse", true);
print "</head>";
include("web/xml/browse-head.xml");

print '<div class="content">';

print '<h2>Issues</h2>';

// query for all volumes -- eXist
$query = 'declare namespace tei="http://www.tei-c.org/ns/1.0";
for $b in /tei:TEI//tei:div1
order by ($b/tei:p/tei:date/@when)
return
<result>
{$b/@xml:id}
{$b/tei:head}
<docdate>
{$b/tei:p/tei:date/@when}
</docdate>
</result>';

$xsl_file = "xslt/issue-list.xsl";
$xsl_params = array('mode' => "flat");

$maxdisplay = "110"; //show all the issues
$position = "1"; //start here

// run the query 
$xmldb->xquery($query, $position, $maxdisplay);
$xmldb->xslTransform($xsl_file, $xsl_params);
$xmldb->printResult();




include("web/xml/footer.xml");
?> 
   
</div>
</body>
</html>
