<?php

include_once("config.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

//$id = $_GET["dcidentifier"];


html_head("Overview", true);

include("xml/overview-head.xml");

print '<div class="content">';
print '<h2>Overview</h2>';

print transform("xml/overview.xml", "xsl/overview.xsl"); 



print '<div class="acklink"><a href="acknowledgments.php">Acknowledgments</a></div>';

include("xml/footer.xml");

?> 


</body>
</html>
