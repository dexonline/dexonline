<?php
require_once __DIR__ . '/../lib/Core.php';
const SOURCE_ID = 82; // Converting IVO-III

$pi = Model::factory('PageIndex')
    ->where('sourceId', SOURCE_ID)
    ->find_many();

foreach ($pi as $p) {
  $word = Str::unicodeToLatin($p->word);
  Log::info('Converting in PageIndex at id [%d] word [%s] to latin charset as [%s]',
            $p->id, $p->word, $word);
  $p->word = $word;
  $p->save();
}
