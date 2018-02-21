<?php

$projects = Model::factory('AccuracyProject')->find_many();

foreach ($projects as $p) {
  Log::info("Patching project $p");

  $p->computeSpeedData();
  $p->computeAccuracyData();
  $evalLength = $p->getEvalLength();
  $errorCount = $p->getErrorCount();
  $p->errorRate = $evalLength ? ($errorCount / $evalLength) : 0.0;
  $p->save();
}
