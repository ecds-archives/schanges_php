<?php 

include_once("phpDOM/classes/include.php");
import("org.active-link.xml.XML");

class taminoConnection {

  // connection parameters
  var $host;
  var $db;
  var $coll;
  // whether or not to display debugging information
  var $debug;
  
  // these variables used internally
  var $base_url;
  var $xmlContent;
  var $xml;
  var $xsl_result;

  // cursor variables
  var $cursor;
  var $count;
  var $position;

  // variables for highlighting search terms
  var $begin_hi;
  var $end_hi;


  

  function taminoConnection($argArray) {
    $this->host = $argArray['host'];
    $this->db = $argArray['db'];
    $this->coll = $argArray['coll'];
    $this->debug = $argArray['debug'];

    $this->base_url = "http://$this->host/tamino/$this->db/$this->coll?";

    // variables for highlighting search terms
    $this->begin_hi[0]  = "<span class='term1'><b>";
    $this->begin_hi[1] = "<span class='term2'><b>";
    $this->begin_hi[2] = "<span class='term3'><b>";
    $this->end_hi = "</b></span>";
  }

  // send an xquery to tamino & get xml result
  // returns  tamino error code (0 for success, non-zero for failure)
  function xquery ($query) {
    $myurl = $this->base_url . "_xquery=" . $this->encode_xquery($query);
    if ($this->debug) {
      print "DEBUG: In function taminoConnection::xquery, url is $myurl.<p>";
    }

    $this->xmlContent = file_get_contents($myurl);
    if ($this->debug) {
      $copy = $this->xmlContent;
      $copy = str_replace(">", "&gt;", $copy);
      $copy = str_replace("<", "\n&lt;", $copy);
      print "DEBUG: in taminoConnection::xquery, xmlContent is <pre>$copy</pre>"; 
    }

    $length = strlen($this->xmlContent);
    if ($length < 5000) {
      // phpDOM can only handle xmlContent within certain size limits
      $this->xml = new XML($this->xmlContent);
      if (!($this->xml)) {        ## call failed
	print "TaminoConnection xquery Error: unable to retrieve xml content.<br>";
      }
      $error = $this->xml->getTagAttribute("ino:returnvalue", 
					   "ino:response/ino:message");
    } else {
      // not really a tamino error.... might have unexpected results
      $this->xml = 0;
      $error = 0;
    }
   return $error;
  }


  // send an x-query (xql) to tamino & get xml result
  // returns  tamino error code (0 for success, non-zero for failure)
  // optionally allows for use of xql-style cursor
  function xql ($query, $position = NULL, $maxdisplay = NULL) {
    if ($this->debug) {
      print "DEBUG: In function taminoConnection::xql, query is $query.<p>";
    }

    if (isset($position) && isset($maxdisplay)) {
      $xql = "_xql($position,$maxdisplay)=";
    } else {
      $xql = "_xql=";
    }

    $myurl = $this->base_url . $xql . $this->encode_xquery($query);
    if ($this->debug) {
      print "DEBUG: In function taminoConnection::xql, url is $myurl.<p>";
    }

    $this->xmlContent = file_get_contents($myurl);
    if ($this->debug) {
      $copy = $this->xmlContent;
      $copy = str_replace(">", "&gt;", $copy);
      $copy = str_replace("<", "\n&lt;", $copy);
      print "DEBUG: in taminoConnection::xql, xmlContent is <pre>$copy</pre>"; 
    }

    $length = strlen($this->xmlContent);
    if ($length < 150000) {
      // phpDOM can only handle xmlContent within certain size limits
      $this->xml = new XML($this->xmlContent);
      if (!($this->xml)) {        ## call failed
	print "TaminoConnection xquery Error: unable to retrieve xml content.<br>";
      }
      $error = $this->xml->getTagAttribute("ino:returnvalue", 
					   "ino:response/ino:message");
    } else {
      // not really a tamino error.... might have unexpected results
      $this->xml = 0;
      $error = 0;
    }
   return $error;
  }

   // convert a readable xquery into a clean url for tamino
   function encode_xquery ($string) {
     // get rid of multiple white spaces
     $string = preg_replace("/\s+/", " ", $string);
     // convert spaces to their hex equivalent
     $string = str_replace(" ", "%20", $string);
     return $string;
   }

   // retrieve the cursor & get the total count
   function getCursor () {
     // NOTE: this is an xql style cursor, not xquery
     if ($this->xml) {
       $this->cursor = $this->xml->getBranches("ino:response", "ino:cursor");
       if ($this->cursor) {
	 $this->count = $this->cursor[0]->getTagAttribute("ino:count", "ino:cursor");
       } else {
	 // no matches (or, possibly-- unable to retrieve cursor)
	 $this->count = 0;
       }
     } else {
       print "Error! taminoConnection xml variable uninitialized.<br>";
     }
   }

   // transform the tamino XML with a specified stylesheet
   function xslTransform ($xsl_file, $xsl_params = NULL) { 
     // create xslt handler
     $xh = xslt_create();
     // specify file base so that xsl includes will work
     // Note: last / on end of fileBase is important!
     $fileBase = 'file://' . getcwd () . "/xsl/";
     //  print "file base is $fileBase<br>";
     xslt_set_base($xh, $fileBase);

     $args = array('/_xml' => $this->xmlContent);
     $this->xsl_result = xslt_process($xh, 'arg:/_xml', $xsl_file, NULL, $args, $xsl_params);
     
     if ($this->xsl_result) {
       // Successful transformation
     } else {
       print "Transformation failed.<br>";
       print "Error: " . xslt_error($xh) . " (error code " . xslt_errno($xh) . ")<br>";
     }
     xslt_free($xh);
   }

   function printResult ($term = NULL) {
     if (isset($term[0])) {
       $this->highlight($term);
     }
     print $this->xsl_result;

   }

   // Highlight the search strings within the xsl transformed result.
   // Takes an array of terms to highlight.
   function highlight ($term) {
     // note: need to fix regexps: * -> \w* (any word character)
      // FIXME: how best to deal with wild cards?

     // only do highlighting if the term is defined
     for ($i = 0; (isset($term[$i]) && ($term[$i] != '')); $i++) {
       // replace tamino wildcard (*) with regexp -- 1 or more word characters 
       $_term = str_replace("*", "\w+", $term[$i]);
     // Note: regexp is constructed to avoid matching/highlighting the terms in a url 
       $this->xsl_result = preg_replace("/([^=|']\b)($_term)(\b)/i",
	      "$1" . $this->begin_hi[$i] . "$2$this->end_hi$3", $this->xsl_result);
     }
   }

   // print out search terms, with highlighting matching that in the text
   function highlightInfo ($term) {
     if (isset($term[0])) {
       print "<p align='center'>The following search terms have been highlighted: ";
       for ($i = 0; isset($term[$i]); $i++) {
	 print "&nbsp; " . $this->begin_hi[$i] . "$term[$i]$this->end_hi &nbsp;";
       }
       print "</p>";
     }
   }

}