<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");


html_head("Acknowledgments", true);

include("xml/overview-head.xml");

print '<div class="content">';
print '<h2>Acknowledgments</h2>';


print transform("xml/acknowledgments.xml", "xsl/overview.xsl"); 


include("xml/footer.xml");

?> 


</body>
</html>
