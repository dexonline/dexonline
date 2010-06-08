<?
class DivertaAdsModule extends AdsModule {

  public function run($lexems, $definitions) {
    $lexemIds = array();
    if (!empty($lexems)) {
      foreach ($lexems as $l) {
        $lexemIds[] = $l->id;
      }
    }
    if (count($lexemIds) == 0 && !empty($definitions)) {
      $defIdString = '-1';
      foreach ($definitions as $def) {
        $defIdString .= ",{$def->id}";
      }
      $lexemIds = db_getArray(db_execute("select distinct lexemId from LexemDefinitionMap where DefinitionId in ($defIdString)"));
    }
    if (count($lexemIds) == 0 || count($lexemIds) >= 100) {
      return null; // No keywords or too many keywords (indicating a regexp search)
    }
    $lexemIdString = join(',', $lexemIds);
    $books = db_getObjects(new DivertaBook(), db_execute("select distinct b.* from diverta_Book b, diverta_Index i where b.id = i.bookId and i.lexemId in ({$lexemIdString}) order by impressions"));

    if (count($books)) {
      $book = $books[0];
      $book->impressions++;
      $book->save();
      return array('bookId' => $book->id, 'bookUrl' => 'http://google.com');
    }
    return null;
  }
}

class DivertaBook extends BaseObject {
	var $_table = 'diverta_Book';

  public static function get($where) {
    $obj = new DivertaBook();
    $obj->load($where);
    return $obj->id ? $obj : null;
  }
}

class DivertaIndex extends BaseObject {
	var $_table = 'diverta_Index';
}

?>
