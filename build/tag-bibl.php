#!/usr/bin/php
<?php

/* clean up the bibl section of the southern changes teiHeader
   --splits it out into tagged sections, adds pubPlace and publisher,
   calculates date value and adds in YYYY-MM format

   caveats:
   * doesn't handle seasons (spring,fall, etc.) in dates
   * for multi-month dates split by - or /, uses first month only
   * strotime function seems to have trouble with year-only dates in the 2000s
   
Dates that are incorrectly recognized will be output with a value of 1969-12 (0 time in unix)
(FIXME: better way to handle this?)
     
 */

$input = $argv[1];

$xmlfiles = array();

// if arg is a directory, process all the xml files
if (is_dir($input)) {
  $files = scandir($input);
  // find all the xml files
  foreach ($files as $f) {
    if (substr($f, strlen($f) - 4, strlen($f)) == ".xml") {
      array_push($xmlfiles, "$input/$f");	// use full path to file
    }
  }
} elseif (is_file($input)) {
  array_push($xmlfiles, $input);
} else {
  // neither file nore directory
  print "Error: no file or directory specified.\n";
  exit;	
}



$doc = new DOMDocument();
$doc->resolveExternals = false;
$doc->substituteEntities = false;

foreach ($xmlfiles as $file) {

  $xmlparse = $doc->load($file);
  if (! $xmlparse) {
    print "Error: could not parse $file\n";
    continue;
  }
  
  $xpath = new DOMXpath($doc);
  $nodelist = $xpath->query("/TEI.2/teiHeader/fileDesc/sourceDesc/bibl");
  if ($nodelist->length != 1) {
    // there should be one and only one match; if not, skip this file
    print "Warning: bibl not found; skipping $file\n";
    continue;
  }
  $bibl = $nodelist->item(0);
  
  $bibltext = $bibl->nodeValue;
  // clean up whitespace to simplify subsequent regexp
  $bibltext = preg_replace("/\s+/", ' ', $bibltext);
  
  // split out the component parts of the bibl
  $matches = array();
  preg_match("/^(Southern\s+Changes)\.\s*(Volume.*),\s*(Number[^,.]*)[,.]\s*(.*)$/", $bibltext, $matches);
  // first match is the entire string; subsequent matches are () sections of the regexp
  $title = $matches[1];
  $vol = $matches[2];
  $number = $matches[3];
  $date =  $matches[4];
  // get rid of any commas that might confuse strtotime, and standardize date display format
  $date = str_replace(",", "", $date);
  
  if (strpos($date, '-')) {
    // some dates are in format month1-month2 year; use first month only for date value
    $datestr = preg_replace("/-\w* /", " ", $date);
  } else if (strpos($date, '/')) {
    // other dates are in format month1/month2 year; treat same as above
    $datestr = preg_replace("/\/\w* /", " ", $date);
  } else {
    $datestr = $date;
  }
  
  // convert text time to unix time that can be output in the proper format
  $datetime = strtotime($datestr, 0);
  
  // clear out text-only version of bibl
  $bibl->nodeValue = "";
  
  
/* Add the content back, delimited by tags.  New format looks like this:
    <bibl>
    	<title>Southern Changes</title>.
    	<biblScope type="volume">Volume 2</biblScope>,
   	<biblScope type="issue">Number 6</biblScope>.
    	<date value="1979-06">June-July 1979</date>.
	<pubPlace>Atlanta, GA</pubPlace>:
	<publisher>Southern Regional Council</publisher>.
    </bibl>
*/

  $bibl->appendChild($doc->createElement("title", $title));
  $bibl->appendChild($doc->createTextNode(". "));
  $biblscope = $bibl->appendChild($doc->createElement("biblScope", $vol));
  $biblscope->setAttribute("type", "volume");
  $bibl->appendChild($doc->createTextNode(", "));
  $biblscope = $bibl->appendChild($doc->createElement("biblScope", $number));
  $biblscope->setAttribute("type", "issue");
  $bibl->appendChild($doc->createTextNode(". "));
  $datenode = $bibl->appendChild($doc->createElement("date", "$date"));
  $datenode->setAttribute("value", date("Y-m", $datetime));
  $bibl->appendChild($doc->createTextNode(". "));
  $bibl->appendChild($doc->createElement("pubPlace", "Atlanta, GA"));
  $bibl->appendChild($doc->createTextNode(": "));
  $bibl->appendChild($doc->createElement("publisher", "Southern Regional Council"));
  $bibl->appendChild($doc->createTextNode("."));
  
  
  /* when we're happy with the new output, save back to a file-- original or a new one? */
  $doc->save($file);
}

?>