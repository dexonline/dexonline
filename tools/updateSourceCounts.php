<?php

require_once __DIR__ . '/../phplib/Core.php';

Log::notice('started');

foreach (Model::factory('Source')->find_many() as $src) {
  $src->ourDefCount = Model::factory('Definition')->where('sourceId', $src->id)->where('status', Definition::ST_ACTIVE)->count();
  $src->updatePercentComplete();
  $src->save();
}

Log::notice('finished');

?>
