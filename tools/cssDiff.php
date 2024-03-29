#!/usr/bin/php
<?php
/**
 * This script compares two CSS files and outputs the declarations present in
 * the second one, but not the first.
 *
 * It can optionally prefix every selector in the resulting file with a
 * specified string, including replacing the :root pseudo class.
 *
 * Note that final semicolons (before closing braces, "}") are mandatory.
 * Therefore, this script will only work on expanded Bootstrap files, not
 * minified ones.
 **/

require_once __DIR__ . '/../lib/Core.php';

LocaleUtil::change('en_US.utf8');

// parse command line options
$opts = getopt('df:p:rt:w');
$debug = isset($opts['d']);
$fromFile = $opts['f'] ?? null;
$prefix = $opts['p'] ?? '';
$replaceRoot = isset($opts['r']);
$toFile = $opts['t'] ?? null;
$addWarning = isset($opts['w']);

if (!$fromFile || !$toFile) {
  error(
    "Usage: cssDiff.php\n" .
    "  [-d]                     (print debug info)\n" .
    "  -f <from_file>\n" .
    "  [-p <selector_prefix>]\n" .
    "  [-r]                     (replace :root pseudo class with selector_prefix)\n" .
    "  -t <to_file>\n" .
    "  [-w]                     (add a comment that the file is autogenerated)\n"
  );
}

$fromParser = new CssParser($fromFile);
$fromDoc = $fromParser->parse();

$toParser = new CssParser($toFile);
$toDoc = $toParser->parse();

$map = $fromDoc->index();
$toDoc->diff($map);

$toDoc->print($prefix, $replaceRoot, $addWarning);

/*************************************************************************/

function debug($msg) {
  global $debug;

  if ($debug)  {
    print "$msg\n";
  }
}

function error($msg) {
  print "$msg\n";
  exit(1);
}

class CssStream {
  private $f;
  private $peek0, $peek1; /* characters read from the stream, but not yet returned */

  function __construct($filename) {
    $this->f = @fopen($filename, 'r') or error("Cannot open file $filename.");
    $this->peek0 = fgetc($this->f);
    $this->peek1 = fgetc($this->f);
    $this->skipComment();
  }

  /**
   * Consumes one character. Shifts the lookahead buffer.
   */
  function advance() {
    $result = $this->peek0;
    $this->peek0 = $this->peek1;
    $this->peek1 = fgetc($this->f);
    return $result;
  }

  function skipComment() {
    while (($this->peek0 == '/') && ($this->peek1 == '*')) {
      do {
        $this->advance();
      } while (($this->peek0 != '*') || ($this->peek1 != '/'));

      // skip the closing slash-star
      $this->advance();
      $this->advance();
    }
  }

  /**
   * Consumes one character, skipping any slash-star comments.
   * From this point on, we no longer use advance(), only getc().
   */
  function getc() {
    $result = $this->advance();
    $this->skipComment();
    return $result;
  }

  /**
   * Advances through the stream, skipping whitespace (and comments), to the
   * next meaningful character or EOF. That caracter is returned, but not
   * consumed.
   */
  function peek() {
    while ($this->peek0 && ctype_space($this->peek0)) {
      $this->getc();
    }
    return $this->peek0;
  }

  /**
   * Reads and returns characters, including whitespace, up to $stop. $stop is
   * consumed, but not included in the returned value.
   */
  function readTo($stop) {
    $result = '';
    while ($this->peek0 != $stop) {
      $result .= $this->getc();
    }
    $this->getc(); // skip $stop
    return trim($result);
  }

  /**
   * Skips whitespace until the next meaningful character, which should be
   * $c. Consumes $c.
   */
  function expect($c) {
    $p = $this->peek();
    if ($p != $c) {
      error("Expected character [$c], got [$p]");
    }
    $this->getc();
  }
}

class CssParser {
  private CssStream $stream;

  function __construct($filename) {
    $this->stream = new CssStream($filename);
  }

  function parse() {
    $doc = new CssDocument();

    while (($c = $this->stream->peek()) !== false) {
      if ($c == '@') {
        $doc->addBlock($this->parseAtRule());
      } else {
        $doc->addBlock($this->parseRuleset());
      }
    }

    return $doc;
  }

  function parseRuleset() {
    $rs = new CssRuleset();
    $rs->setSelector($this->stream->readTo('{'));

    while ($this->stream->peek() != '}') {
      $rs->addDeclaration(
        $this->stream->readTo(':'),
        $this->stream->readTo(';')
      );
    }
    $this->stream->expect('}');

    return $rs;
  }

  function parseAtRule() {
    $ar = new CssAtRule();
    $ar->setIdentifier($this->stream->readTo('{'));

    while ($this->stream->peek() != '}') {
      $ar->addRuleset($this->parseRuleset());
    }
    $this->stream->expect('}');

    return $ar;
  }
}

/**
 * A ruleset is a glorified array of property-value declarations. We don't use
 * key-value pairs because, in theory, a ruleset can define the same property
 * twice.
 */
