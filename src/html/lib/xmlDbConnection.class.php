<?php 

include "taminoConnection.class.php";
include "existConnection.class.php";

class xmlDbConnection {

  // connection parameters
  var $host;
  var $port;
  var $db;
  var $coll;
  var $dbtype; 	// tamino,exist
  // whether or not to display debugging information
  var $debug;
  
  // these variables used internally
  var $xmldb;	// tamino or exist class object
  var $xsl_result;

  // xml/xpath variables - references
  var $xml;
  var $xpath;

  // array of xsl & parameters to use in xsl transform
  var $xslt;

  // variables for return codes/messages?

  // cursor variables (needed here?)
  var $cursor;
  var $count;
  var $position;

  // variables for highlighting search terms
  var $begin_hi;
  var $end_hi;

  // for outputting debugging xml in a more helpful way
  var $debug_count;

  function xmlDbConnection($argArray) {
    $this->host = $argArray['host'];
    $this->db = $argArray['db'];
    //    $this->coll = $argArray['coll'];
    $this->debug = $argArray['debug'];

    $this->dbtype = $argArray['dbtype'];
    if ($this->dbtype == "exist") {
      // create an exist object, pass on parameters
      $this->xmldb = new existConnection($argArray);
    } else {	// for backwards compatibility, make tamino default
      // create a tamino object, pass on parameters
     $this->xmldb = new taminoConnection($argArray);
    }

    // xmlDb count is the same as tamino or exist count 
    $this->count =& $this->xmldb->count;
    // xpath just points to tamino xpath object
    $this->xml =& $this->xmldb->xml;
    $this->xpath =& $this->xmldb->xpath;


    // array of xsl & parameters to use in xsl transform
    // structure: xslt[]["xsl"] = xslt filename
    //		  xslt[]["param"] = array of parameters
    $this->xslt = array();

    // variables for highlighting search terms
    // begin highlighting variables are now defined when needed, according to number of terms
    $this->end_hi = "</span>";

    $this->debug_count = 0;

    define("DATABASE_XML", 0);
    define("TRANSFORMED_XML", 1);
  }

  // send an xquery & get xml result
  function xquery ($query, $position = NULL, $maxdisplay = NULL) {
    // pass along xquery & parameters to specified xml db
    $this->xmldb->xquery($this->encode_xquery($query), $position, $maxdisplay);
    if ($this->debug) {
      $this->displayXML();
    }
  }

  // x-query : should only be in tamino...
  function xql ($query, $position = NULL, $maxdisplay = NULL) {
    // pass along xql & parameters to specified xml db
    $this->xmldb->xql($this->encode_xquery($query), $position, $maxdisplay);
    if ($this->debug) {
      $this->displayXML();
    }
  }

  // retrieve cursor, total count    (xquery cursor is default)
  function getCursor () {
    $this->xmldb->getCursor();
  }

  // explicit xquery cursor - for backwards compatibility
  function getXqueryCursor () {
    $this->xmldb->getCursor();
  }

  // get x-query cursor (for backwards compatibility)
  function getXqlCursor () {
    $this->xmldb->getXqlCursor();
  }


  // bind an xslt to parameters
  function xslBind ($file, $params = NULL) {
    $i = count($this->xslt);
    $this->xslt[$i]["xsl"] = $file;
    $this->xslt[$i]["param"] = $params;
  }

  // successively transform xml by all bound xslts, in the order that they were bound
  function transform() {
    $input = DATABASE_XML;	// default initial input
    for ($i = 0; $i < count($this->xslt); $i++) {
      $this->xslTransform($this->xslt[$i]["xsl"], $this->xslt[$i]["param"], $input);
      $input = TRANSFORMED_XML;	// use result of last xsl transform for all subsequent inputs
      // if debugging is turned on, display transform information & output result
      // (particularly important for debugging more than one transform in a row)
      if ($this->debug) {
	print "XSLT transform with stylesheet " . $this->xslt[$i]["xsl"];
	if (count($this->xslt[$i]["param"])) {
	  print "<br>\nParameters: \n";
	  foreach ($this->xslt[$i]["param"] as $key => $val) print "$key => $val \n";
	}
	print "<br>\n";
	print "Result of transformation:<br>\n";
	print $this->displayXML(TRANSFORMED_XML);
      }
    }
  }
  
