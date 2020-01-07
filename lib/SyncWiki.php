<?php

const WIKI_BASE = 'https://wiki.dexonline.ro';
const PREFIX_LISTING_URL =
  WIKI_BASE . '/api.php?action=query&list=allpages&apprefix=Articol/&aplimit=max&format=json';
const PAGE_LISTING_URL =
  WIKI_BASE . '/api.php?action=query&pageids=%s&prop=info&inprop=url&format=json';
const PARSER_URL = WIKI_BASE . '/api.php';
const PAGE_RAW_URL = WIKI_BASE . '/index.php?action=raw&curid=%d';

class SyncWiki {

  public static function process($force = false) {
    Log::notice('started');
  // Get the article list sorted alphabetically
    $json = @file_get_contents(PREFIX_LISTING_URL);
    if (!$json) {
      Log::error('Cannot get prefix listing from ' . PREFIX_LISTING_URL);
      return false;
    }
    $data = json_decode($json);

    $pageIds = [];
    $pageIdHash = [];
    foreach ($data->query->allpages as $p) {
      $pageId = (string)$p->pageid;
      $pageIds[] = $pageId;
      $pageIdHash[$pageId] = true;
    }

    // Now get the latest revision for each page and, if it's newer than what we have (or if we don't have it at all), fetch it
    $pageListingUrl = sprintf(PAGE_LISTING_URL, implode('|', $pageIds));
    $json = @file_get_contents($pageListingUrl);
    if (!$json) {
      Log::error('Cannot get page info from ' . $pageListingUrl);
      return false;
    }
    $data = json_decode($json);

    // TODO Figure out if an article is really just a section (i.e., it has subpages)
    foreach ($data->query->pages as $page) {
      $pageId = (int)$page->pageid;
      $title = (string)$page->title;
      $lastRevId = (int)$page->lastrevid;
      $fullUrl = (string)$page->fullurl;

      $curPage = WikiArticle::get_by_pageId($pageId);
      if (!$curPage || $curPage->revId < $lastRevId || $force) {
        $pageRawUrl = sprintf(PAGE_RAW_URL, $pageId);

        if (!$curPage) {
          $curPage = Model::factory('WikiArticle')->create();
          $curPage->pageId = $pageId;
        }
        $curPage->revId = $lastRevId;

        $parts = explode('/', $title);
        array_shift($parts); // throw away the 'Articol/' root;
        $curPage->title = array_pop($parts); // the title is the last fragment
        $curPage->section = implode(' : ', $parts); // other fragments are the section title

        if ($curPage->section) {
          $curPage->fullUrl = $fullUrl;
          $curPage->wikiContents = Str::cleanup(file_get_contents($pageRawUrl));
          if ($curPage->wikiContents === false) {
            Log::error("Cannot fetch raw page from $pageRawUrl");
            return false;
          }
          $curPage->htmlContents = self::parse($curPage->wikiContents);
          if ($curPage->htmlContents === false) {
            Log::error("Cannot parse page");
            return false;
          }
          $curPage->save();

          WikiKeyword::deleteByWikiArticleId($curPage->id);
          $keywords = $curPage->extractKeywords();
          foreach ($keywords as $keyword) {
            $wk = Model::factory('WikiKeyword')->create();
            $wk->wikiArticleId = $curPage->id;
            $wk->keyword = $keyword;
            $wk->save();
          }
          Log::info("Saved page #{$pageId} \"{$title}\"");
        } else {
          Log::warning("Skipping section #{$pageId} \"{$title}\"");
        }
      }
    }

    // Now delete all the pages on our side that aren't category members because
    //   (a) they have been deleted or
    //   (b) they have been renamed to a different prefix
    $ourIds = DB::getArray('select pageId from WikiArticle');
    foreach ($ourIds as $ourId) {
      if (!array_key_exists($ourId, $pageIdHash)) {
        $curPage = WikiArticle::get_by_pageId($ourId);
        Log::info("Deleting page #{$curPage->pageId} \"{$curPage->title}\"");
        $curPage->delete();
      }
    }
    Log::notice('finished');

    return true;
  }

  public static function parse($text) {
    // Preprocessing
    $text = "__NOEDITSECTION__\n" . $text; // Otherwise the returned HTML will contain section edit links
    $text = str_replace(['ş', 'Ş', 'ţ', 'Ţ'], ['ș', 'Ș', 'ț', 'Ț'], $text);

    // Actual parsing
    list($xmlString, $httpCode) = Util::makeRequest(PARSER_URL, [
      'action' => 'parse',
      'text' => $text,
      'format' => 'xml',
      'editsection' => false,
      'disablepp' => true,
    ]);
    $xml = simplexml_load_string($xmlString);
    $html = (string)$xml->parse->text;
    if (!$html) {
      return false;
    }

    // Manipulate the DOM to convert some elements to bootstrap
    // Ensure there is a single root element
    $html = "<div>{$html}</div>";

    // Load the HTML and make sure it is in UTF8. Do not add DTD and <head> and <body> tags.
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
      LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Add table classes
    foreach($dom->getElementsByTagName('table') as $table) {
      $table->setAttribute('class', trim($table->getAttribute('class') . ' table table-hover'));
    }

    // Convert toc divs to panels
    $toc = $dom->getElementById('toc');
    if ($toc) {
      $toc->setAttribute('class', 'panel panel-default');

      foreach ($toc->childNodes as $c) {
        if ($c->nodeType == XML_ELEMENT_NODE) { // skip text nodes
          if (($c->getAttribute('id') == 'toctitle') ||
            ($c->getAttribute('class') == 'toctitle')) {
            $title = $c;
          } else if ($c->nodeName == 'ul') {
            $ul = $c;
          }
        }
      }

      $title->setAttribute('class', 'panel-heading');
      $title->nodeValue = 'Cuprins';

      $body = $dom->createElement('div');
      $body->setAttribute('class', 'panel-body');
      $toc->removeChild($ul);
      $body->appendChild($ul);
      $toc->appendChild($body);
    }

    $html = $dom->saveHTML();

    // decode all non-ASCII characters, which DOMDocument does by default.
    $html = html_entity_decode($html);

    // Postprocessing
    //Use media files from wiki
    $html = preg_replace('/src="\/(.*?)\.(jpg|png|gif)"/', 'src="' . WIKI_BASE . '/$1.$2"', $html);
    $html = preg_replace('/href="\/(.*?)\.(jpg|png|gif)"/', 'href="' . WIKI_BASE . '/$1.$2"', $html);
    $html = preg_replace('/srcset="(.*?)"/', '', $html);

    // Convert links to other articles
    $html = str_replace('href="/wiki/', 'href="/articol/', $html);

    // Fully qualify links to index.php. Most likely, these are link to non-existant articles.
    $html = str_replace('href="/index.php', 'href="https://wiki.dexonline.ro/index.php', $html);

    return $html;
  }


}
