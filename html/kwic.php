<?php
include_once("config.php");
include_once("lib/xmlDbConnection.class.php");

$exist_args{"debug"} = true;

$db = new xmlDbConnection($exist_args);
//$connectionArray{"debug"} = false;

//$xdb = new xmlDbConnection($connectionArray);

global $title;
global $abbrev;
global $collection;


$id = $_GET["id"]; 
$docname = $_GET["docname"];
$keyword = $_GET["keyword"];
print "DEBUG: id=$id, docname=$docname, keyword=$keyword, database=$db";

$htmltitle = "Southern Changes Digital Archive";


// what should be displayed here?  for sc: article title, author, date

// use article query with context added
// note: using |= instead of &= because we want context for any of the
// keyword terms, whether they appear together or not
$xquery = $teixq . "let \$doc := document('/db/schanges/$docname.xml')//div2[@id = \"$id\"]
return 
<TEI.2>
{\$doc/@id}
{\$doc/head}
{\$doc/byline/docAuthor}
{\$doc/docDate}
<kwic>{teixq:kwic-context(\$doc, '$keyword')}</kwic>
</TEI.2>";


/* this is one way to specify context nodes  (filter based on the kinds of nodes to include)
  <context>{(\$a//p|\$a//titlePart|\$a//q|\$a//note)[. &= '$keyword']}</context>
   above is another way-- allow any node, but if the node is a <hi>, return parent instead
   (what other nodes would need to be excluded? title? others?)
*/

$db->xquery($xquery);
//$doctitle = $db->findnode("title");
// truncate document title for html header
//$doctitle = str_replace(", an electronic edition", "", $doctitle);


print "$doctype
<html>
 <head>
    <title>$htmltitle : $doctitle : Keyword in Context</title>
    <link rel='stylesheet' type='text/css' href='schanges.css'>";

include("xml/header_search.xml");

print "<div class='content'>
<div class='title'><a href='index.html'>$title</a></div>";

$xsl_params = array("url_suffix" => "keyword=$keyword");

$db->xslBind("kwic-towords.xsl");
$db->xslBind("kwic.xsl", $xsl_params);

$db->transform();
$db->printResult();


?>
