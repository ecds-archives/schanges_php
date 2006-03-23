<?php
include_once("config.php");

include_once("lib/xmlDbConnection.class.php");
//include_once("CTI/xmlDbConnection.class.php");
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
$docid = $_GET["artid"];     // limit keyword search to one article
$kwic = $_GET["kwic"];	     // is this a kwic search or not? defaults to not
$position = $_GET["pos"];  // position (i.e, cursor)
$maxdisplay = $_GET["max"];  // maximum  # results to display

// set some defaults
if ($kwic == '') $kwic = "false";
// if no position is specified, start at 1
if ($position == '') $position = 1;
// set a default maxdisplay
if ($maxdisplay == '') $maxdisplay = 20;       // what is a reasonable default?

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
if ($docid != '') { $for .= "@id = '$docid' and "; }

// create an array of conditions for the query, depending on the search terms submitted
$conditions = array();

//Working queries. format: $where = "where tf:containsText($a, '$kw')";
//For sc search use []filter, [tf:containstText(., '$kw')] so that sort works

/*if ($kw) {
   foreach ($kwarray as $k){
   array_push ($conditions, "tf:containsText(., '$kw') ");
   }
}*/

if (($kw) and !($phrase)) {
      $all = 'let $allrefs := (';
      //$allcount = 'let $allcounts := (';
      for ($i = 0; $i < count($kwarray); $i++) {
	$term = "'$kwarray[$i]'";
	$let .= "let \$ref$i := tf:createTextReference(\$a//p, $term) ";
	//$let .= "let \$count$i := tf:createTextReference(\$a//p//text(), $term) ";
	if ($i > 0) { $all .= ", "; $allcount .= ", "; } //remove $allcount?
	$all .= "\$ref$i"; 
	//$allcount .= "\$count$i"; 
        array_push($conditions, "tf:containsText(., $term)");
      }

      $all .= ") ";
      $let .= $all;
      //$allcount .= ") ";
      //$let .= $allcount;
//print("DEBUG: all=$all, let=$let");
}

if (($phrase) and !(kw)) {
   foreach ($phrarray as $r){
   array_push ($conditions, "tf:containsText(., '$r') ");
   //$let .= "let \$phrref := tf:createTextReference(\$a//p, '$r') 
   //let \$allrefs := (\$phrref) ";
	$wordcount = count($phrarray); 
	}
}

