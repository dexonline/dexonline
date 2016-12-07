<?php

/**
 * Structure definitions from DEX '98.
 **/

require_once __DIR__ . '/../phplib/util.php';

define('SOURCE_ID', 1);
define('MY_USER_ID', 1);
define('J_ONLY', true); // for debugging purposes, process only the letter J

if (count($argv) != 2) {
  die("Usage: {$argv[0]} <XML_directory>\n");
}

$dir = $argv[1];
$files = @scandir($dir) or die("Could not get directory contents.\n");

foreach ($files as $file) {
  if (StringUtil::endsWith($file, '.xml') &&
      (!J_ONLY || ($file == 'j.xml'))) {
    $fullName = realpath("$dir/$file");
    Log::info("Reading file $fullName");
    $s = file_get_contents($fullName);
    $s = preprocessXmlString($s);
    $xml = new SimpleXMLElement($s);

    foreach ($xml->body->entry as $e) {
      processEntry($e);
    }
  }
}

/*************************************************************************/

function preprocessXmlString($s) {
  $s = str_replace('iso-entities\\', 'iso-entities/', $s);

  // doubly encode < signs so that they'll still be encoded after html_entity_decode
  $s = str_replace('&lt;', '&amp;lt;', $s);
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

  $s = html_entity_decode($s, ENT_HTML5, 'utf-8');
  return $s;
}


// $xml is a SimpleXML <entry>
function processEntry($xml) {
  // pick a form to search for
  $hw = mb_strtolower((string)$xml->hw); // hw is never empty
  $orth = ($xml->orth) ? mb_strtolower((string)$xml->orth) : '';
  $stress = ($xml->stress) ? mb_strtolower((string)$xml->stress) : '';
  $stress = str_replace('`', "'", $stress);

  if ($stress) {
    $field = 'form';
    $value = $stress;
  } else {
    $field = 'formNoAccent';
    $value = $orth ? $orth : $hw;
  }

  // load matching lexems
  $lexems = Model::factory('Lexem')
          ->where($field, $value)
          ->find_many();
  if (empty($lexems)) {
    Log::error("No lexems for {$value}");
  }
}
