<?php

//one possibe way to deal with messy xqueries & the urls they produce

include_once("taminoConnection.class.php");
include("common_functions.php");
//$url = 'http://tamino.library.emory.edu/passthru/servlet/transform/tamino/BECKCTR/ILN?_xquery=
//$url = 'http://tamino.library.emory.edu/tamino/SRC/schanges?_xquery=

$args = array('host' => "vip.library.emory.edu",
		'db' => "SRC",
	      //	      'debug' => true,
		'coll' => 'schanges');
$tamino = new taminoConnection($args);

$query = 'for $b in input()/TEI.2//div1
return <div1>
 {$b/@type}
 {$b/@id}
 {$b/head}
 { for $c in $b/div2 return
   <div2>
     {$c/@id}
     {$c/@type}
     {$c/head}
     {$c/docAuthor}
     {$c/docDate}
   </div2>
}</div1>
sort by (@id)';
//$url = encode_url($url); in taminoConnection.class

$xsl_file = "contents.xsl";
//Note: change these next lines for Schanges web pages, but parallel structure.
//html_head("Browse");

//include("xml/head.xml");
//include("xml/sidebar.xml");


$rval = $tamino->xquery($query);
if ($rval) {       // tamino Error code (0 = success)
  print "<p>Error: failed to retrieve contents.<br>";
  print "(Tamino error code $rval)</p>";
  exit();
} 


print '<div class="content"> 
          <h2>Browse</h2>'; 

print "<hr>";
//all in taminoConnection.class   
// use sablotron to transform xml
   //$xmlContent = file_get_contents($url);
   //$result = transform($xmlContent, $xsl_file); 
   //print $result;
   //print $xmlContent;

$tamino->xslTransform($xsl_file);
$tamino->printResult();


print "<hr>";
 
   
print '</div>';
   

  //include("xml/foot.xml");
?>


</body>
</html>
