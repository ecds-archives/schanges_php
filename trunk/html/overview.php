<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("CTI/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["dcidentifier"];

$args = array('host' => $tamino_server,
	      'db' => $tamino_db["meta-db"],
	      'coll' => $tamino_coll["meta-coll"],
	      'debug' => false);
$tamino = new xmlDbConnection($args);

html_head("Overview", true);

include("xml/overview-head.xml");

print '<div class="content">';
print '<h2>Overview</h2>';

//include("xml/overview.xml");
print transform("xml/overview.xml", "xsl/overview.xsl"); 



print '<div class="acklink"><a href="acknowledgments.php">Acknowledgments</a></div>';

include("xml/footer.xml");

?> 


</body>
</html>