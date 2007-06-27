<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["id"];

$exist_args{"debug"} = true;
$xmldb = new xmlDbConnection($exist_args);

html_head("Browse", true);

include("xml/browse-head.xml");

print '<div class="content">';

print '<h2>Issues</h2>';

// query for all volumes -- eXist
$allquery = 'for $b in /TEI.2//div1
order by ($b/p/date/@value)
return
<result>
{$b/@id}
{$b/head}
</result>';

$xsl_file = "xsl/issue-list.xsl";
$xsl_params = array('mode' => "flat", "vol" => $vol);

// run the query 
$xmldb->xquery($allquery);
$xmldb->xslTransform($xsl_file, $xsl_params);
$xmldb->printResult();


/*$rval = $tamino->xquery($allquery);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} */



include("xml/footer.xml");
?> 
   
</div>
   
<?php
  //include("xml/footer.xml");
?>


</body>
</html>
