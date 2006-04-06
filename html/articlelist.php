<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("CTI/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["id"];

$args = array('host' => $tamino_server,
	      'db' => $tamino_db["meta-db"],
	      'coll' => $tamino_coll["meta-coll"],
	      'debug' => false);
$tamino = new xmlDbConnection($args);



//query for single issue list of articles
$query = 'declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
<sibling>
{for $b in input()/schangesfw-metadata[ctirecord/ctimetadata/rdfRDF/ctiItemGroup/dcidentifier = "' . "$id" . '"]
for $a in $b/ctirecord/ctimetadata/rdfRDF/ctiItem
return 
<result>
{$a/dctitle}
{$a/dccreator}
{$a/dcidentifier}
{$a/dcdescription}
</result>}
{for $c in input()/schangesfw-metadata/ctirecord/ctimetadata/rdfRDF/ctiItemGroup[dcidentifier = "' . "$id" . '"]
return <issueid> {$c/dcidentifier}</issueid>}
{for $f in input()/schangesfw-metadata
let $d := $f/ctirecord/ctimetadata/rdfRDF/ctiItemGroup
return
<issueidlist>
{$d/dcidentifier}
{$d/dcdescription}
{$d/dcdate}
</issueidlist>
sort by (dcdate)} 
</sibling>';



$rval = $tamino->xquery($query);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 


html_head("Article Browse", true);


include("xml/browse-head.xml");

print '<div class="content">';

print '<h2>Articles</h2>';


$xsl_file = "article-list.xsl";
//$xsl_params = array('mode' => "flat", "vol" => $vol);
$tamino->xslTransform($xsl_file); //, $xsl_params);
//$tamino->xslTransform($xsl_file);
$tamino->printResult();

?> 
   
</div>
   
<?php
  include("xml/footer.xml");
?>


</body>
</html>


