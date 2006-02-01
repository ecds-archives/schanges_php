<?php

// php functions & variables used by more than one ILN php page
include("config.php");

/* Check browser & OS and determine which css file to use
   (only checking for IE -- the only one that needs different css ) 
*/
function getCSS () {
  $HTTP_USER_AGENT = getenv("HTTP_USER_AGENT");

  if (eregi ("MSIE", $HTTP_USER_AGENT)) { $browser = "MSIE"; }
  if (eregi ("mac",  $HTTP_USER_AGENT)) { $os = "mac"; }
  else if (eregi ("win",  $HTTP_USER_AGENT)) { $os = "win"; }
  
  // $css_basepath = "http://chaucer.library.emory.edu/iln/";
  // development
  $css_basepath = "http://reagan.library.emory.edu/rebecca/ilnweb/";
  // production
  //  $css_basepath = "http://cti.library.emory.edu/iln/";
  $css = "iln.css"; 
  if ($browser == "MSIE") {
    if ($os == "mac") {
      $css = "iln-iemac.css";
    } else if ($os == "win") {
      $css = "iln-iewin.css";
    }
  }
  return "$css_basepath$css";
}


/* 12.10.2004 - Added robots meta line to header, partially as a test
   to see if it would help google to index the actual articles.
*/

function html_head ($mode, $contentlist = false) {
  global $base_url;	// use base url as set in site-wide config file
  $mycss = getCSS();
print "<html>
 <head>
 <title>$mode - The Civil War in America from The Illustrated London News</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
<meta name=\"robots\" content=\"index,follow\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"$mycss\">\n";

// only load content-list javascript if needed
 if ($contentlist) {
   print "<script language=\"Javascript\" 
    src=\"$base_url/cookies.js\"></script>
<script language=\"Javascript\" 
	src=\"$base_url/content-list.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"$base_url/contents.css\">\n";
 }
print "<script language=\"Javascript\"
       src=\"$base_url/image_viewer/launchViewer.js\"></script>
 </head>";
}


// common variables for highlighting search terms
static $begin_hi  = "<span class='term1'><b>";
static $begin_hi2 = "<span class='term2'><b>";
static $begin_hi3 = "<span class='term3'><b>";
static $end_hi = "</b></span>";


// convert a readable xquery into a clean url
function encode_url ($string) {
  // get rid of multiple white spaces
  $string = preg_replace("/\s+/", " ", $string);
  // convert spaces to hex equivalent
  $string = str_replace(" ", "%20", $string);
  return $string;
}


// highlight the search strings in the text
// highlight up to three terms (optionally)
function highlight ($string, $term1, $term2 = NULL, $term3 = NULL) {
  // note: need to fix regexps: * -> \w* (any word character)

  // use global highlight variables
  global $begin_hi, $begin_hi2, $begin_hi3, $end_hi;

  // FIXME: how to deal with wild cards?
  $_match = str_replace("*", "\w*", $match);

  // Note: don't match/highlight the terms in a url (to pass to next file)
  $string = preg_replace("/([^=|']\b)($term1)(\b)/i", "$1$begin_hi$2$end_hi$3", $string);
  if ($term2) {
    $string = preg_replace("/([^=|']\b)($term2)(\b)/i", "$1$begin_hi2$2$end_hi$3", $string);
  }
  if ($term3) {
    $string = preg_replace("/([^=|']\b)($term3)(\b)/i", "$1$begin_hi3$2$end_hi$3", $string);
  }

  return $string;
}

// param arg is optional - defaults to null
function transform ($xml, $xsl_file, $xsl_params = NULL) {
  //      print "in function transform, xml is <pre>$xml</pre>, xsl is $xsl_file<br>"; 

//   print "in function transform, xsl is $xsl_file<br>";
//   if ($xml) {
//     print "is true/defined<br>";
//     print "<pre>$xml</pre>";
//   } else {    print "xml is not true/defined<br>"; }


  // create xslt handler
  $xh = xslt_create();

  // specify file base so that xsl includes will work
  // Note: last / on end of fileBase is important!
  $fileBase = 'file://' . getcwd () . '/xsl/';
  //  print "file base is $fileBase<br>";
  xslt_set_base($xh, $fileBase);

  // get xml contents from url
  //  $xmlContent = file_get_contents($url);
  //$xslContent = file_get_contents("xsl/$xsl_file");

  $args = array(
  		'/_xml'    =>    $xml
		//  		'/_xsl'    =>    $xslContent
  		);
  
  //  $result = xslt_process($xh, "xml/browse.xml", "xsl/browse.xsl");
  // $result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $args);
  //$result = xslt_process($xh, 'arg:/_xml', $xsl_file, NULL, $args);
  $result = xslt_process($xh, 'arg:/_xml', $xsl_file, NULL, $args, $xsl_params);

  if ($result) {
    // Successful transformation
  } else {
    print "Transformation failed.<br>";
    print "Error: " . xslt_error($xh) . " (error code " . xslt_errno($xh) . ")<br>";
  }
  xslt_free($xh);

  return $result;
}


?>
