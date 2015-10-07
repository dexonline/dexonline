<?php

require_once __DIR__ . '/../phplib/util.php';

log_scriptLog('Running updateSourceCounts.php.');

foreach (Model::factory('Source')->find_many() as $src) {
  $src->ourDefCount = Model::factory('Definition')->where('sourceId', $src->id)->where('status', Definition::ST_ACTIVE)->count();
  $src->updatePercentComplete();
  $src->save();
}

log_scriptLog('updateSourceCounts.php completed.');


?>
