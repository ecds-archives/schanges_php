<?php

// pass article id as argument, for example:
// browse.php?id=iln38.1068.002
// optionally, pass search terms for highlighting; for example;
// browse.php?id=iln38.1068.002&term=lincoln  

include("common_functions.php");
$id = $_GET["id"];
$term = $_GET["term"];
$term2 = $_GET["term2"];
$term3 = $_GET["term3"];

$args = array('host' => "vip.library.emory.edu",
		'db' => "SRC",
	      //	      'debug' => true,
		'coll' => 'schanges');
$tamino = new taminoConnection($args);


//$url = "http://tamino.library.emory.edu/passthru/servlet/transform/tamino/BECKCTR/ILN?_xql=TEI.2//div1/div2[@id='" . $id . "']";
//$url = "http://tamino.library.emory.edu/tamino/BECKCTR/ILN?_xql=TEI.2//div1/div2[@id='" . $id . "']";
$url = "http://tamino.library.emory.edu/tamino/SRC/schanges?_xquery=
for \$b in input()/TEI.2//div1/div2
where \$b/@id = '$id'
return 
\$b";

$rval = $tamino->xquery($query);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 

$xsl_file = "browse.xsl";

html_head("Browse - Article");

//include("xml/head.xml");
//include("xml/sidebar.xml");
?>

   <div class="content"> 


<?php

   if ($term) {
     print "<p align='center'>The following search terms have been highlighted: ";
     print "&nbsp; $begin_hi$term$end_hi &nbsp;";
     if ($term2) { print "&nbsp; $begin_hi2$term2$end_hi &nbsp;"; }
     if ($term3) { print "&nbsp; $begin_hi3$term3$end_hi &nbsp;"; }
     print '<hr width="50%">';
 }

if ($id) {
  // if id is defined, get the content & transform it
  $xmlContent = file_get_contents($url);
  
  $result = transform($xmlContent, $xsl_file);
  if ($term) {
    print highlight($result, $term, $term2, $term3);
  } else {
    print $result;
  }
  

} else {
  print "<p class='error'>Error: No article specified!</p>";
}

?>


  </div>
   
<?php
  include("xml/foot.xml");
?>


</body>
</html>
