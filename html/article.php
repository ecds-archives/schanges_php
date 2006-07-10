<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include("common_functions.php");
include_once("CTI/xmlDbConnection.class.php");

$id = $_GET["id"];

$issueid = $_GET["mdid"];

$terms = $_GET["term"];

$args = array('host' => $tamino_server,
	      'db' => $tamino_db["data-db"],
	      'coll' => $tamino_coll["data-coll"],
	      'debug' => false);
$tamino = new xmlDbConnection($args);

/*$args_meta = array('host' => $tamino_server,
	     'db' => $tamino_db["meta-db"],
	     'coll' => $tamino_coll["meta-coll"],
	     'debug' => true);
$tamino_meta = new xmlDbConnection($args_meta);*/

$query='declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
<sibling>
{for $b in input()/TEI.2/:text/body/div1/div2[@id="' . "$id" . '"]
return $b}
{let $c := input()/TEI.2/:text/body/div1[@id="' . "$issueid" . '"]
return 
<issueid>
{$c/@id}
{$c/head}
</issueid>}
{let $c := input()/TEI.2/:text/body/div1[@id="' . "$issueid" . '"]
for $a in $c/div2
return
<result>
{$a/@id}
{$a/@type}
{$a/head}
{$a/byline}
{$a/docDate}
</result>}
</sibling>';

//rewriting query to get all info from doc and not metadata for simplicity
/*$sibling_query='declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
<sibling>
{for $c in input()/schangesfw-metadata/ctirecord/ctimetadata/rdfRDF/ctiItemGroup[dcidentifier = "' . "$issueid" . '"]
return <issueid> {$c/dcidentifier}</issueid>}
{for $b in input()/schangesfw-metadata[ctirecord/ctimetadata/rdfRDF/ctiItemGroup/dcidentifier = "' . "$issueid" . '"]
for $a in $b/ctirecord/ctimetadata/rdfRDF/ctiItem
return 
<result>
{$a/dctitle}
{$a/dccreator}
{$a/dcidentifier}
{$a/dcdescription}
</result>}
</sibling>';*/

$rval = $tamino->xquery($query);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 

/*$rval2 = $tamino_meta->xquery($sibling_query);
if ($rval2) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
}*/

html_head("Article", true);


include("xml/browse-head.xml");
//include("xml/sidebar.xml");


print '<div class="content">';
$tamino->highlightInfo($terms);

print '<h2>Article</h2>';

$xsl_file = "article.xsl";
$xsl_params = array('mode' => "flat", "vol" => $vol);
$tamino->xslTransform($xsl_file, $xsl_params);
//$tamino->xslTransform($xsl_file);
$tamino->printResult($terms);

?> 
   
</div>
   
<?php
  include("xml/footer.xml");
?>


</body>
</html>