   // transform the database returned xml with a specified stylesheet
  function xslTransform ($xsl_file, $xsl_params = NULL, $input = DATABASE_XML) {
     /* load xsl & xml as DOM documents */
     $xsl = new DomDocument();
     $xsl->load("xsl/$xsl_file");

     /* create processor & import stylesheet */
     $proc = new XsltProcessor();
     $xsl = $proc->importStylesheet($xsl);
     if ($xsl_params) {
       foreach ($xsl_params as $name => $val) {
         $proc->setParameter(null, $name, $val);
       }
     }
     /* transform the xml document and store the result */
     if ($input == DATABASE_XML) 	  // transform xml retrieved from the database (default)
       $this->xsl_result = $proc->transformToDoc($this->xmldb->xml);
     else if ($input == TRANSFORMED_XML)  // transform xml resulting from prior xsl transform
       $this->xsl_result = $proc->transformToDoc($this->xsl_result);
   }

 // transform the xml created by the previous xsl transform with a specified stylesheet
   function xslTransformResult ($xsl_file, $xsl_params = NULL) {
     // call default xslTransform with option to specify xml input
     $this->xslTransform($xsl_file, $xsl_params, TRANSFORMED_XML);
   }


   function printResult ($term = NULL) {
     if ($this->xsl_result) {
       if ($term[0] != NULL) {
         $this->highlightXML($term);
         // this is a bit of a hack: the <span> tags used for
         // highlighting are strings, and not structural xml; this
         // allows them to display properly, rather than with &gt; and
         // &lt; entities
	 //print html_entity_decode($this->xsl_result->saveXML());
	print $this->xsl_result->saveXML();
       } else {
         print $this->xsl_result->saveXML();
       }
     }

   }

   // get the content of an xml node by name when the path is unknown
   function findNode ($name, $node = NULL) {
     // this function is for backwards compatibility... 
     if (isset($this->xpath)) {     // only use the xpath object if it has been defined
       $n = $this->xpath->query("//$name");
       // return only the value of the first one
       if ($n) { $rval = $n->item(0)->textContent; }
     } else {
       $rval =0;
     }
     return $rval;
   }


   // highlight text within the xml structure
   function highlightXML ($term) {
     // if span terms are not defined, define them now
     if (!(isset($this->begin_hi))) { $this->defineHighlight(count($term)); }
     $this->highlight_node($this->xsl_result, $term);
   }

