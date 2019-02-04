<?php

/**
 * Verify [verb], [group] and [conjugation] tags of all verbs, report
 * incorrect tags and add missing tags.
 **/

require_once __DIR__ . '/../lib/Core.php';

ini_set('memory_limit', '512M');

$opts = getopt('n');
$dryRun = isset($opts['n']);

Log::info('start');

$groupMap = loadTags('grupa', 4);
$conjMap = loadTags('conjugarea', 11);
$verbTag = Tag::get_by_value('verb');

$groupIds = Util::objectProperty($groupMap, 'id');
$conjIds = Util::objectProperty($conjMap, 'id');

$verbs = Model::factory('Lexeme')
  ->where_in('modelType', [ 'V', 'VT' ])
  ->order_by_asc('formNoAccent')
  ->find_many();

foreach ($verbs as $v) {
  list ($group, $conj) = getExpectedGroupAndConjugation($v, $groupMap, $conjMap);
  if (!$group || !$conj) {
    // skip it
    Log::warning('Nu pot deduce grupa și conjugarea verbului %s', $v);
  } else {

    // collect existing group and conjugation tags
    $groupTags = [];
    $conjTags = [];
    $hasVerbTag = false;
    foreach ($v->getTags() as $tag) {
      if (in_array($tag->id, $groupIds)) {
        $groupTags[] = $tag;
      } else if (in_array($tag->id, $conjIds)) {
        $conjTags[] = $tag;
      } else if ($tag->id == $verbTag->id) {
        $hasVerbTag = true;
      }
    }

    if (!$hasVerbTag) {
      Log::warning('Adaug [verb] la verbul %s', $v);
      if (!$dryRun) {
        ObjectTag::associate(ObjectTag::TYPE_LEXEME, $v->id, $verbTag->id);
      }
    }

    examineTags($v, $group, $groupTags, $groupMap);
    examineTags($v, $conj, $conjTags, $conjMap);
  }

}

Log::info('stop, %d verbe examinate', count($verbs));

/*************************************************************************/

function loadTags($name, $count) {
  $map = [];

  for ($i = 1; $i <= $count; $i++) {
    $format = ($i == 1) ? '%s %s' : '%s a %s-a';
    $tagValue = sprintf($format, $name, Str::arabicToRoman($i));
    $tag = Tag::get_by_value($tagValue);
    assert($tag);
    $map[$i] = $tag;
  }

  return $map;
}

function examineTags($v, $expected, $existing, $map) {
  global $dryRun;

  if (count($existing) > 1) {
    Log::error('EROARE: Verbul %s are etichete multiple: %s',
               $v, implode(', ', Util::objectProperty($existing, 'value')));
  } else if (!count($existing)) {
    Log::warning('Adaug [%s] la verbul %s', $map[$expected]->value, $v);
    if (!$dryRun) {
      ObjectTag::associate(ObjectTag::TYPE_LEXEME, $v->id, $map[$expected]->id);
    }
  } else if ($existing[0]->id != $map[$expected]->id) {
    Log::error('EROARE: [%s] în loc de [%s] la verbul %s',
               $existing[0]->value, $map[$expected]->value, $v);
  }
}

function loadForm($lexeme, $inflectionId) {
  return InflectedForm::get_by_lexemeId_inflectionId_variant_apheresis(
    $lexeme->id, $inflectionId, 0, 0);
}

function getExpectedGroupAndConjugation($v, &$groupMap, &$conjMap) {
  // some of these could be null
  $form1sg = loadForm($v, 54);
  $form3sg = loadForm($v, 56);
  $form1pl = loadForm($v, 57);
  $form3pl = loadForm($v, 59);
  $formPart = loadForm($v, 52);
  $formPs1sg = loadForm($v, 72);

  $last = mb_substr($v->formNoAccent, -1);
  $lastPart = $formPart ? mb_substr($formPart->formNoAccent, -1) : null;

  if ($last == 'a' &&
      $form3sg &&
      Str::endsWith($form3sg->formNoAccent, 'ază') &&
      // treat cases like boteza, așeza which have the short conjugation
      mb_strlen($form3sg->formNoAccent) - mb_strlen($v->formNoAccent) >= 2) {
    return [ 1, 2 ];

  } else if (Str::endsWith($v->formNoAccent, 'ea') &&
             $form1sg && $form3pl &&
             $form1sg->form == $form3pl->form) {
    return [ 2, 8 ];

  } else if ($last == 'a' &&
             $form3sg && $form3pl &&
             $form3sg->form == $form3pl->form) { // short conjugation - ara, afla, spăla
    return [ 1, 1 ];

  } else if ($last == 'î' &&
             $form3sg && $form3pl &&
             $form3sg->form == $form3pl->form) {
    return [ 4, 3 ];

  } else if ($last == 'î' &&
             $form3sg &&
             Str::endsWith($form3sg->formNoAccent, 'ăște')) {
    return [ 4, 7 ];

  } else if ($last == 'i' &&
             $form3sg && $form3pl &&
             $form3sg->form == $form3pl->form) {
    return [ 4, 4 ];

  } else if ($last == 'i' &&
             $form3sg &&
             Str::endsWith($form3sg->formNoAccent, 'ește')) {
    return [ 4, 6 ];

  } else if ($last == 'i' &&
             $form1sg && $form3pl &&
             $form1sg->form == $form3pl->form) { // short conjugation - adormi, fugi, sări
    return [ 4, 5 ];

  } else if ($last == 'e' &&
             $lastPart == 's') {
    return [ 3, 10 ];

  } else if ($last == 'e' &&
             $lastPart == 't' &&
             $formPs1sg &&
             Str::endsWith($formPs1sg->formNoAccent, 'sei')) {
    return [ 3, 11 ];

  } else if ($last == 'e' &&
             $lastPart == 't') {
    return [ 3, 9 ];

  }

  // try to copy the group and conjugation from the model's exponent
  $model = FlexModel::get_by_modelType_number('V', $v->modelNumber);
  $exp = $model->getExponentWithParadigm();
  $tags = $exp->getTags();
  $group = 0;
  $conj = 0;

  foreach ($exp->getTags() as $tag) {
    if (!$group) {
      $group = array_search($tag, $groupMap) ?? 0;
    }
    if (!$conj) {
      $conj = array_search($tag, $conjMap) ?? 0;
    }
  }

  if ($group || $conj) {
    Log::warning('Copiez grupa %d conjugarea %d de la exponentul lui %s (%s)',
                 $group, $conj, $v, $exp);
  }

  return [ $group, $conj ];
}
