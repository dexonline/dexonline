<?php

require_once("../phplib/util.php");

$SPRITE = 'sprite2.png';
$GEOMETRY = '875x110';
$IMAGES = array(
  array('file' => '../wwwbase/img/zepu/shadows/shadow875.png',  'x' => 0,   'y' => 0,   'width' => 875, 'height' => 22, 'bg' => null),
  array('file' => '../wwwbase/img/zepu/shadows/shadow538.png',  'x' => 0,   'y' => 25,  'width' => 538, 'height' => 21, 'bg' => null),
  array('file' => '../wwwbase/img/zepu/shadows/shadow350.png',  'x' => 0,   'y' => 50,  'width' => 350, 'height' => 16, 'bg' => null),
  array('file' => '../wwwbase/img/zepu/shadows/shadow216.png',  'x' => 0,   'y' => 70,  'width' => 216, 'height' => 14, 'bg' => null),
  array('file' => '../wwwbase/img/zepu/logo.png',               'x' => 550, 'y' => 25,  'width' => 316, 'height' => 82, 'bg' => null),
  array('file' => '../wwwbase/img/zepu/dexonline_logo_mic.png', 'x' => 220, 'y' => 70,  'width' => 111, 'height' => 25, 'bg' => '#eeeeee'),
  array('file' => '../wwwbase/img/hosting/elvsoft.png',         'x' => 460, 'y' => 50,  'width' => 88,  'height' => 31, 'bg' => null),
  array('file' => '../wwwbase/img/icons/user_orange.png',       'x' => 355, 'y' => 50,  'width' => 16,  'height' => 16, 'bg' => '#eeeeee'),
  array('file' => '../wwwbase/img/icons/exclamation.png',       'x' => 370, 'y' => 50,  'width' => 16,  'height' => 16, 'bg' => null),
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
    OS::errorAndExit("Unknown flag: $arg");
  }
}

if ($merge === $split) {
  OS::errorAndExit("Please specify exactly one of --merge or --split");
}

if ($merge) {
  OS::executeAndAssert("convert -size $GEOMETRY xc:white $SPRITE");
  foreach ($IMAGES as $i) {
    if ($i['bg']) {
      OS::executeAndAssert(sprintf("convert $SPRITE -fill '%s' -draw 'rectangle %s,%s %s,%s' $SPRITE",
				   $i['bg'], $i['x'], $i['y'], $i['x'] + $i['width'] -1, $i['y'] + $i['height'] - 1));
    }
    OS::executeAndAssert("convert $SPRITE {$i['file']} -geometry +{$i['x']}+{$i['y']} -compose over -composite $SPRITE");
  }
  OS::executeAndAssert("optipng $SPRITE");
  print "Composed and optimized sprite in $SPRITE\n";
} else {
  print "Splitting is not implemented yet.\n";
}

?>
