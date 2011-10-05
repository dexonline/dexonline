<?php

require_once("../phplib/util.php");

$SPRITE = 'sprite.png';
$GEOMETRY = '875x175';
$IMAGES = array(
  array('file' => '../wwwbase/img/zepu/menuBG.png', 'x' => 0, 'y' => 20),
  array('file' => '../wwwbase/img/zepu/footerBG.png', 'x' => 0, 'y' => 35),
  array('file' => '../wwwbase/img/zepu/logo.png', 'x' => 0, 'y' => 90, 'width' => 316, 'height' => 82),
  array('file' => '../wwwbase/img/zepu/dexonline_logo_mic.png', 'x' => 320, 'y' => 90, 'width' => 111, 'height' => 25),
  array('file' => '../wwwbase/img/zepu/cauta.png', 'x' => 320, 'y' => 120, 'width' => 76, 'height' => 40),
  array('file' => '../wwwbase/img/hosting/sei.png', 'x' => 435, 'y' => 90, 'width' => 82, 'height' => 72),
  array('file' => '../wwwbase/img/hosting/elvsoft.png', 'x' => 520, 'y' => 90, 'width' => 88, 'height' => 31),
  array('file' => '../wwwbase/img/icons/user_orange.png', 'x' => 530, 'y' => 125, 'width' => 16, 'height' => 16),
  array('file' => '../wwwbase/img/icons/exclamation.png', 'x' => 530, 'y' => 151, 'width' => 16, 'height' => 16),
);

// Expect --merge or --split
$merge = false;
$split = false;

foreach ($argv as $i => $arg) {
  if ($arg == "--merge") {
    $merge = true;
  } else if ($arg == '--split') {
    $split = true;
  } else if ($i) {
    os_errorAndExit("Unknown flag: $arg");
  }
}

if ($merge === $split) {
  os_errorAndExit("Please specify exactly one of --merge or --split");
}

if ($merge) {
  os_executeAndAssert("convert -size $GEOMETRY xc:white $SPRITE");
  foreach ($IMAGES as $i) {
    os_executeAndAssert("convert $SPRITE {$i['file']} -geometry +{$i['x']}+{$i['y']} -compose over -composite $SPRITE");
  }
  os_executeAndAssert("optipng $SPRITE");
  print "Composed and optimized sprite in $SPRITE\n";
} else {
  print "Splitting is not implemented yet.\n";
}

?>
