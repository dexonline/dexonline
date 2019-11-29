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
  private static $pendingDefIds = []; // gather all the ID that have been marked for review

  private static $definitions = null;

  private static $sourceName = 'test';

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
    $source = Source::get_by_id($sourceId);
    if(!$source) {
      print "No source with the specified ID!".PHP_EOL;
      die;
    }
    self::$sourceName = $source->urlName;

    return Model::factory('Definition')
    ->where_equal('sourceId', $sourceId)
    ->where_gt('id', self::START_ID)
    ->where_equal('status', Definition::ST_ACTIVE)
    ->order_by_asc('id')
    ->limit(self::BATCH_SIZE)
    ->find_many();

  } // getDefinitionIds()

  public function setModified($defId) {
    self::$modDefIds[] = $defId;
  }

  public function putApart($defId, $type, $message = null, $pending = false) {
    self::$defWarnings[$type][$defId] =  ' => ' . $message;
    if ($pending) { self::$pendingDefIds[] = $defId; }
  } // putApart

  public function displayWarnings($writeFile = false, $dryRun = false) {
    // Summing up what we've gathered
    $count = count(self::$defWarnings, true);
    if ($count) {
      $warnMessage = "A total of $count issue(s) has been found.".PHP_EOL;

      foreach (self::$defWarnings as $keyG => $group) { // $keyG is one of the grouping const
        $warnMessage .= PHP_EOL."[$keyG = " . count($group). "]".PHP_EOL;

        foreach ($group as $keyD => $value) { // $keyD id definitionId

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
    if ($writeFile) { file_put_contents( Config::TEMP_DIR . DIRECTORY_SEPARATOR . self::$sourceName . '.txt', $warnMessage, FILE_APPEND); }

  } // displayWarnings()

  // Almost last step to tag the modified versions of definitions
  public function markReprocessTag() {
    $taggedIds = array_unique(self::$modDefIds);

    $objTag = ObjectTag::TYPE_DEFINITION_VERSION;
    $tagId = Config::TAG_ID_REPROCESS;

    foreach ($taggedIds as $ignored => $defId) {
      $modDef = Model::factory('DefinitionVersion')
        ->where_equal('definitionId', $defId)
        ->order_by_desc('createDate')
        ->find_one();
      ObjectTag::associate($objTag, $modDef->id, $tagId);
    }
    print "A total of " . count($taggedIds) . " versions were tagged with reprocess tag.".PHP_EOL;

  } // associateTags()

  // Final step is to mark definitions for review if option -s was set
  public function markPending() {
    $pendIds = array_unique(self::$pendingDefIds);
    $pend = Definition::ST_PENDING;

    foreach ($pendIds as $ignored => $defId) {
      $pendDef = Definition::get_by_id($defId);
      $pendDef->status = $pend;
      $pendDef->save();
    }
    print "A total of " . count($pendIds) . " definitions were put in pending state.".PHP_EOL;

  } // markPending()

} // end of class
