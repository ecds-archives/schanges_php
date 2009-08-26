<?php
include_once("config.php");
include_once("lib/xmlDbConnection.class.php");

$exist_args{"debug"} = false;

$db = new xmlDbConnection($exist_args);

global $title;
global $abbrev;
global $collection;


$id = $_REQUEST["id"]; 
$keyword = $_REQUEST["keyword"];


$htmltitle = "Southern Changes Digital Archive";


// what should be displayed here?  for sc: article title, author, date

// use article query with context added
// note: using |= instead of &= because we want context for any of the
// keyword terms, whether they appear together or not
$xquery = "let \$doc := /TEI.2//div2[@id = \"$id\"]
return 
<item>
{\$doc/@id}
{\$doc/head}
{\$doc/byline/docAuthor}
{\$doc/docDate}
<context>
{for \$c in \$doc//*[. |= \"$keyword\"]
   return if (name(\$c) = 'hi') then \$c/..[. |= \"$keyword\"] else  \$c }</context>
</item>";


/* this is one way to specify context nodes  (filter based on the kinds of nodes to include)
  <context>{(\$a//p|\$a//titlePart|\$a//q|\$a//note)[. &= '$keyword']}</context>
   above is another way-- allow any node, but if the node is a <hi>, return parent instead
   (what other nodes would need to be excluded? title? others?)
*/

$db->xquery($xquery);


print "$doctype
<html>
 <head>
    <title>$htmltitle : $doctitle : Keyword in Context</title>
    <link rel='stylesheet' type='text/css' href='web/css/schanges.css'>";

include("web/xml/header_search.xml");

print "<div class='content'>
<div class='title'><a href='index.html'>$title</a></div>";

$xsl_params = array("url_suffix" => "keyword=$keyword");

$db->xslBind("xslt/kwic-towords.xsl");
$db->xslBind("xslt/kwic.xsl", $xsl_params);

$db->transform();
$db->printResult();


?>
