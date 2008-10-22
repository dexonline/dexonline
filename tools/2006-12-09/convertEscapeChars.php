<?
// We want the internalRep field to contain ONLY Unicode.
// TODO: Fix all tables, not just definitions.
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');

$GLOBALS['htmlEntities'] = array();

$dbResult = mysql_query("select * from Definition");
$numRows = mysql_num_rows($dbResult);
$i = 0;
$changed = 0;

while ($dbRow = mysql_fetch_assoc($dbResult)) {
  $def = new Definition();
  $def->populateFromDbRow($dbRow);
  
  $newInternalRep = myConvert($def->internalRep);
  $newHtmlRep = text_htmlize($newInternalRep);
  $internalRepChanged = ($newInternalRep != $def->internalRep);
  $htmlRepChanged = ($newHtmlRep != $def->htmlRep);
  if ($internalRepChanged || $htmlRepChanged) {
    // write a custom query so we don't update the ModDate field
    // (also for speed)
    $query = sprintf("update Definition set InternalRep = '%s', " .
                     "HtmlRep = '%s' where Id = '%d'",
                     addslashes($newInternalRep),
                     addslashes($newHtmlRep),
                     $def->id);
    mysql_query($query);
    $changed++;
    print "Changed definition " . $def->id;
    if ($htmlRepChanged) {
      print " (HtmlRep has changed)";
    }
    print "\n";
  }
  $i++;
  if ($i % 1000 == 0) {
    print "$i/$numRows definitions processed, $changed changed.\n";
  }
}
mysql_free_result($dbResult);
print_r($GLOBALS['htmlEntities']);


function myConvert($s) {
  $map = array('&lt;' => '<',
               '&lt' => '<',
               '&gt;' => '>',
               '&#x113;' => 'ē',
               '&#37;' => '\\%',
               '&#x25;' => '\\%',
               '&#x0025;' => '\\%',
               '&#x7e;' => '\\~',
               '&#x27;' => "\\'",
               '&#39;' => "\\'",
               '&rsquo;' => "\\'",
               '&#xB4;' => "\\'",
               '&#x301;' => "\\'",
               '&#8220;' => '"',
               '&#8221;' => '"',
               '&#8222;' => '"',
               '&#x2a;' => '\\*',
               '&#x2A;' => '\\*',
               '&#x002A;' => '\\*',
               '&#x002a;' => '\\*',
               '&#9674;' => '*',
               '&#9830;' => '**',
               '&#064;' => '\\@',
               ',c' => ', c',
               ',C' => ', C',
               ',s' => ', s',
               ',S' => ', S',
               ',t' => ', t',
               ',T' => ', T',
               '&#8211;' => '-',
               '&#8212;' => '-',
               );
  $s = str_replace(array_keys($map), array_values($map), $s);

  $len = mb_strlen($s);
  $state = 0; // 0 = normal, 1 = after &, 2 = &#
  $chunk = '';
  $result = '';
  for ($i = 0; $i < $len; $i++) {
    $char = text_getCharAt($s, $i);

    if ($state == 0) {
      if ($char == '&') {
        $chunk = $char;
        $state = 1;
      } else {
        $result .= $char;
        $chunk = '';
      }
    } else if ($state == 1) {
      if ($char == '#') {
        $chunk .= $char;
        $state = 2;
      } else if ($char == '&') {
        $result .= $chunk;
        $chunk = $char;
      } else {
        $result .= $chunk;
        $result .= $char;
        $state = 0;
        $chunk = '';
      }
    } else if ($state == 2) {
      if ($char == ';') {
        $chunk .= $char;
        $result .= processSequence($chunk);
        $chunk = '';
        $state = 0;
      } else if ($char == '&') {
        $result .= $chunk;
        $chunk = $char;
        $state = 1;
      } else {
        $chunk .= $char;
      }
    } else {
      print "ERROR!\n";
      exit(1);
    }
  }
  $result .= $chunk;
  return $result;
}

function processSequence($chunk) {
  $len = strlen($chunk);
  if ($chunk[0] != '&' || $chunk[1] != '#' || $chunk[$len - 1] != ';') {
    print "Extracted chunk should start with &# and end with ;\n";
    exit (1);
  }

  $middle = substr($chunk, 2, $len - 3);
  if ($middle[0] == 'x' || $middle[0] == 'X') {
    $hex = substr($middle, 1);
    if (ereg("^[0-9a-fA-F]+$", $hex)) {
      $c = text_chr(hexdec($hex));
      $GLOBALS['htmlEntities'][$chunk] = $c;
      print "HEXA: $chunk -> $c\n";
      return $c;
    } else {
      print "GARBAGE: $hex\n";
      exit(1);
    }
  } else {
    $dec = $middle;
    if (ereg("^[0-9]+$", $dec)) {
      $c = text_chr((int)$dec);
      $GLOBALS['htmlEntities'][$chunk] = $c;
      print "DECIMAL: $chunk -> $c\n";
      return $c;
    } else {
      print "GARBAGE: $dec\n";
      exit(1);
    }
  }
  return $chunk;
}

?>
