<?php

require_once("../phplib/util.php");

log_scriptLog('Running updateSourceCounts.php.');

foreach (Model::factory('Source')->find_many() as $src) {
  $src->ourDefCount = Model::factory('Definition')->where('sourceId', $src->id)->where('status', ST_ACTIVE)->count();
  $src->updatePercentComplete();
  $src->save();
}

log_scriptLog('updateSourceCounts.php completed.');


?>
