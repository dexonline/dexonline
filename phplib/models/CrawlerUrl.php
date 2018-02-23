<?php

class CrawlerUrl extends BaseObject implements DatedObject {
  static $_table = 'CrawlerUrl';

  private $rawHtml = null;
  private $parser = null;
  private $body = null;

  static function create($url, $siteId) {
    $cu = Model::factory('CrawlerUrl')->create();
    $cu->url = $url;
    $cu->siteId = $siteId;
    return $cu;
  }

  function createParser() {
    $s = str_replace ( "</" , " </" , $this->rawHtml);
    $this->parser = str_get_html($s);
  }

  function freeParser() {
    unset($this->parser);
  }

  // fetches the URL and instantiates a parser
  function fetch() {
    $this->rawHtml = @file_get_contents($this->url);
    if (!$this->rawHtml) {
      throw new CrawlerException("could not fetch {$this->url}");
    }

    // insert spaces so that plaintext doesn't concatenate paragraphs.
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

  function getBodyFileName($root) {
    return sprintf('%s/%s/body/%s.txt', $root, $this->siteId, $this->id);
  }

  function getHtmlFileName($root) {
    return sprintf('%s/%s/raw/%s.html.gz', $root, $this->siteId, $this->id);
  }

  function loadBody($root) {
    $this->body = $this->loadData($this->getBodyFileName($root));
  }

  function loadHtml($root) {
    $this->rawHtml = $this->loadData($this->getHtmlFileName($root));
  }

  function saveBody($root) {
    $this->saveData($this->body, $this->getBodyFileName($root));
  }

  function saveHtml($root) {
    $this->saveData($this->rawHtml, $this->getHtmlFileName($root));
  }
}
