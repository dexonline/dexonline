<?php

$projects = Model::factory('AccuracyProject')->find_many();

foreach ($projects as $p) {
  Log::info("Patching project $p");

  $p->computeSpeedData();
  $p->computeErrorRate();
  $p->save();
}
