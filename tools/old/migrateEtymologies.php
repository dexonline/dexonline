<?php

/**
 * Replace plain-text etymologies with labels where possible.
 **/

require_once __DIR__ . '/../phplib/util.php';

ensureTag('Cf.');
ensureTag('după');
ensureTag('expresie latină');
ensureTag('formație onomatopeică');
ensureTag('locuțiune latină');
ensureTag('necunoscută');
ensureTag('onomatopee');
ensureTag('(provine) din');
ensureTag('vezi');

$meanings = Model::factory('Meaning')
          ->where('type', Meaning::TYPE_ETYMOLOGY)
          ->order_by_asc('id')
          ->find_many();

foreach ($meanings as $m) {
  $matches = [];
  $tags = [];
  $fixed = true;
  $skip = false;

  if (preg_match('/^Vezi (@[^@]+@)$/',
                 $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 5);
    $tags[] = 'vezi';
  } else if (preg_match('/^din (@[^@]+@) \+ sufix (\$-[^$]+)\$$/i',
                        $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 4);
    $tags[] = '(provine) din';
  } else if (preg_match('/^(@[^@]+@) \+ sufix (\$-[^$]+)\$$/',
                        $m->internalRep, $matches)) {
    $tags[] = '(provine) din';
  } else if (preg_match('/^din (@[^@]+@) \(n\. pr\.\) \+ sufix (\$-[^$]+)\$$/i',
                        $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 4);
    $tags[] = '(provine) din';
  } else if (preg_match('/^(@[^@]+@) \(n\. pr\.\) \+ sufix (\$-[^$]+)\$$/',
                        $m->internalRep, $matches)) {
    $tags[] = '(provine) din';
  } else if (preg_match('/^Expresie( din limba)? latină.$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = '';
    $tags[] = 'expresie latină';
  } else if (preg_match('/^Locuțiune latină.$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = '';
    $tags[] = 'locuțiune latină';
  } else if (preg_match('/^Onomatopee.$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = '';
    $tags[] = 'onomatopee';
  } else if (preg_match('/^Formație onomatopeică.$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = '';
    $tags[] = 'formație onomatopeică';
  } else if (preg_match('/^(Necunoscută|Etimologie necunoscută).$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = '';
    $tags[] = 'necunoscută';
  } else if (preg_match('/^din (@[^@]+@)$/i',
                        $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 4);
    $tags[] = '(provine) din';
  } else if (preg_match('/^de la (@[^@]+@)$/i',
                        $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 6);
    $tags[] = '(provine) din';
  } else if (preg_match('/^Cf\. (@[^@]+@)$/',
                        $m->internalRep, $matches)) {
    $m->internalRep = substr($m->internalRep, 4);
    $tags[] = 'Cf.';
  } else if (preg_match('/^@[^@]+@$/',
                        $m->internalRep, $matches)) {
    /* nothing */
    $skip = true;
  } else if (preg_match('/^$/',
                        $m->internalRep, $matches)) {
    /* nothing */
    $skip = true;
  } else if (preg_match('/^(Din|După|Cf\.) limba ([^,@]+) (@[^@]+@)( "[^"]+".)?$/i',
                        $m->internalRep, $matches)) {
    switch($matches[1]) {
      case 'din':
      case 'Din':
        $tags[] = '(provine) din'; break;
      case 'după':
      case 'După':
        $tags[] = 'după'; break;
      default:
        $tags[] = 'Cf.'; break;
    }

    $t = getLanguageTag($matches[2]);
    if ($t) {
      $tags[] = $t->value;
      $m->internalRep = $matches[3];
    } else {
      $fixed = false;
    }

  } else if (preg_match('/^Din limba ([^,@]+) (@[^@]+@) limba ([^,@]+) (@[^@]+@)$/',
                        $m->internalRep, $matches)) {
    if (splitMeaning($m, $matches[1], $matches[2], $matches[3], $matches[4])) {
      $skip = true;
    } else {
      $fixed = false;
    }
  } else if (preg_match('/^Din limba ([^,@]+),( limba)? ([^,@]+) (@[^@]+@)$/',
                        $m->internalRep, $matches)) {
    if (splitMeaning($m, $matches[1], $matches[4], $matches[3], $matches[4])) {
      $skip = true;
    } else {
      $fixed = false;
    }
  } else {
    $fixed = false;
  }

  $t = $m->getTree();

  if ($fixed && !$skip) {
    print "[{$t->description}] [{$m->displayOrder}] [{$m->internalRep}]\n";

    // associate tags
    foreach ($tags as $value) {
      print "* [{$value}]\n";
      $tag = Tag::get_by_value($value);
      ObjectTag::associate(ObjectTag::TYPE_MEANING, $m->id, $tag->id);
    }

    $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
    $m->save();

    Log::info("migrated [{$t->description}] [{$m->displayOrder}] [{$m->internalRep}]");
  } else if (!$skip) {
    Log::info("cannot parse tree [{$t->description}] https://dexonline.ro/editTree.php?id={$t->id}");
  }
}

/*************************************************************************/

function ensureTag($value) {
  $t = Tag::get_by_value($value);
  if (!$t) {
    $numRoots = model::factory('Tag')
              ->where('parentId', 0)
              ->count();
    $t = Model::factory('Tag')->create();
    $t->parentId = 0;
    $t->displayOrder = $numRoots + 1;
    $t->value = $value;
    $t->save();
    Log::info("created tag {$value}");
  }
}

function getLanguageTag($lang) {
  $t = Tag::get_by_value("limba {$lang}");
  if (!$t) {
    Log::error("No tag [{$lang}]\n");
  }
  return $t;
}

// returns false on failure
function splitMeaning($m, $lang1, $rep1, $lang2, $rep2) {
  $t1 = getLanguageTag($lang1);
  $t2 = getLanguageTag($lang2);

  if (!$t1 || !$t2) {
    return false;
  }

  // remove comma
  $rep1 = preg_replace('/,@$/', '.@', $rep1);

  $tree = $m->getTree();
  print "[{$tree->description}] [{$m->displayOrder}] [{$m->internalRep}]\n";
  print "* [{$t1->value}]\n";

  $m->internalRep = $rep1;
  $m->htmlRep = AdminStringUtil::htmlize($m->internalRep, 0);
  $m->save();
  ObjectTag::associate(ObjectTag::TYPE_MEANING, $m->id, $t1->id);

  // do not set the treeId field yet
  $m2 = Model::factory('Meaning')->create();
  $m2->parentId = $m->parentId;
  $m2->type = Meaning::TYPE_ETYMOLOGY;
  $m2->userId = $m->userId;
  $m2->internalRep = $rep2;
  $m2->htmlRep = AdminStringUtil::htmlize($m2->internalRep, 0);
  $m2->save();
  ObjectTag::associate(ObjectTag::TYPE_MEANING, $m2->id, $t2->id);

  print "* * [{$tree->description}] [...] [{$m2->internalRep}]\n";
  print "* * [{$t2->value}]\n";

  // insert $m2 among $tree's
  $meanings = Model::factory('Meaning')
            ->where('treeId', $tree->id)
            ->order_by_asc('displayOrder')
            ->find_many();
  $merged = [];
  $m2->treeId = $m->treeId; // now set the treeId field
  foreach ($meanings as $meaning) {
    $merged[] = $meaning;
    if ($meaning->id == $m->id) {
      $merged[] = $m2;
    }
  }

  Meaning::renumber($merged);

  foreach ($merged as $meaning) {
    $meaning->save();
  }

  Log::info("migrated and split [{$tree->description}] [{$m->displayOrder}] [{$m->internalRep}]");
  return true;
}
