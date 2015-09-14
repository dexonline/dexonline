<?php

require_once __DIR__ . '/../phplib/util.php';

$SPRITE = 'sprite3.png';
$GEOMETRY = '316x113';
$IMAGES = array(
  array('file' => 'wwwbase/img/zepu/logo.png',               'x' => 0, 'y' => 0,  'width' => 316, 'height' => 82, 'bg' => null),
  // array('file' => 'wwwbase/img/hosting/elvsoft.png',         'x' => 0, 'y' => 82,  'width' => 88,  'height' => 31, 'bg' => null),
  array('file' => 'wwwbase/img/zepu/dexonline_logo_mic.png', 'x' => 88, 'y' => 82,  'width' => 111, 'height' => 25, 'bg' => '#eeeeee'),
  array('file' => 'wwwbase/img/icons/user_orange.png',       'x' => 199, 'y' => 82,  'width' => 16,  'height' => 16, 'bg' => '#eeeeee'),
  array('file' => 'wwwbase/img/icons/exclamation.png',       'x' => 215, 'y' => 82,  'width' => 16,  'height' => 16, 'bg' => null),
);

chdir(util_getRootPath());

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
