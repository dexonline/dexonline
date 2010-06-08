<?
require_once('../phplib/util.php');

$INFL_LONG_INF = 50;
$INFL_PART = 52;
$LEXEM_EDIT_URL = 'http://dexonline.ro/admin/lexemEdit.php?lexemId=';

$dbResult = db_execute("select * from Lexem where modelType in ('V', 'VT') and isLoc order by formNoAccent");
while (!$dbResult->EOF) {
  $verb = new Lexem();
  $verb->set($dbResult->fields);
  $dbResult->MoveNext();

  $ifs = db_find(new InflectedForm(), "lexemId = {$verb->id} and inflectionId = $INFL_LONG_INF");
  assert(count($ifs) <= 1);
  if (count($ifs) == 1) {
    $longInfForm = $ifs[0]->formNoAccent;
    $longInfModelNumber = text_endsWith($longInfForm, 'are') ? '113' : '107';
    $lexems = db_find(new Lexem(), "formNoAccent = '{$longInfForm}' and modelType = 'F' and modelNumber = '{$longInfModelNumber}'");
    if (count($lexems) != 1) {
      print "I {$longInfForm} are " . count($lexems) . " lexeme corespunzătoare\n";
    }
    foreach ($lexems as $longInf) {
      if (!$longInf->isLoc) {
        print "I {$longInf->formNoAccent} nu este în LOC {$LEXEM_EDIT_URL}{$longInf->id}\n";
        $longInf->isLoc = 1;
        $longInf->save();
      }
    }
  }

  if ($verb->modelType == 'VT') {
    $ifs = db_find(new InflectedForm(), "lexemId = {$verb->id} and inflectionId = $INFL_PART");
    $pm = ParticipleModel::loadByVerbModel($verb->modelNumber);
    assert($pm);
    foreach ($ifs as $if) {
      $partForm = $if->formNoAccent;
      $lexems = db_find(new Lexem(), "formNoAccent = '{$partForm}' and modelType = 'A' and modelNumber = '{$pm->adjectiveModel}'");
      if (count($lexems) != 1) {
        print "P {$partForm} are " . count($lexems) . " lexeme corespunzătoare\n";
      }
      foreach ($lexems as $part) {
        if (!$part->isLoc) {
          print "P {$part->formNoAccent} nu este în LOC {$LEXEM_EDIT_URL}{$part->id}\n";
          $part->isLoc = 1;
          $part->save();
        }
      }
    }
  }
}

?>
