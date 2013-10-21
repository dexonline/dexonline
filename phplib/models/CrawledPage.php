<?php


class CrawledPage  extends BaseObject implements DatedObject {
  
  public static $_table = 'CrawledPage';

  // Salveaza informatiile despre pagina curent crawl-ata in tabelul CrawledPage
  public static function savePage2DB($url, $httpStatus, $rawPage, $parsedText, $rawPagePath, $parsedTextPath, $timestamp) {
    @mkdir(dirname($rawPagePath), 0777, true);
    @mkdir(dirname($parsedTextPath), 0777, true);
    file_put_contents($rawPagePath, $rawPage);
    file_put_contents($parsedTextPath, $parsedText);

    try {
      $tableObj = Model::factory(self::$_table);
      $tableObj->create();
      $tableObj->timestamp = $timestamp;
      $tableObj->url = $url;
      $tableObj->httpStatus = $httpStatus;
      $tableObj->rawPagePath = $rawPagePath;
      $tableObj->parsedTextPath = $parsedTextPath;
      $tableObj->save();

      return $tableObj->id;
    } catch(Exception $ex) {
      AppLog::exceptionLog($ex);
    }
    return null;
  }

  //intoarce o lista cu domeniile parsate
  public static function getListOfDomains() {

    //return Model::factory(self::$_table)->raw_query("select id, substr(substring_index(url, '/', 3),8) as domain from CrawledPage group by domain order by id asc;")->find_many();
    return Model::factory(self::$_table)->raw_query("select id, domain from
       (select id, substr(substring_index(url, '/', 3),8) as domain from CrawledPage order by id desc) alias1 group by domain order by id asc;")->find_many();
  }

  function getNextDiacriticsFile() {
    return Model::factory(self::$_table)->raw_query("select id, parsedTextPath from CrawledPage where id not in (select fileId from FilesUsedInDiacritics);")->find_one();
  }
  

}

?>