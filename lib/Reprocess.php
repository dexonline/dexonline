<?php

class Reprocess {
  const START_ID = 0;
  const BATCH_SIZE = 10000;
  const MODULO_CHECK = 500;

  const DEF_URL = "https://dexonline.ro/editare-definitie/";

  // grouping defs
  const WARN_STRESS = 'unstressed lexicons';
  const WARN_HTMLIZE = 'htmlize errors';
  const WARN_AMBIGUOUS = 'ambiguous abbreviations';
  const MIXED_ALPH = 'words with mixed alphabets';
  const CONVERTED = 'converted strings';
  const UNCONVERTED = 'unconverted definitions';
  const MODULO_DEMO = 'demo definitions';

  private static $defWarnings = []; // here we gather the issues encountered throughout the reprocess

  private static $modDefIds = []; // gather all the ID that have been modified for tagging the version

  private static $definitions = null;

  public function readOptions($x, $apostrophes = true) {

    print "There was a problem reading the arguments.".PHP_EOL;
    print "Options are: -a (mark ambiguities).".PHP_EOL;
    print "             -n (dry run).".PHP_EOL;
    print "             -w (write warnings to file).".PHP_EOL;
    print "             -s (change status to temporary).".PHP_EOL;
    print "Are you sure you want to run without options set?  Type 'y' to continue: ";

    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    fclose($handle);

    if(trim($line) != 'y'){
      print "ABORTING!".PHP_EOL;
      exit;
    }

    print "".PHP_EOL;
    print "OK!, continuing...".PHP_EOL;
    if ($dryRun) {
      print "---- DRY RUN ----".PHP_EOL;
    }

  } // readOptions()

  public function getDefinitionIds($sourceId) {
    return Model::factory('Definition')
    ->where_equal('sourceId', $sourceId)
    ->where_gt('id', self::START_ID)
    ->where_equal('status', Definition::ST_ACTIVE)
    ->order_by_asc('id')
    ->limit(self::BATCH_SIZE)
    ->find_many();

  } // getDefinitionIds()

  public function saveDefinition() {
    $saved = $def->save();
    if ($saved) {
      $modDef = DefinitionVersion::get_by_definitionId($def->id)
      ->order_by('createDate', 'desc')
      ->find_one();
      ObjectTag::associate(ObjectTag::TYPE_DEFINITION_VERSION, $modDef->id, Config::TAG_ID_REPROCESS);
    } //if ($saved)

  } // saveDefinition()

  public function putApart($defId, $type, $message = null) {
    self::$defWarnings[$type][$defId] =  ' => ' . $message;
  } // putApart

  public function displayWarnings($writeFile = false, $dryRun = false) {
    // Summing up what we've gathered
    $count = count(self::$defWarnings, true);
    if ($count) {
      $warnMessage = "A total of $count issue(s) has been found.".PHP_EOL;

      foreach (self::$defWarnings as $keyG => $group) { // $keyG is one of the grouping const
        $warnMessage .= PHP_EOL."[$keyG = " . count($group). "]".PHP_EOL;

        foreach ($group as $keyD => $value) { // $keyD id definitionId
          $modDefIds[] = $keyD;
          if (is_array($value)) { // must be ambiguousMatches or htmlizeWarnings

            foreach ($value as $match) {
              $warnMessage .= "[should review - $keyG] [{$match['abbrev']}]@{$match['position']}".PHP_EOL;
            }

          } else {
            $warnMessage .= "[should review - $keyG] (" . self::DEF_URL . "$keyD)" . $value .PHP_EOL;
          }

        }

      }

    } //if ($count)

    if ($dryRun) { echo $warnMessage; }
    if ($writeFile) { file_put_contents( Config::TEMP_DIR . DIRECTORY_SEPARATOR . 'delrie.txt', $warnMessage, FILE_APPEND); }

  } // displayWarnings()

  // Final step is to tag the modified versions of the definition
  public function associateTags() {
    $taggedIds = array_unique(self::$modDefIds);

    foreach ($taggedIds as $ignored => $defId) {
      $modDef = DefinitionVersion::get_by_definitionId($defId)
      ->order_by('createDate', 'desc')
      ->find_one();
      ObjectTag::associate(ObjectTag::TYPE_DEFINITION_VERSION, $modDef->id, Config::TAG_ID_REPROCESS);
    }

  } // associateTags()

} // end of class