class CssRuleset {
  private string $selector;
  private array $declarations = [];

  function getSelector() {
    return $this->selector;
  }

  function setSelector(string $selector)  {
    $this->selector = $selector;
  }

  function getDeclarations() {
    return $this->declarations;
  }

  function addDeclaration(string $prop, string $value) {
    $this->declarations[] = [ $prop, $value ];
  }

  function isEmpty() {
    return empty($this->declarations);
  }

  /**
   * Removes declarations that appear identically in $map. $map is a map of
   * ruleset selector x prop => value for the correct parent at-rule (if any).
   */
  function diff(array $map) {
    foreach ($this->declarations as $i => $pair) {
      $old = $map[$this->selector][$pair[0]] ?? null;
      if ($old == $pair[1])  {
        debug("Removing unchanged declaration {$pair[0]}: {$pair[1]}");
        unset($this->declarations[$i]);
      }
    }
  }

  function print(string $prefix, bool $replaceRoot, int $indent = 0) {
    $sel = $this->selector;

    if ($prefix) {
      $parts = explode(',', $sel);
      $newParts = [];
      foreach ($parts as $part) {
        $part = trim($part);
        if (!Str::startsWith($part, $prefix) && !Str::startsWith($part, ':')) {
          $part = $prefix . ' ' . $part;
        } else if ($replaceRoot && ($part == ':root')) {
          $part = $prefix;
        }
        $newParts[] = $part;
      }
      $sel = implode(', ', $newParts);
    }

    $sp = str_repeat(' ', 2 * $indent);
    print "{$sp}{$sel} {\n";
    foreach ($this->declarations as $pair) {
      print "{$sp}  {$pair[0]}: {$pair[1]};\n";
    }
    print "{$sp}}\n";
  }
}

/**
 * An at-rule like "@media (min-width: 1200px) { ... }", for our purpopses, has
 * an identifier and contains zero or more rulesets.
 */
class CssAtRule {
  private string $identifier;
  private array $rulesets = [];

  function getIdentifier() {
    return $this->identifier;
  }

  function setIdentifier(string $identifier)  {
    $this->identifier = $identifier;
  }

  function getRulesets() {
    return $this->rulesets;
  }

  function addRuleset(CssRuleset $rs) {
    $this->rulesets[] = $rs;
  }

  function isEmpty() {
    return empty($this->rulesets);
  }

  /**
   * Removes declarations that appear identically in $map. Removes empty
   * rulesets. $map is a map of at-rule identifier x ruleset selector x prop
   * => value produced by index().
   */
  function diff(array $map) {
    foreach ($this->rulesets as $i => $rs) {
      $rs->diff($map[$this->identifier]);

      if ($rs->isEmpty()) {
        debug('Removing empty ruleset ' . $rs->getSelector());
        unset($this->rulesets[$i]);
      }
    }
  }

  function print(string $prefix, bool $replaceRoot) {
    print "{$this->identifier} {\n";
    foreach ($this->rulesets as $rs) {
      $rs->print($prefix, $replaceRoot, 1);
    }
    print "}\n";
  }
}

class CssDocument {
  // an array of at-rules or rulesets
  private array $blocks = [];

  function addBlock(object $obj) {
    $this->blocks[] = $obj;
  }

  /**
   * Returns a map of at-rule identifier x ruleset selector x prop => value.
   */
  function index() {
    $map = [];
    foreach ($this->blocks as $block) {
      if ($block instanceof CssRuleset) {
        $selector = $block->getSelector();
        foreach ($block->getDeclarations() as $pair) {
          $map[''][$selector][$pair[0]] = $pair[1];
        }
      } else { /* CssAtRule */
        $identifier = $block->getIdentifier();
        foreach ($block->getRulesets() as $rs) {
          $selector = $rs->getSelector();
          foreach ($rs->getDeclarations() as $pair) {
            $map[$identifier][$selector][$pair[0]] = $pair[1];
          }
        }
      }
    }
    return $map;
  }

  /**
   * Removes declarations that appear identically in $map. Removes empty
   * rulesets and atrules. $map is a map of at-rule identifier x ruleset
   * selector x prop => value produced by index().
   */
  function diff(array $map) {
    foreach ($this->blocks as $i => $block) {
      if ($block instanceof CssRuleset) {
        $block->diff($map['']);
        if ($block->isEmpty()) {
          debug('Removing empty ruleset ' . $block->getSelector());
          unset($this->blocks[$i]);
        }
      } else { /* CssAtRule */
        $block->diff($map);
        if ($block->isEmpty()) {
          debug('Removing empty at-rule ' . $block->getIdentifier());
          unset($this->blocks[$i]);
        }
      }
    }
  }

  function print(string $prefix, bool $replaceRoot, bool $addWarning) {
    if ($addWarning) {
      print "/* This file is autogenerated by scripts/cssDiff.php. DO NOT EDIT! */\n";
    }

    foreach ($this->blocks as $block) {
      $block->print($prefix, $replaceRoot);
      print "\n";
    }
  }
}
