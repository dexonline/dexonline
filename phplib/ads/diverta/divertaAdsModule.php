<?php
class DivertaAdsModule extends AdsModule {

  function run($lexemes, $definitions) {
    if (empty($lexemes) && empty($definitions)) {
      // If we are called from a page with no keywords, display one of the top 10 highest CTR books.
      $random = rand(0, 9);
      // TODO: Label books as mature
      $book = Model::factory('DivertaBook')
        ->raw_query("select * from diverta_Book where impressions " .
                    "and title not like '%sex%' " .
                    "and title not like '%erotic%' " . 
                    "and title not like '%bordel%' " . 
                    "and title not like '%glamour%' " . 
                    "order by clicks/impressions desc limit $random, 1")
        ->find_one();
      return array('bookId' => $book->id);
    }
    
    $lexemeIds = array();
    if (!empty($lexemes)) {
      foreach ($lexemes as $l) {
        $lexemeIds[] = $l->id;
      }
    }
    if (count($lexemeIds) == 0 && !empty($definitions)) {
      $defIdString = '-1';
      foreach ($definitions as $def) {
        $defIdString .= ",{$def->id}";
      }
      $lexemeIds = DB::getArray("select distinct lexemeId from LexemDefinitionMap where DefinitionId in ($defIdString)");
    }
    if (count($lexemeIds) == 0 || count($lexemeIds) >= 100) {
      return null; // No keywords or too many keywords (indicating a regexp search)
    }
    $lexemeIdString = implode(',', $lexemeIds);
    $books = Model::factory('DivertaBook')->table_alias('b')->select('b.*')->join(DivertaIndex::$_table, 'b.id = i.bookId', 'i')
      ->where_in('i.lexemeId', $lexemeIds)->order_by_asc('impressions')->find_many();

    if (count($books)) {
      // 20% chance to serve the book with the fewest impressions / 80% chance to serve the book with the highest CTR
      if (rand(0, 99) < 20) {
        return array('bookId' => $books[0]->id);
      } else {
        $best = 0;
        $bestCtr = 0.00;
        foreach ($books as $i => $book) {
          $ctr = $book->impressions ? ($book->clicks / $book->impressions) : 0.00;
          if ($ctr > $bestCtr) {
            $bestCtr = $ctr;
            $best = $i;
          }
        }
        return array('bookId' => $books[$best]->id);
      }
    }
    return null;
  }
}

class DivertaBook extends BaseObject implements DatedObject {
  public static $_table = 'diverta_Book';
}

class DivertaIndex extends BaseObject {
  public static $_table = 'diverta_Index';
}
