<?php

include_once("config.php");
include_once("xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["id"];


$args = array('host' => $tamino_server,
	      'db' => $tamino_db["data-db"],
	      'coll' => $tamino_coll["data-coll"],
	      'debug' => false);
$tamino = new xmlDbConnection($args);

$query='declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
for $b in input()/TEI.2/:text/body/div1/div2[@id="' . "$id" . '"]
return $b';

$rval = $tamino->xquery($query);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 


//html_head("Issue List", true);


include("xml/browse-head.xml");
//include("xml/sidebar.xml");


print '<div class="content">';

print '<h2>Article</h2>';


$xsl_file = "article.xsl";
$xsl_params = array('mode' => "flat", "vol" => $vol);
$tamino->xslTransform($xsl_file, $xsl_params);
//$tamino->xslTransform($xsl_file);
$tamino->printResult();

?> 
   
</div>
   
<?php
  include("xml/footer.xml");
?>


</body>
</html>