   // recursive function to highlight search terms in xml nodes
   function highlight_node ($n, $term) {
     // build a regular expression of the form /(term1)|(term2)/i 
     $regexp = "/"; 
     for ($i = 0; $term[$i] != ''; $i++) {
       if ($i != 0) { $regexp .= "|"; }
         $regterm[$i] = str_replace("*", "\w*", $term[$i]); 
         $regexp .= "($regterm[$i])";
       }
     $regexp .= "/i";	// end of regular expression

     $children = $n->childNodes;
     foreach ($children as $i => $c) {
       if ($c instanceof domElement) {		
	 $this->highlight_node($c, $term);	// if a generic domElement, recurse 
       } else if ($c instanceof domText) {	// this is a text node; now separate out search terms

         if (preg_match($regexp, $c->nodeValue)) {
           // if the text node matches the search term(s), split it on the search term(s) and return search term(s) also
           $split = preg_split($regexp, $c->nodeValue, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

           // loop through the array of split text and create text nodes or span elements, as appropriate
           foreach ($split as $s) {
	     if (preg_match($regexp, $s)) {	// if it matches, this is one of the terms to highlight
	       for ($i = 0; $regterm[$i] != ''; $i++) {
	         if (preg_match("/$regterm[$i]/i", $s)) { 	// find which term it matches
                   $newnode = $this->xsl_result->createElement("span", htmlentities($s));
	           $newnode->setAttribute("class", "term" . ($i+1));	// use term index for span class (begins at 1 instead of 0)
	         }
	       }
             } else {	// text between search terms - regular text node
	       $newnode = $this->xsl_result->createTextNode($s);
	     }
	    // add newly created element (text or span) to parent node, using old text node as reference point
	    $n->insertBefore($newnode, $c);
           }
           // remove the old text node now that we have added all the new pieces
           $n->removeChild($c);
	 }
       }   // end of processing domText element
     }	
   }

   // print out search terms, with highlighting matching that in the text
   function highlightInfo ($term) {
     if (!(isset($this->begin_hi))) { $this->defineHighlight(count($term)); }
     if (isset($term[0])) {
       print "<p align='center'>The following search terms have been highlighted: ";
       for ($i = 0; isset($term[$i]); $i++) {
	 print "&nbsp; " . $this->begin_hi[$i] . htmlentities($term[$i]) . "$this->end_hi &nbsp;";
       }
       print "</p>";
     }
   }

   // create <span> tags for highlighting based on number of terms
   function defineHighlight ($num) {
     $this->begin_hi = array();
    // strings for highlighting search terms 
    for ($i = 0; $i < $num; $i++) {
      $this->begin_hi[$i]  = "<span class='term" . ($i + 1) . "'>";
    }
   }

   // print out links to different result sets, based on cursor
   // arguments: url to link to (pos & max # to display will be added), max
   function resultLinks ($url, $position, $max) {
     //FIXME: at least in exist, we can get a default maximum from result set itself...
     $result = "<div class='resultlink'>";
	if ($this->count > $max) {
	  $result .= "<li class='firstresultlink'>More results:</li";
	  for ($i = 1; $i <= $this->count; $i += $max) {
	    if ($i == 1) {
	      $result .= '<li class="firstresultlink">';
	    } else { 
	      $result .= '<li class="resultlink">';
	    }
            // url should be based on current search url, with new position defined
	    $myurl = $url .  "&pos=$i&max=$max";
            if ($i != $position) {
	      $result .= "<a href='$myurl'>";
	    }
    	    $j = min($this->count, ($i + $max - 1));
    	    // special case-- last set only has one result
    	    if ($i == $j) {
      	      $result .= "$i";
    	    } else {
      	      $result .= "$i - $j";
    	    }
	    if ($i != $position) {
      	      $result .= "</a>";
    	    }
    	    $result .= "</li>";
	  }
	}
	$result .= "</div>"; 
	return $result;
   }


  // convert a readable xquery into a clean url for tamino or exist
  function encode_xquery ($string) {
    // convert multiple consecutive white spaces (of any kind) to a single space
    // convert escaped quotes & apostrophes into their tamino entities
    $pattern = Array("/\s+/", "/\\\'/", '/\\\"/');
    $replace = Array(" ",     "&apos;", '&quot;');
    $string = preg_replace($pattern, $replace, $string);
    // convert all characters into url %## equivalent
    return urlencode($string);
  }

   // print out xml (for debugging purposes)
  function displayXML ($input = DATABASE_XML) {	// by default, display xml returned from db query

    if ($this->debug_count == 0) {	// first time outputting xml - also output javascript for show/hide functionality
      print "<script language='Javascript' type='text/javascript'>
function toggle (id) {
  var a = document.getElementById(id); 
  if (a.style.display == '') a.style.display = 'block';	//default 
  a.style.display = (a.style.display != 'none') ? 'none':'block';
}
</script>\n";
    }

    print "<a href='javascript:toggle(\"debugxml$this->debug_count\")'>+ show/hide xml</a>\n";
    print "<div id='debugxml$this->debug_count'>\n";
    print "<pre>";
    
    switch ($input) {
    case DATABASE_XML:
      if ($this->xml) {
      	$this->xml->formatOutput = true;
	print htmlentities($this->xml->saveXML());
      }
      break;
    case TRANSFORMED_XML:
      if ($this->xsl_result) {
	$this->xsl_result->formatOutput = true;
	print htmlentities($this->xsl_result->saveXML());
      }
      break;
    }
    print "</pre>";
    print "</div>";

    $this->debug_count++;
   }


  // load xml from a file (instead of getting from a query)
  function loadxml ($file) {
    $this->xml = new domDocument();
    $this->xml->load($file);
  }


}
