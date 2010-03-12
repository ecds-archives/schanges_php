<?php

include_once("config.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

//$id = $_GET["dcidentifier"];


html_head("Overview", true);
print "</head>";
include("web/xml/overview-head.xml");

print '<div class="content">';
print '<h2>Overview</h2>';

print transform("web/xml/overview.xml", "xslt/overview.xsl"); 



print '<div class="acklink"><a href="acknowledgments.php">Acknowledgments</a></div>';

include("web/xml/footer.xml");

?> 


</body>
</html>
