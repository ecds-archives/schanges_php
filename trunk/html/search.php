<?php
include_once("config.php");

//include_once("lib/xmlDbConnection.class.php");
include_once("CTI/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET['id'];

$args = array('host' => $tamino_server,
		'db' => $tamino_db["data-db"],
	      	'coll' => $tamino_coll["data-coll"],
	        'debug' => 'true');
$tamino = new xmlDbConnection($args);

// search terms
$kw = $_GET["keyword"];
$phrase = $_GET["exact"];
$title = $_GET["title"];
$author = $_GET["author"];
$date = $_GET["date"];
//$place= $_GET["place"];
$mode= $_GET["mode"];
$docid = $_GET["artid"];		// limit keyword search to one article
$kwic = $_GET["kwic"];		// is this a kwic search or not? defaults to not
$position = $_GET["pos"];  // position (i.e, cursor)
$maxdisplay = $_GET["max"];  // maximum  # results to display

// set some defaults
if ($kwic == '') $kwic = "false";
// if no position is specified, start at 1
if ($position == '') $position = 1;
// set a default maxdisplay
if ($maxdisplay == '') $maxdisplay = 10;       // what is a reasonable default?

$kwarray = processterms($kw);
$phrarray[] = trim($phrase);
$ttlarray=processterms($title);
$autharray=processterms($author);
$darray=processterms($date);
//$plarray=processterms($place);

$doctitle = "Search Results";
$doctitle .= ($kwic == "true" ? " - Keyword in Context" : "");
html_head($doctitle);

//print "<body>";

include("xml/header_search.xml");

$declare = 'declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"  declare namespace xs = "http://www.w3.org/2001/XMLSchema" ';
$for = ' for $a in input()/TEI.2/:text/body/div1/div2[';


$conditions = array();

//Working queries. format: $where = "where tf:containsText($a, '$kw')";
//For search use []filter, [tf:containstText(., '$kw')] so that sort by works

/*if ($kw) {
   foreach ($kwarray as $k){
   array_push ($conditions, "tf:containsText(., '$kw') ");
   }
}*/

if ($kw) {
      $all = 'let $allrefs := (';
      $allcount = 'let $allcounts := (';
      for ($i = 0; $i < count($kwarray); $i++) {
	$term = "'$kwarray[$i]'";
	$let .= "let \$ref$i := tf:createTextReference(\$a//p, $term) ";
	//$let .= "let \$count$i := tf:createTextReference(\$a//p//text()[not(parent::figDesc)], $term) ";
	if ($i > 0) { $all .= ", "; $allcount .= ", "; }
	$all .= "\$ref$i"; 
	//$allcount .= "\$count$i"; 
        array_push($conditions, "tf:containsText(., $term)");
      }
      $all .= ") ";
      $let .= $all;
      $allcount .= ") ";
      $let .= $allcount;
}

if ($phrase) {
   foreach ($phrarray as $r){
   array_push ($conditions, "tf:containsText(., '$r') ");
   $let .= "let \$phrref := tf createTextReference(.//p, '$r') ";
    }
}

if ($title) {
        foreach ($ttlarray as $t){
        array_push ($conditions, "tf:containsText(./head, '$t') ");
    }
}
if ($author) {
        foreach ($autharray as $a){
        array_push ($conditions, "tf:containsText(./byline/docAuthor/name, '$a') ");
    }
}
if ($date) {
        foreach ($darray as $d){    
            array_push ($conditions, "tf:containsText(./docDate, '$d') ");
    }
}
/*if ($place) {
    foreach ($plarray as $p){
    array_push ($conditions, "tf:containsText(\$b/pubPlace, '$p') ");
    }
} */
foreach ($conditions as $c) {
    if ($c == $conditions[0]) {
        $where="$c";
    } 
    else {
        $where.= " and $c";
	}
}


//have to take each individual keyword into an array.
$myterms = array();
if ($kw) {$myterms = array_merge($myterms, $kwarray); }
if ($title) {$myterms = array_merge($myterms, $ttlarray); }
if ($phrase) {$myterms = array_merge($myterms, $phrarray);}
if ($author) {$myterms = array_merge($myterms, $autharray); }
if ($date) {$myterms = array_merge($myterms, $darray); }
//if ($place) {$myterms = array_merge($myterms, $plarray); }


$countquery = "$declare <total>{count($for $where] return \$a)}</total>";

$return = ' return <div2> {$a/head}{$a/byline}{$a/@id}{$a/@type}{$a/docDate}  </div2>';
$sort = 'sort by (docDate/@value)';

$query = "$declare $for$where] $sort $return";
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

include("xml/footer.xml");

?>

</body>
</html>
