<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';

define('J_ONLY', false); // for debugging purposes, process only the letter J

define('SOURCE_ID', 1);
define('MY_USER_ID', 1);

define('ST_ERROR', 1);
define('ST_SKIPPED', 2);
define('ST_OK', 3);

Log::info('started');

if (count($argv) != 2) {
  die("Usage: {$argv[0]} <XML_directory>\n");
}

$dir = $argv[1];
$files = @scandir($dir) or die("Could not get directory contents.\n");
$numEntries = 0;
$statuses = [ ST_ERROR => 0, ST_SKIPPED => 0, ST_OK => 0 ];

foreach ($files as $file) {
  if (StringUtil::endsWith($file, '.xml') &&
      (!J_ONLY || ($file == 'j.xml'))) {
    $fullName = realpath("$dir/$file");
    Log::info("Reading file $fullName");
    $s = file_get_contents($fullName);
    $s = preprocessXmlString($s);
    $xml = new ExtendedXML($s);

    foreach ($xml->body->entry as $e) {
      $numEntries++;
      $status = processEntry($e);
      $statuses[$status]++;
    }
  }
}

Log::info('%s entries, %s errors, %s skipped, %s imported.',
          $numEntries, $statuses[ST_ERROR], $statuses[ST_SKIPPED], $statuses[ST_OK]);
Log::info('ended');


/*************************************************************************/

class ExtendedXML extends SimpleXMLElement {
  function getParent() {
    return current($this->xpath('parent::*'));
  }
}

function preprocessXmlString($s) {
  $s = str_replace('iso-entities\\', 'iso-entities/', $s);

  // doubly encode < signs so that they'll still be encoded after html_entity_decode
  $s = str_replace('&lt;', '&amp;lt;', $s);
  $s = html_entity_decode($s, ENT_HTML5, 'utf-8');
  // print_r(get_html_translation_table(HTML_ENTITIES, ENT_HTML5));

  $s = str_replace(
    [
      '&ebreve;', '&Ebreve;',
      '&ibreve;', '&Ibreve;',
    ],
    [
      'ĕ', 'Ĕ',
      'ĭ', 'Ĭ',
    ],
    $s);

  return $s;
}


// $xml is a SimpleXML <entry>
function processEntry($xml) {
  $data = collectData($xml);
  return ST_OK;

  // pick a form to search for
  $hw = mb_strtolower((string)$xml->hw); // hw is never empty
  $orth = ($xml->orth) ? mb_strtolower((string)$xml->orth) : '';
  $stress = ($xml->stress) ? mb_strtolower((string)$xml->stress) : '';
  $stress = str_replace('`', "'", $stress);

  // load matching lexems
  $lexems = [];
  if ($stress) {
    $value = $stress;
    $lexems = Model::factory('Lexem')
            ->where('form', $stress)
            ->find_many();
  }

  if (empty($lexems)) {
    // either there is no stress forms or no lexems match
    $value = $orth ? $orth : $hw;
    $lexems = Model::factory('Lexem')
            ->where('formNoAccent', $value)
            ->find_many();
  }

  if (empty($lexems)) {
    if (StringUtil::startsWith($value, '-') ||
        StringUtil::endsWith($value, '-')) {
      // Log::info("Skipping affix {$value}");
      return ST_SKIPPED;
    }
    // Log::error("No lexems for {$value}");
    return ST_ERROR;
  }

  // load matching entries
  $lexemIds = util_objectProperty($lexems, 'id');
  $entries = Model::factory('Entry')
           ->table_alias('e')
           ->select('e.*')
           ->distinct()
           ->join('EntryLexem', ['e.id', '=', 'el.entryId'], 'el')
           ->where_in('el.lexemId', $lexemIds)
           ->find_many();

  if (empty($entries)) {
    Log::error("Lexems, but no entries for {$value}");
    return ST_ERROR;
  }

  // if any of the entries have trees with meanings, skip this <entry>
  $entryIds = util_objectProperty($entries, 'id');
  $meaning = Model::factory('Meaning')
           ->table_alias('m')
           ->join('Tree', ['m.treeId', '=', 't.id'], 't')
           ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
           ->where('t.status', Tree::ST_VISIBLE)
           ->where_in('te.entryId', $entryIds)
           ->find_one();
  if ($meaning) {
    // Log::info("Skipping entry {$value} because meanings already exist.");
    return ST_SKIPPED;
  }

  // pick any of the available trees (if it exists, then it should be empty)
  $tree = Model::factory('Tree')
        ->table_alias('t')
        ->select('t.*')
        ->join('TreeEntry', ['t.id', '=', 'te.treeId'], 'te')
        ->where('t.status', Tree::ST_VISIBLE)
        ->where_in('te.entryId', $entryIds)
        ->find_one();
  if (!$tree) {
    $tree = Model::factory('Tree')->create();
    $tree->description = $entries[0]->description;
    $tree->status = Tree::ST_VISIBLE;
    $tree->save();
    TreeEntry::associate($tree->id, $entries[0]->id);
    Log::info("Created tree for {$value}");
  }

  if (Meaning::get_by_treeId($tree->id)) {
    Log::error("Tree {$tree->id} has meanings for {$value}");
    return ST_ERROR;
  }

  return ST_OK;
}

// Given a SimpleXML <entry>, collect data from it in a format we can use
function collectData($xml) {
  // collect children data
  $cdata = getMergedChildren($xml);

  switch ($xml->getName()) {
  case 'def':
    foreach ($xml->children() as $c) {
      if ($c->getName() == 'usg') {
        var_dump("*********************************************");
        var_dump($cdata);
      }
    }
    // if ($xml->count()) {
    //   $def = trim((string)$xml);
    //   $def = preg_replace('/\s+/', ' ', $def);
    //   //      var_dump($def);
    // }
    return [];

  case 'xptr':
    // Terminal node appearing under def, etym or orth. Contains a reference to another entry.
    // References are numbered inconsistently, but we'll make a note of it.
    return makeTuple([ 'rep' => '[ref]' ]);

  default:
    return $xml;
  }
}

// Sometimes node contents are mixed with child nodes, like <a>bcd<e>fgh</e>ijk</a>.
// SimpleXML cannot give us the correct order, so we convert the node to DOM. See:
// http://stackoverflow.com/questions/20131226/simplexml-get-element-content-between-child-elements
function getMergedChildren($xml) {
  $result = [];

  $domElement = dom_import_simplexml($xml);
  foreach ($domElement->childNodes as $domChild) {
    switch ($domChild->nodeType) {

    case XML_TEXT_NODE:
      $result[] = $domChild->nodeValue;
      break;

    case XML_ELEMENT_NODE:
      $result[] = collectData(simplexml_import_dom($domChild));
      break;

    default:
      Log::error("unknown node type {$domChild->nodeType}");
      exit;
    }
  }

  return $result;
}

function makeTuple($tuple) {
  return $tuple; // for now
}
