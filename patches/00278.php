<?php

/* Emulate the model editor for every verb model */

const FORM_MAP = [
  52 => [
    'rank' => 5,
    'longId' => 106,
    'name' => 'participiu',
  ],
  53 => [
    'rank' => 7,
    'longId' => 107,
    'name' => 'gerunziu',
  ],
];

$verbModels = Model::factory('FlexModel')
  ->where('modelType', 'V')
  //  ->where('number', '319')
  ->order_by_expr('cast(number as unsigned)')
  ->find_many();

foreach ($verbModels as $m) {
  Log::info('updating model V%s', $m->number);
  $lexeme = Lexeme::create($m->exponent, $m->modelType, $m->number);
  $ifMap = $lexeme->generateInflectedFormMap();

  foreach (FORM_MAP as $shortId => $rec) {
    $forms = $ifMap[$rec['rank']] ?? [];
    foreach ($forms as $variant => $shortForm) {
      $long = $shortForm->form . 'u';
      Log::info('  %s variant %d: %s => %s', $rec['name'], $variant, $shortForm->form, $long);

      // extract and save the transforms
      $transforms = FlexStr::extractTransforms($m->exponent, $long, false);
      if ($transforms === null) {
        Log::fatal('Nu pot extrage transformările între %s și %s.', $m->exponent, $long);
        exit(1);
      }

      $accentShift = array_pop($transforms);
      if ($accentShift != ModelDescription::UNKNOWN_ACCENT_SHIFT &&
          $accentShift != ModelDescription::NO_ACCENT_SHIFT) {
        $accentedVowel = array_pop($transforms);
      } else {
        $accentedVowel = '';
      }

      $order = count($transforms);
      foreach ($transforms as $t) {
        // Make sure the transform has an ID.
        $t = Transform::createOrLoad($t->transfFrom, $t->transfTo);
        $md = Model::factory('ModelDescription')->create();
        $md->modelId = $m->id;
        $md->inflectionId = $rec['longId'];
        $md->variant = $variant;
        $md->applOrder = --$order;
        $md->recommended = true;
        $md->hasApocope = false;
        $md->transformId = $t->id;
        $md->accentShift = $accentShift;
        $md->vowel = $accentedVowel;
        $md->save();
      }
    }
  }
}
