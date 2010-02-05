<?
require_once("../../phplib/util.php");
assert_options(ASSERT_BAIL, 1);
debug_off();

$dbResult = mysql_query('select * from model_mappings');
$mms = ModelMapping::populateFromDbResult($dbResult);

foreach ($mms as $mm) {
  print "Creating {$mm->modelType}{$mm->slaveNumber} from " .
    "{$mm->modelType}{$mm->masterNumber}\n";
  $master = Model::loadByTypeNumber($mm->modelType, $mm->masterNumber);

  // Create the model
  $slave = Model::create($mm->modelType, $mm->slaveNumber,
                         "Derivat din {$mm->modelType}{$mm->masterNumber}");
  $slave->save();
  $slave->id = db_getLastInsertedId();

  // Clone the model descriptions
  $mds = ModelDescription::loadByModelId($master->id);
  foreach ($mds as $md) {
    $md->id = 0;
    $md->modelId = $slave->id;
    $md->save();
  }

  // Clone the participle model
  if ($mm->modelType == 'V') {
    $pm = ParticipleModel::loadByVerbModel($mm->masterNumber);
    $clonePm = ParticipleModel::create($mm->slaveNumber, $pm->participleModel);
    $clonePm->save();
  }

  // Delete the mapping
  mysql_query("delete from model_mappings where model_type = " .
              " '{$mm->modelType}' and slave_no = '{$mm->slaveNumber}'");

  // Regenerate the lexems. In theory the paradigm won't change, but we want
  // to actually see it.
  $lexems = Lexem::loadByCanonicalModel($mm->modelType, $mm->slaveNumber);
  foreach ($lexems as $l) {
    print "\tRegenerating paradigm for {$l->form} ({$l->modelType}" .
      "{$l->modelNumber})\n";
    $l->regenerateParadigm();
  }
}

mysql_query('drop table model_mappings');

?>
