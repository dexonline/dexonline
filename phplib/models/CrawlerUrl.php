<?php

class CrawlerUrl extends BaseObject implements DatedObject {
  static $_table = 'CrawlerUrl';

  const CONTEXT_SIZE = 100;

  private $rawHtml = null;
  private $parser = null;
  private $body = null;
  private static $root = null;

  static function init() {
    $file = __DIR__ . '/../../app/crawler/crawler.conf';
    $config = parse_ini_file($file, true);
    self::$root = $config['global']['path'];
  }

  static function create($url, $siteId) {
    $cu = Model::factory('CrawlerUrl')->create();
    $cu->url = $url;
    $cu->siteId = $siteId;
    return $cu;
  }

  function createParser() {
    $this->loadHtml();
    // insert spaces so that plaintext doesn't concatenate paragraphs.
    $s = str_replace ( "</" , " </" , $this->rawHtml);
    $this->parser = str_get_html($s);
  }

  function freeParser() {
    unset($this->parser);
  }

  function getBody() {
    if (!$this->body) {
      throw new CrawlerException('body needs to be loaded or extracted first');
    }
    return $this->body;
  }

  // fetches the URL and instantiates a parser
  function fetch() {
    $this->rawHtml = @file_get_contents($this->url);
    if (!$this->rawHtml) {
      throw new CrawlerException("could not fetch {$this->url}");
    }

    $this->createParser();
  }

  function extractAuthor($selector, $regexp) {
    $authors = $this->parser->find($selector);

    if (empty($authors)) {
      Log::notice('no authors found');
      $this->author = '';
    } else {
      if (count($authors) > 1) {
        Log::notice('%s authors found, using the first one', count($authors));
      }
      $authorWrapper = trim($authors[0]->plaintext);

      if (!preg_match($regexp, $authorWrapper, $matches)) {
        throw new CrawlerException("Cannot extract author from string [{$authorWrapper}]");
      }

      $this->author = Str::cleanup($matches[1]);
    }
  }

  function extractTitle($selector) {
    $titles = $this->parser->find($selector);

    if (empty($titles)) {
      Log::warning('no titles found');
      $this->title = '';
    } else {
      if (count($titles) > 1) {
        Log::warning('%s titles found, using the first one', count($titles));
      }

      $titleWrapper = $titles[0];

      // strip away all the children nodes
      foreach ($titleWrapper->children() as $child ) {
        $child->outertext = '';
      }

      $this->title = trim($titleWrapper->innertext);
      $this->title = Str::cleanup($this->title);
    }
  }

  function extractBody($selector) {
    $bodies = $this->parser->find($selector);
    if (count($bodies) != 1) {
      throw new CrawlerException('expected 1 body, got ' . count($bodies));
    }

    $this->body = trim($bodies[0]->plaintext);
    $this->sanitizeBody();
  }

  function sanitizeBody() {
    $this->body = Str::cleanup($this->body);
    $this->body = html_entity_decode($this->body);
    $this->body = str_replace('­', '', $this->body); // remove soft hyphens, Unicode 00AD
  }

  function fetchAndExtract($authorSelector, $authorRegexp, $titleSelector, $bodySelector) {
    $this->fetch();
    $this->extractAuthor($authorSelector, $authorRegexp);
    $this->extractTitle($titleSelector);
    $this->extractBody($bodySelector);
  }

  function loadData($file) {
    if (!$this->id) {
      throw new CrawlerException('cannot load data before the CrawlerUrl object has an ID');
    }
    $data = file_get_contents($file);
    if ($data === false) {
      throw new CrawlerException('Cannot load crawled page; please check your config parameters.');
    }
    if (Str::endsWith($file, '.gz')) {
      $data = gzdecode($data);
    }
    if ($data === false) {
      throw new CrawlerException("Cannot gunzip $file .");
    }
    return $data;
  }

  function saveData($data, $file) {
    if (!$this->id) {
      throw new CrawlerException('cannot save data before the CrawlerUrl object has an ID');
    }
    @mkdir(dirname($file), 0777, true);
    if (Str::endsWith($file, '.gz')) {
      $data = gzencode($data);
    }
    if (file_put_contents($file, $data) === false) {
      throw new CrawlerException('Cannot save crawled page; please check your config parameters.');
    }
  }

  function getBodyFileName() {
    return sprintf('%s/%s/body/%s.txt', self::$root, $this->siteId, $this->id);
  }

  function getHtmlFileName() {
    return sprintf('%s/%s/raw/%s.html.gz', self::$root, $this->siteId, $this->id);
  }

  function loadBody() {
    if ($this->body === null) {
      $this->body = $this->loadData($this->getBodyFileName());
    }
  }

  function loadHtml() {
    if ($this->rawHtml === null) {
      $this->rawHtml = $this->loadData($this->getHtmlFileName());
    }
  }

  function saveBody() {
    $this->saveData($this->body, $this->getBodyFileName());
  }

  function saveHtml() {
    $this->saveData($this->rawHtml, $this->getHtmlFileName());
  }

  /**
   * Currently unused. Might be useful for extracting context around a word.
   **/
  function getPhrases() {
    // split at '. ' when there are no periods among the previous 5 characters
    $phrases = preg_split('/(?<=[^.]{5,5})\\. /', $this->getBody());
    foreach ($phrases as &$p) {
      $p .= '.';
    }
    return $phrases;
  }

  /**
   * Returns the context around this unknown word.
   **/
  function getContext($unknownWord) {
    $body = $this->getBody();

    // figure out the multibyte position from the ASCII position
    $pos = mb_strlen(substr($body, 0, $unknownWord->position));

    $left = max(0, $pos - self::CONTEXT_SIZE);
    $right = min(mb_strlen($body), $pos + mb_strlen($unknownWord->word) + self::CONTEXT_SIZE);

    while (Str::getCharAt($body, $left) != ' ') {
      $left++;
    }
    while (Str::getCharAt($body, $right) != ' ') {
      $right--;
    }
    $context = trim(mb_substr($body, $left, $right - $left));

    return $context;
  }

  /**
   * @return array An array of CrawlerUnknownWords (some of these words are in
   * fact known and will be filtered later)
   **/
  function getWords() {
    $this->loadBody();
    $body = $this->getBody();

    // don't deal with dashes and capital letters for now
    preg_match_all("/(?<!([-']|\p{L}))\p{Ll}{3,}(?!([-']|\p{L}))/u",
                   $body, $matches, PREG_OFFSET_CAPTURE);

    $results = [];
    foreach ($matches[0] as $rec) {
      $uw = Model::factory('CrawlerUnknownWord')->create();
      $uw->word = Str::convertOrthography($rec[0]);
      $uw->position = $rec[1];
      $uw->crawlerUrlId = $this->id;
      $results[] = $uw;
    }
    return $results;
  }

  private function unknownWordFilter($crawlerUnknownWord) {
    return !InflectedForm::get_by_formNoAccent($crawlerUnknownWord->word);
  }

  /**
   * Creates CrawlerUnknownWord records for unknown words in $this->body.
   * Does nothing if the extractedUnknownWords field is true.
   **/
  function extractUnknownWords() {
    if ($this->extractedUnknownWords) {
      return;
    }

    $words = $this->getWords();
    $unknown = array_filter($words, [$this, 'unknownWordFilter']);
    foreach ($unknown as $uw) {
      Log::info('Found unknown word [%s] in url %d [%s]', $uw->word, $this->id, $this->url);
      $uw->save();
    }

    $this->extractedUnknownWords = true;
    $this->save();
  }

}

CrawlerUrl::init();
