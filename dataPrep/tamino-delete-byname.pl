#!/usr/bin/perl -w

# tamino-delete-byname.pl
# Rebecca Sutton Koeser, March 2004.
# Queries tamino by document name to get ino:id,
# then deletes by the document by id

use XML::Twig;

my ($debug, $db, $coll, $root, $argmax, $arg, @files);
$debug = 0;

my($usage, $exename);
$exename = "tamino-delete-byname.pl";
$usage = "Usage:
 $exename --db DATABASE --coll COLLECTION [--root TEI.2] file [file2 [file3]]

 $exename deletes one or several files from the specified database and
 collection in tamino.

   Options:
	-d,--db		Tamino Database
	-c,--coll	Tamino collection
	-r,--root	root element in collection (defaults to TEI.2)
	-h,--help	Display usage information

Note: filenames should specified without any path, e.g. 'file.xml' .

";
$argmax = $#ARGV;

## get command line arguments & filenames
for (my $i = 0; $i <= $argmax; $i++){
  $arg = shift(@ARGV);
  if    (($arg =~ /^-d/)||($arg =~ /^--db/))   { $i++; $db = shift(@ARGV); }
  elsif (($arg =~ /^-c/)||($arg =~ /^--coll/)) { $i++; $coll = shift(@ARGV); }
  elsif (($arg =~ /^-r/)||($arg =~ /^--root/)) { $i++; $root = shift(@ARGV); }
  elsif (($arg =~ /^-h/)||($arg =~ /^--help/)) { print $usage; exit(); }
  ## any other arguments should be filenames
  else { push(@files, $arg); }
}

if (!($db) || !($coll)) {
  print "Error: database and collection must be defined!\n";
  print $usage;
  exit();
}

## default root element is TEI.2 (specify anything else on command line)
if (!($root)) {
  $root = "TEI.2";
}

if ($debug) {
  print "Settings:\tDB = $db\tcollection = $coll\troot element = $root\n";
}

my($tamino_host, $xquery_a, $xquery_b);
$tamino_host = "vip.library.emory.edu";
$xquery_a = 'declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
declare namespace xs = "http://www.w3.org/2001/XMLSchema"
declare namespace xf="http://www.w3.org/2002/08/xquery-functions"
for $q in input()/TEI.2
let $docname := tf:getDocname(xf:root($q))
let $id := tf:getInoId(xf:root($q))
where $docname eq "';
$xquery_b = '"
return <TEI.2>
<id>{$id}</id>
</TEI.2>';


my($url, $del_url, $tamino_outfile, $twig, $result, $tei, $tamino_id, $msg, $msg_code, $rval);
foreach my $f (@files) {
  ## query tamino to get ino:id
  $url = "http://$tamino_host/tamino/$db/$coll?_xquery=$xquery_a$f$xquery_b";
  $url =~ s/\s+/ /g;
  $tamino_outfile = "/tmp/tamino-$$.$f";
  system("wget -q '$url' --output-document=$tamino_outfile");

  $twig = XML::Twig->new();
  $twig->parsefile($tamino_outfile);
  $result = $twig->root->first_child('xq:result');
  $tei = $result->first_child('TEI.2');
  ## if $tei is undefined, no match was returned
  if ($tei) {
    $tamino_id =  $tei->first_child_text('id');
    if ($debug) { print "ino:id for $f is $tamino_id\n"; }
  } else {
    print "Error: No matching document found for $f.\n";
    next;
  }

  # delete temporary file when we are done with it
  system("rm $tamino_outfile");

  ## delete according to ino:id
  $del_url = "http://$tamino_host/tamino/$db/$coll?_delete=/$root" . '[@ino:id' . "='$tamino_id']";
  if ($debug) { print "delete url is:\n$del_url\n"; }
  system("wget -q '$del_url' --output-document=$tamino_outfile");

  $twig->parsefile($tamino_outfile);
  $result = $twig->root->first_child('ino:message');
  $rval = $result->att('ino:returnvalue');
  $msg = $result->first_child('ino:messagetext');
  $msg_code =  $tei->att('ino:code');

  # Note: this error should never happen since we just looked up the ino:id
  if (($msg_code) && ($msg_code eq "INOXIE8300")) {
    print "Error: No matching document found.\n";
  }
  ## expected response from tamino for successful deletion
  if (($rval eq '0') &&
      ($result->first_child_text('ino:messageline') =~ /document\(s\) deleted/)) {
    print "Successfully deleted $f from tamino.\n"
  }

  # delete temporary file (again)
  system("rm $tamino_outfile");

}


#_delete=/TEI.2[@ino:id='6']
