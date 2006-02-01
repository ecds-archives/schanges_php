<?php
include("config.php");

//html_head("Search Results");

include_once("lib/xmlDbConnection.class.php");

print "<body>";

include("header.html");

$id = $_GET['id'];

$args = array('host' => "vip.library.emory.edu",
		'db' => "SRC_TEST",
	      	'coll' => 'schanges',
	        'debug' => 'true');
$tamino = new xmlDbConnection($args);

// search terms
$kw = $_GET["keyword"];
$title = $_GET["title"];
$author = $_GET["author"];
$date = $_GET["date"];
//$place= $_GET["place"];
$mode= $_GET["mode"];


$kwarray = processterms($kw);
$ttlarray=processterms($title);
$autharray=processterms($author);
$darray=processterms($date);
$plarray=processterms($place);


$declare = 'declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction" ';
$for = ' for $a in input()/TEI.2/:text/body/div1/div2';


$conditions = array();

//Working queries. format: $where = "where tf:containsText(\$a, '$kw')";
if ($kw) {
    if ($mode == "exact") {
        array_push($conditions, "tf:containsText(\$a, '$kw')");
    }
    if ($mode == "synonym") {
        array_push($conditions, "tf:containsText(\$a, tf:synonym('$kw'))");
    }
    else {
        foreach ($kwarray as $k){
            $term = ($mode == "phonetic") ? "tf:phonetic('$k')" : "'$k'";
            array_push($conditions, "tf:containsText(\$a, $term)");
        }
    }
}
if ($title) {
        foreach ($ttlarray as $t){
        array_push($conditions, "tf:containsText(\$a/head, '$t') ");
    }
}
if ($author) {
        foreach ($autharray as $a){
        array_push($conditions, "tf:containsText(\$a/byline/docAuthor/name, '$a') ");
    }
}
if ($date) {
        foreach ($darray as $d){    
            array_push ($conditions, "tf:containsText(\$a/docDate, '$d') ");
    }
}
/*if ($place) {
    foreach ($plarray as $p){
    array_push ($conditions, "tf:containsText(\$b/pubPlace, '$p') ");
    }
} */
foreach ($conditions as $c) {
    if ($c == $conditions[0]) {
        $where= "where $c";
    } else {
        $where.= " and $c";
            }
}

//have to take each individual keyword into an array.
$myterms = array();
if ($kw) {$myterms = array_merge($myterms, $kwarray); }
if ($title) {$myterms = array_merge($myterms, $ttlarray); }
if ($author) {$myterms = array_merge($myterms, $autharray); }
if ($date) {$myterms = array_merge($myterms, $darray); }
//if ($place) {$myterms = array_merge($myterms, $plarray); }


$countquery = "$declare <total>{count($for $let $where return \$a)}</total>";

$return = ' return <div2> {$a/head}{$a/byline}{$a/@id}  </div2>';
$sort = 'sort by (docAuthor/name/@reg)';

$query = "$declare $for $where $return $sort";
$tamino->xquery($countquery);
$total = $tamino->findNode("total");
$tamino->xquery($query);
$tamino->getXqueryCursor();

$xsl_file = "search.xsl";


// pass search terms into xslt as parameters 
// (xslt passes on terms to browse page for highlighting)
$term_list = implode("|", $myterms);
$xsl_params = array("term_list"  => $term_list);



print '<div class="content">';
if ($total==0){
print "<p><b>No matches found.</b> You may want to broaden your search and see search tips for suggestions.</p>";
include ("searchoptions.php");
    
    
}
else
{
print "<p class='center'>Number of matching articles: <b>$total</b></p>";
$tamino->xslTransform($xsl_file, $xsl_params);
$tamino->highlightInfo($myterms);
$tamino->printResult($myterms);
}
print '</div>';

//Function that takes multiple terms separated by white spaces and puts them into an array
function processterms ($str) {
// clean up input so explode will work properly
    $str = preg_replace("/\s+/", " ", $str);  // multiple white spaces become one space
    $str = preg_replace("/\s$/", "", $str);	// ending white space is removed
    $str = preg_replace("/^\s/", "", $str);  //beginning space is removed
    $terms = explode(" ", $str);    // multiple search terms, divided by spaces
    return $terms;
}


?>

</body>
</html>
