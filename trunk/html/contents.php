<?php

//one possibe way to deal with messy xqueries & the urls they produce

include("common_functions.php");
//$url = 'http://tamino.library.emory.edu/passthru/servlet/transform/tamino/BECKCTR/ILN?_xquery=
$url = 'http://tamino.library.emory.edu/tamino/SRC/schanges?_xquery=
for $b in input()/TEI.2//div1
return <div1>
 {$b/@type}
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
$url = encode_url($url);

$xsl_file = "contents.xsl";
//Note: change these next lines for Schanges web pages, but parallel structure.
//html_head("Browse");

//include("xml/head.xml");
//include("xml/sidebar.xml");
?>

   <div class="content"> 
          <h2>Browse</h2>

<?php

print "<hr>";
   // use sablotron to transform xml
   $xmlContent = file_get_contents($url);
   //$result = transform($xmlContent, $xsl_file); 
   //print $result;
   print $xmlContent;

print "<hr>";

?> 
   
  </div>
   
<?php
  //include("xml/foot.xml");
?>


</body>
</html>
