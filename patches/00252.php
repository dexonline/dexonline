<?php

$projects = Model::factory('AccuracyProject')->find_many();

foreach ($projects as $p) {
  Log::info("Patching project $p");

  $p->computeSpeedData();
  $p->computeAccuracyData();
  $rlen = $p->getReviewedLength();
  $errorCount = $p->getErrorCount();
  $p->errorRate = $rlen ? ($errorCount / $rlen) : 0.0;
  $p->save();
}
