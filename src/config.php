<?php

/* Configuration settings for entire site */

// pick up login/authorization information
  //session_start();

// set level of php error reporting --  ONLY display errors
// (will hide ugly warnings if databse goes offline/is unreachable)
  error_reporting(E_ALL ^ E_NOTICE);	// for production
  //error_reporting(E_ERROR | E_PARSE);    // for development
$in_production = false;

// root directory and url where the website resides
// production version

if($in_production==true) {
$basedir = "/home/httpd/html/beck/southernchanges";
$server = "bohr.library.emory.edu";
 $webserver = "beck.library.emory.edu";
$base_path = "/southernchanges";
$base_url = "http://$webserver$base_path/";
 $port = "7080";
 } else {


//root directory and url for wilson website
//development
$basedir = "/~christopherpollette";
$server = "kamina.library.emory.edu";
 $webserver = "localhost";
$base_path = "/~christopherpollette/schanges/schange";
$base_url = "http://$webserver$base_path/";
$port = "8080";
 }


// add basedir to the php include path (for header/footer files and lib directory)
set_include_path(get_include_path() . ":" . $basedir . ":" . "$basedir/lib" . ":" . "$basedir/web/xml");

//shorthand for link to main css file
$cssfile = "web/css/schanges.css";
$csslink = "<link rel='stylesheet' type='text/css' href='$base_url/$cssfile'>";


if($in_production==true) {
  $port = "7080";
 } else {
  $port = "8080";
 }

$db = "schanges";

$exist_args = array('host'   => $server,
	      	    'port'   => $port,
		    'db'     => $db,
		    'dbtype' => "exist");

// shortcut to include common tei xqueries
$teixq = 'import module namespace teixq="http://www.library.emory.edu/xquery/teixq" at
"xmldb:exist:///db/xquery-modules/tei.xqm"; '; 


?>
