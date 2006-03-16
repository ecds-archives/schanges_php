<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("CTI/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["dcidentifier"];

$args = array('host' => $tamino_server,
	      'db' => $tamino_db["meta-db"],
	      'coll' => $tamino_coll["meta-coll"],
	      'debug' => false);
$tamino = new xmlDbConnection($args);

// query for all volumes 
/*$allquery = 'for $b in input()/TEI.2/:text/body/div1
sort by (@id)
return <div1 id="{$b/@id}" type="{$b/@type}">
 {$b/head}
 {$b/docDate}
 <count type="article">{count($b/div2)}</count>
 <count type="figure">{count($b//figure)}</count>
</div1>'; */

$allquery = 'for $a in input()/schangesfw-metadata/ctirecord/ctimetadata/rdfRDF/ctiItemGroup
sort by (./dcdate)
return 
<result>
{$a/dcidentifier}
{$a/dcdescription}
</result>';


//query for single volume by id
/* $idquery = 'for $b in input()/TEI.2/:text/body/div1
where $b/@id = "' . $id  . '"
return <div1 id="{$b/@id}" type="{$b/@type}">
 {$b/head}
 {$b/docDate}
 { for $c in $b/div2 return
   <div2 id="{$c/@id}" type="{$c/@type}" n="{$c/@n}">
     {$c/head}
     {$c/bibl}
     {for $d in $c/p/figure return $d}
   </div2>}
</div1>';
*/



//$query = isset($id) ? $idquery : $allquery;
//$vol = isset($id) ? "single" : "all";



$rval = $tamino->xquery($allquery);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 

html_head("Browse", true);

include("xml/browse-head.xml");



print '<div class="content">';

print '<h2>Issues</h2>';


$xsl_file = "issue-list.xsl";
$xsl_params = array('mode' => "flat", "vol" => $vol);
$tamino->xslTransform($xsl_file, $xsl_params);
//$tamino->xslTransform($xsl_file);
$tamino->printResult();

include("xml/footer.xml");
?> 
   
</div>
   
<?php
  //include("xml/footer.xml");
?>


</body>
</html>