if (($kw) and ($phrase)) {
      $all = 'let $allrefs := (';
      for ($i = 0; $i < count($kwarray); $i++) {
	$term = "'$kwarray[$i]'";
	$let .= "let \$ref$i := tf:createTextReference(\$a//p, $term) ";
	if ($i > 0) { $all .= ", "; } 
	$all .= "\$ref$i";}
   foreach ($phrarray as $r){
   array_push ($conditions, "tf:containsText(., '$r') ");
   $let .= "let \$phrref := tf:createTextReference(\$a//p, '$r') ";
   $all .= ", \$phrref";              //this is OK if there is only one phrase
   $wordcount = count($phrarray);     //if add a phrase, need to iterate
   }
      $all .= ") ";
      $let .= $all;
print("DEBUG: all=$all, let=$let");
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

//Creating the counts and totals
//have to take each individual keyword into an array.
$myterms = array();
if ($kw) {$myterms = array_merge($myterms, $kwarray); }
if ($phrase) {$myterms = array_merge($myterms, $phrarray);}
if ($title) {$myterms = array_merge($myterms, $ttlarray); }
if ($author) {$myterms = array_merge($myterms, $autharray); }
if ($date) {$myterms = array_merge($myterms, $darray); }
//if ($place) {$myterms = array_merge($myterms, $plarray); }

$return = ' return <div2> {$a/head}{$a/byline}{$a/@id}{$a/@type}{$a/docDate}  ';


if (($phrase) and !($kw)) {
   $return .= "<matches><term>\$phrarray</term><total>{xs:integer(count(\$allrefs) div $wordcount)}</total>"; 
}

if (($kw) and !($phrase)) {
   $return .= "<matches>";
   if (count($kwarray) >= 1) {	// if there are multiple terms, display count for each term
      for ($i = 0; $i < count($kwarray); $i++) {
        $return .= "<term>$kwarray[$i]<count>{count(\$ref$i)}</count></term>";
      	}
      $return .= "<total>{count(\$allrefs)}</total></matches>";
      }
}

if (($kw) and ($phrase)) {
   if (count($kwarray) >= 1) {	// if there are multiple terms, display count for each term
      for ($i = 0; $i < count($kwarray); $i++) {
      $return .= "<matches><term>$kwarray[$i]<count>{count(\$ref$i)}</count></term>"; 
      }
      $return .= "<term>$phrarray</term><count>{xs:integer(count(\$allrefs) div $wordcount)}</count></term>";
      $return .= "</matches>";
	   	}
	   }


// if this is a keyword in context search, get context nodes
// return previous pagebreak (get closest by max of previous sibling pb & previous p/pb)
if ($kwic == "true") {
  $return .= '<context><page>{tf:highlight($a//p[';
  if ($mode == "exact") { 
    $return .= "tf:containsText(.//text()[not(parent::figDesc)], '$kw')"; 
  } else {
    for ($i = 0; $i < count($kwarray); $i++) { 
      $term = ($mode == "phonetic") ? "tf:phonetic('$kwarray[$i]')" : "'$kwarray[$i]'";
      if ($i > 0) { $return .= " or "; }
      $return .= "tf:containsText(.//text()[not(parent::figDesc)], $term) ";
    }
  }
  $return .= '], $allrefs, "MATCH")}</page></context>';
}
$return .= '</div2>';



$countquery = "$declare <total>{count($for $where] return \$a)}</total>";

if (($kw) OR ($phrase)) {	// only sort by # of matches if it is defined
   $sort = 'sort by (xs:int(matches/total) descending)';
}


$query = "$declare $for$where] $let $return $sort";
$tamino->xquery($countquery);
$total = $tamino->findNode("total");
$tamino->xquery($query);
$tamino->getXqueryCursor();

$xsl_file = "scsearch.xsl";
$kwic1_xsl = "kwic-towords.xsl";
$kwic2_xsl = "kwic-words.xsl";

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
print "<p class='center'>Number of matching articles: <b>$total</b><br/>" . ($kwic == "true" ? "Keyword in Context " : "") . "Search Results</p>";

  $myopts = "keyword=$kw&title=$title&author=$author&date=$date&place=$place&mode=$mode";
  // based on KWIC mode, set options for search link & transform result appropriately
  switch ($kwic) {
     case "true": $altopts = "$myopts&pos=$position&max=$maxdisplay&kwic=false";
 	    	$mylink = "Summary"; 
	        $myopts .= "&kwic=true";	// preserve for result links
		$tamino->xslTransform($kwic1_xsl);
		//		print "DEBUG: went through one transform.";
		//		  $tamino->displayXML(1);
		$tamino->xslTransformResult($kwic2_xsl);
		//		print "DEBUG: went through second transform.";
		//$tamino->displayXML(1);
		$xsl_params{"mode"} = "kwic";
		$tamino->xslTransformResult($xsl_file, $xsl_params);
		break;
     case "false": $altopts .= "$myopts&pos=$position&max=$maxdisplay&kwic=true";
		$mylink = "Keyword in Context"; 
		$xsl_params{"selflink"} = "search.php?$myopts";
		$tamino->xslTransform($xsl_file, $xsl_params);
		break;
  }


  $tamino->count = $total;	// set tamino count from first (count) query, so resultLinks will work
//  $rlinks = $tamino->resultLinks("search.php?$myopts", $position, $maxdisplay);
 // print $rlinks;

  // kwic/summary results toggle only relevant if search includes keywords
  if ($kw) {
    print "<p>View <a href='search.php?$altopts'>$mylink</a> search results. </p>";
  }

  if ($kwic == "true") {
    print "<p class='tip'>Page numbers indicate where paragraphs containing search terms begin.</p>";
  }




//$tamino->xslTransform($xsl_file, $xsl_params);
$tamino->highlightInfo($myterms);
$tamino->printResult($myterms);
}

print '</div>'; // end of content div

//Function that takes multiple terms separated by white spaces and puts them into an array
function processterms ($str) {
// clean up input so explode will work properly

    $str = preg_replace("/\s+/", " ", $str);  // multiple white spaces become one space
    $str = preg_replace("/\s$/", "", $str);	// ending white space is removed
    $str = preg_replace("/^\s/", "", $str);  //beginning space is removed

    $terms = explode(" ", $str);    // multiple search terms, divided by spaces
    
    return $terms;
    }


    // this fn is here until it can be added to CTI/xmlDbConnection.class.php
   // print out links to different result sets, based on cursor
   // arguments: url to link to (pos & max # to display will be added), max
   function resultLinks ($url, $position, $max) {
     //FIXME: at least in exist, we can get a default maximum from result set itself...
     $result = "<div class='resultlink'>";
	if ($this->count > $max) {
	  $result .= "<li class='firstresultlink'>More results:</li";
	  for ($i = 1; $i <= $this->count; $i += $max) {
	    if ($i == 1) {
	      $result .= '<li class="firstresultlink">';
	    } else { 
	      $result .= '<li class="resultlink">';
	    }
            // url should be based on current search url, with new position defined
	    $myurl = $url .  "&pos=$i&max=$max";
            if ($i != $position) {
	      $result .= "<a href='$myurl'>";
	    }
    	    $j = min($this->count, ($i + $max - 1));
    	    // special case-- last set only has one result
    	    if ($i == $j) {
      	      $result .= "$i";
    	    } else {
      	      $result .= "$i - $j";
    	    }
	    if ($i != $position) {
      	      $result .= "</a>";
    	    }
    	    $result .= "</li>";
	  }
	}
	$result .= "</div>"; 
	return $result;
   }

include("xml/footer.xml");

?>

</body>
</html>
