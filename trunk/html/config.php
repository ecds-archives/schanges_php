<?php

/* Configuration settings for entire site */

// pick up login/authorization information
session_start();

// set level of php error reporting --  ONLY display errors
// (will hide ugly warnings if databse goes offline/is unreachable)
//error_reporting(E_ERROR);	// for production
//error_reporting(E_ERROR | E_PARSE);    // for development

// root directory and url where the website resides
// development version
/*$basedir = "/home/httpd/html/rebecca/wwiweb";
$server = "reagan.library.emory.edu";
$base_path = "/rebecca/wwiweb";
$base_url = "http://$server$base_path/";
*/
//root directory and url for Alice's website
//development
$basedir = "/Users/alice/Sites/schanges";
$server = "beckcady.library.emory.edu";
$base_path = "/~alice/schanges";
$base_url = "http://$server$base_path/"; 

//root directory and url for reagan website
//development
$basedir = "/home/httpd/html/alice/schanges";
$server = "reagan.library.emory.edu";
$base_path = "/alice/schanges";
$base_url = "http://$server$base_path/";



// root directory and url where the website resides
// production version
/* $basedir = "/home/httpd/html/cti/greatwar";
$server = "cti.library.emory.edu";
$base_path = "/greatwar";
$base_url = "http://$server$base_path/";
*/

// add basedir to the php include path (for header/footer files and lib directory)
set_include_path(get_include_path() . ":" . $basedir . ":" . "$basedir/lib" . ":" . "$basedir/content");

//shorthand for link to main css file
$cssfile = "wwi.css";
$csslink = "<link rel='stylesheet' type='text/css' href='$base_url/$cssfile'>";

/* tamino settings common to all pages
   Note: all pages use same database, but there are three (two for SRC) different collections
 */
//$tamino_server = "vip.library.emory.edu";
//tamino_db = "WW1";
/* define all these in one place so it is easy to change for testing */
//$tamino_coll["poetry"] = "poetry";
//$tamino_coll["links"] = "links";
//$tamino_coll["postcards"] = "postcards";
//$tamino_coll["postcards"] = "postcards-test";

/* tamino settings common to all pages
   Note: pages use different databases, metadata from the META(_TEST)/schangesfw-metadata, data from SRC(_TEST)/schanges
 */

$tamino_server = "vip.library.emory.edu";
$tamino_db["data-db"] = "SRC_TEST"; //Test db
//$tamino_db["data-db"] = "SRC"; //production server
$tamino_db["meta-db"] = "META_TEST"; //metadata test db
//$tamino_db["meta-db"] = "META"; //metadata production db
$tamino_coll["meta-coll"] = "schangesfw-metadata"; //metadata collection
$tamino_coll["data-coll"] = "schanges"; //data collection

?>
