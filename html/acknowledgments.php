<?php

include_once("config.php");
//include_once("xmlDbConnection.class.php");
include_once("lib/xmlDbConnection.class.php");
include("common_functions.php");

$id = $_GET["dcidentifier"];

/*$args = array('host' => $tamino_server,
	      'db' => $tamino_db["meta-db"],
	      'coll' => $tamino_coll["meta-coll"],
	      'debug' => false);
	      $tamino = new xmlDbConnection($args);*/

html_head("Acknowledgments", true);

include("xml/overview-head.xml");

print '<div class="content">';
print '<h2>Acknowledgments</h2>';

//include("xml/overview.xml");
print transform("xml/acknowledgments.xml", "xsl/overview.xsl"); 


include("xml/footer.xml");

?> 


</body>
</html>
