<?php
include_once("config.php");
include_once("lib/xmlDbConnection.class.php");

$exist_args{"debug"} = true;

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
$xquery = "declare namespace tei='http://www.tei-c.org/ns/1.0';
declare option exist:serialize 'highlight-matches=all';
let \$doc := /tei:TEI//tei:div2[@xml:id = \"$id\"]
return 
<item>
{\$doc/@xml:id}
{\$doc/tei:head}
{\$doc/tei:byline/tei:docAuthor}
{\$doc/tei:docDate}
<context>
{for \$c in \$doc//*[ft:query(., \"$keyword\")]
return if (name(\$c) = 'tei:hi') then \$c/..[ft:query(.,  \"$keyword\")] else  \$c }</context>
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

<?php
  include("web/xml/footer.xml");
?>
</body></html>
