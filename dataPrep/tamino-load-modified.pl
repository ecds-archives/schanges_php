#!/usr/bin/perl -w

use XML::Twig;
use File::stat;
use strict;

my($gmt_diff);
$gmt_diff = 5;	# difference from local time to GMT



my($debug, $usage, $exename);
$debug = 0;
$exename = "tamino-load-modified.pl";
$usage = "Usage:
 $exename --db DATABASE --coll COLLECTION  [--root TEI.2] --xmldir dir

 $exename compares local xml files with tamino files, and optionally loads
 local files that are not currently in tamino, as well as files that have
 been modified more recently than the corresponding tamino file.

   Options:
	-d,--db		Tamino Database
	-c,--coll	Tamino collection
	-r,--root	root element in collection (defaults to TEI.2)
	-x,--xmldir	Location for local copies of xml files in tamino
	-h,--help	Display usage information
";

my($arg, $argmax, $db, $coll, $root, $xml_dir);

$argmax = $#ARGV;
## get command line arguments & filenames
for (my $i = 0; $i <= $argmax; $i++){
  $arg = shift(@ARGV);
  if    (($arg =~ /^-d/)||($arg =~ /^--db/))     { $i++; $db = shift(@ARGV); }
  elsif (($arg =~ /^-c/)||($arg =~ /^--coll/))   { $i++; $coll = shift(@ARGV); }
  elsif (($arg =~ /^-r/)||($arg =~ /^--root/))   { $i++; $root = shift(@ARGV);}
  elsif (($arg =~ /^-x/)||($arg =~ /^--xmldir/)) { $i++; $xml_dir = shift(@ARGV);}
  elsif (($arg =~ /^-h/)||($arg =~ /^--help/))   { print $usage; exit(); }
}

if (!($db) || !($coll) || !($xml_dir)) {
  print "Error: database, collection, and xml directory *must* be defined!\n";
  print $usage;
  exit();
}
## default root element is TEI.2 (specify anything else on command line)
if (!($root)) {
  $root = "TEI.2";
}


my($tamino_host, $xquery, $url, $tamino_outfile, $file, $twig, $result, $tei);

$tamino_host = "vip.library.emory.edu";
$xquery = 'declare namespace tf="http://namespaces.softwareag.com/tamino/TaminoFunction"
declare namespace xs = "http://www.w3.org/2001/XMLSchema"
declare namespace xf="http://www.w3.org/2002/08/xquery-functions"
for $q in input()/TEI.2
let $docname := tf:getDocname(xf:root($q))
let $id := tf:getInoId(xf:root($q))
let $time := tf:getLastModified(xf:root($q))
return <TEI.2>
<docName>{$docname}</docName>
<lastModified>{$time}</lastModified>
<id>{$id}</id>
</TEI.2>';
$tamino_outfile = "/tmp/tamino_docname.$$.xml";
$url = "http://$tamino_host/tamino/$db/$coll?_xquery=$xquery";
$url =~ s/\s+/ /g;
if ($debug) { print "url is:\n$url\n"; }
system("wget -q '$url' --output-document=$tamino_outfile");

## sample xml result from running an xquery for docnames, modtimes, and ids
$twig = XML::Twig->new();

$twig->parsefile($tamino_outfile);

$result = $twig->root->first_child('xq:result');

my(%tamino_modtime, %tamino_id);

## build hashes with the info from tamino response
foreach $tei ($result->children('TEI.2')){
  $tamino_modtime{$tei->first_child_text('docName')} =
    	$tei->first_child_text('lastModified');
  $tamino_id{$tei->first_child_text('docName')} =
    $tei->first_child_text('id');
}

if ($debug) {
  ## output results from tamino query
  print "Document results from tamino:\n";
  foreach my $key (sort(keys(%tamino_modtime))) {
    printf("%-25s\tLast modified %s\tino:id %s\n",
	   $key, $tamino_modtime{$key}, $tamino_id{$key});
  }
}

my(@local_files, $f, $filestats, @modtime, $filedate, @reload, @unloaded);

if (opendir(DIR, $xml_dir)) { } # success
else { confess("Couldn't open local xml directory $xml_dir"); }
@local_files = grep { /.*\.xml$/ && -f "$xml_dir/$_" } readdir(DIR);
closedir DIR;

my($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst);

## Compare local files with the copies loaded in tamino
foreach $f (sort(@local_files)) {
  if ($debug) { print "Comparing local file $f with tamino copy.\n"; }
  if (!($tamino_modtime{$f})) {
#    print "Error: local file $f does not seem to be loaded in tamino.\n";
    push(@unloaded, $f);
    next;
  }
  $filestats = stat("$xml_dir/$f");
  ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($filestats->mtime);
  ## generate tamino-formatted date for comparison
  # year-mon-mdayThour:min:secZ
  $filedate = sprintf("%04d-%02d-%02dT%02d:%02d:%02dZ", $year + 1900, $mon + 1,
		      $mday, $hour + $gmt_diff, $min, $sec);
  if ($debug) {
    print "$f: local mod time=$filedate\ttamino mod time=$tamino_modtime{$f}\n";
  }

  ## Note: tamino filedates seem to be in GMT ... ?
  ## compare modification times
  if ($filedate gt $tamino_modtime{$f}) {
#    print "Local file $f has more recent modification time than tamino version.\n";
    push(@reload, $f);
  }  #elsif ($filedate lt $tamino_modtime{$f}) {
#    print "Tamino copy of $f has more recent modification time than local version.\n";
  #}
}

my($continue);


if ($#reload >= 0) {
  print "These files have been modified locally since they were loaded in tamino:\n";
  foreach $f (@reload) { print "$f "; }
  print "\n";
  print "Reload these files to tamino? (y/n) ";
  $continue = readline(*STDIN);
  chop($continue);
  if ($continue =~ m/[yY]/) {
    # delete the files
    system("tamino-delete-byname.pl -d $db -c $coll @reload");
    # now load the new ones
    foreach $f (@reload) {
      # give files full path
      $f = "$xml_dir/$f";
    }
    # run all files at once (so logfile is useful, not overwritten by last one)
    system("tamino-load.pl -d $db -c $coll @reload");
  }
}

if ($#unloaded >= 0) {
  print "These files are not currently loaded in tamino:\n";
  foreach $f (@unloaded) { print "$f "; }
  print "\n";
  print "Load these files to tamino? (y/n) ";
  $continue = readline(*STDIN);
  chop($continue);
  if ($continue =~ m/[yY]/) {
    # load the new files
    foreach $f (@unloaded) {
      system("tamino-load.pl -d $db -c $coll $xml_dir/$f");
    }
  }
}

# nothing to do
if (($#unloaded < 0) && ($#reload < 0)) {
  print "All files are loaded and up to date in tamino.\n";
}

