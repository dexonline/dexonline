<?php
require_once("../../phplib/util.php");
ini_set('max_execution_time', '3600');
ini_set("memory_limit", "512000000");

$knownBadLexems = array(30181 => "marghiol",
                        36123 => "orb",
                        45412 => "roib",
                        45561 => "rotocol",
                        45569 => "rotogol",
                        34515 => "neșters",
                        34102 => "neîntors",
                        15616 => "oare",
                        13173 => "cumătră",
                        3064 => "argăseală",
                        3284 => "aromeală",
                        //                        20819 => "gargariseală",
                        30143 => "mardeală",
                        30182 => "marghioleală",
                        37613 => "paraponiseală",
                        37711 => "pardoseală",
                        36970 => "palavrageală",
                        18650 => "fâșâialășâia",
                        32004 => "mițuialățuia",
                        32715 => "moțăiala",
                        33 => "aberoscop", // And most others from N11 (342)
                        11790 => "convector", // And most others from N12 (343)
                        39093 => "piciorong",
                        58272 => "al",
                        58283 => "ăst",
                        58284 => "ăsta",
                        9529 => "cinevași",
                        9530 => "cinevașilea",
                        58293 => "câtelea",
                        58304 => "dumneata",
                        58310 => "ist",
                        58311 => "ista",
                        27125 => "istalalt",
                        58312 => "istalalt",
                        58315 => "însuși",
                        58338 => "totul",
                        58342 => "un",
                        );

$models = Model::loadAll();
$tempModel = Model::loadTemporary();

foreach ($models as $model) {
  if ($model->modelType != 'MF' && $model->modelType != 'VT'
      && $model->id != $tempModel->id && $model->id==130) {
    print "Testing model " . $model->getName() . " (id = " . $model->id .
      ")\n";
    $lexems = Lexem::loadByModelId($model->id);
    $transfMap = array();

    foreach ($lexems as $lexem) {
      if (array_key_exists($lexem->id, $knownBadLexems)) {
        print "  Skipping known bad lexem " . $lexem->id . " (" .
          $lexem->unaccented . ")\n";
        continue;
      }
      if (!$lexem->isLoc) {
        continue;
      }
      $wls = WordList::loadByLexemId($lexem->id);
      $ignore = false;
      for ($i = 0; $i < count($wls) && !$ignore; $i++) {
        $ignore = ord($wls[$i]->form) == 0 ||
          text_contains($wls[$i]->form, "'");
      }
      if ($ignore) {
        print "  Ignoring lexem " . $lexem->id . " (" . $lexem->unaccented .
          "): paradigm contains accents or null characters\n";
      }
      if (!$ignore) {
        if (!count($transfMap)) {
          // Create model_descriptions by comparing the first lexem to its
          // existing wordlists.
          print "  Using lexem '" . $lexem->unaccented . "' as exponent\n";
          foreach ($wls as $wl) {
            $transforms = text_extractTransforms($lexem->unaccented,
                                                 $wl->form);
            $transfMap[$wl->inflectionId] = $transforms;
          }
          // Dump the transformation table
          print "  Transforms:\n";
          foreach ($transfMap as $inflId => $transforms) {
            print "    $inflId:";
            foreach ($transforms as $t) {
              print " [" . $t->from . ']->[' . $t->to . ']';
            }
            print "\n";
          }
        }

        // Now generate the paradigm for the lexem and compare it to the
        // existing wordlists.
        foreach ($wls as $wl) {
          if (!$wl->inflectionId) {
            var_dump($wl);
          }
          $transforms = $transfMap[$wl->inflectionId];
          $new = applyTransforms($lexem->unaccented, $transforms);
          if ($new != $wl->form) {
            print "  Lexem " . $lexem->id . " (" . $lexem->unaccented . "), " .
              "inflection " . $wl->inflectionId . " failed: expected [" .
              $wl->form . "], got [$new]\n";
            exit;
          }
        }
      }
    }
  }
}

function applyTransforms($s, $transforms) {
  $copy = $s;
  $positions = array();
  for ($i = count($transforms) - 1; $i >= 0; $i--) {
    $t = $transforms[$i];
    $pos = ($t->from == '-') ? mb_strlen($copy) : mb_strrpos($copy, $t->from);
    $positions[$i] = $pos;
    $copy = mb_substr($copy, 0, $pos);
  }

  $result = '';
  for ($i = count($transforms) - 1; $i >= 0; $i--) {
    $t = $transforms[$i];
    $pos = $positions[$i];

    $len = mb_strlen($s);
    $afterFrom = $pos + mb_strlen($t->from);
    if ($len > $afterFrom) {
      $result = mb_substr($s, $afterFrom) . $result;
    }
    $result = $t->to . $result;
    $s = mb_substr($s, 0, $pos);
  }
  $result = $s . $result;
  return $result;
}

// function applyTransforms($s, $transforms) {
//   $position = 0;
//   for ($i = count($transforms) - 1; $i >= 0; $i--) {
//     $t = $transforms[$i];
    
//     // Replace the last occurrence of $t->from with $t->to. We can only
//     // move forward from the previous position.
//     if ($t->from == '-') {
//       $s = $s . $t->to;
//       $position = mb_strlen($s);
//     } else {
//       $loc = $s ? $loc = mb_strrpos($s, $t->from) : FALSE;
//       if ($loc === FALSE || $loc < $position) {
//         // Prevent any other replacements;
//         $position = mb_strlen($s);
//       } else {
//         $s = mb_substr($s, 0, $loc) .
//           $t->to .
//           mb_substr($s, $loc + mb_strlen($t->from));
//         $position = $loc + mb_strlen($t->to);
//       }
//     }
//   }
//   return $s;
// }

?>
