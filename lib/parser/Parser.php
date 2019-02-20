<?php

/**
 * Wrapper around the PHP-parsing-tool parser.
 **/

require_once __DIR__ . '/../third-party/PHP-parsing-tool/Parser.php';

abstract class Parser {
  const COMMENT_MARKER = '¶';

  protected $baseParser;

  // defined as a function, not as a constant, because we may need to do some string manipulation
  abstract function getGrammar();

  abstract function prepare($rep);

  // instantiates a base parser and compiles the grammar
  function __construct() {
    $grammar = $this->getGrammar();

    $s = '';
    foreach ($grammar as $name => $productions) {
      $s .= "{$name} ";
      foreach ($productions as $p) {
        $s .= " :=> {$p}";
      }
      $s .= ".\n";
    }

    $this->baseParser = new \ParserGenerator\Parser($s);
  }

  // Parses a definition. Returns the modified internalRep.
  function parse($def, &$warnings = null) {
    $warnings = $warnings ?? [];

    $s = $def->internalRep;
    $s = $this->prepare($s);
    list($rep, $comments) = $this->extractComments($s);

    $tree = $this->baseParser->parse($rep);
    if (!$tree) {
      // the reported error position needs to be adjusted for comments we have
      // previously removed
      $index = $this->baseParser->getError()['index'];
      $delta = 0;
      foreach ($comments as $pos => $text) {
        if ($pos < $index) {
          $delta += strlen($text);
        }
      }
      throw new Exception($index + $delta);
    }

    $state = new ParserState();
    $s = $this->parseTree($tree, $state, $comments, $warnings);
    $s = $this->reduceFormatting($s);
    $s = $this->restoreComments($s, $comments);
    list($s, $ignored) = Str::sanitize($s, $def->sourceId);
    $s = $this->reduceFormatting($s);

    return $s;
  }

  // remove footnotes and invisible comments and note their initial positions
  // returns
  // * the cleaned up string
  // * an array of position => comment
  private function extractComments($rep) {
    $comments = [];

    preg_match_all("/(\{\{.*\}\})|(▶.*◀)/U", $rep, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $numDeleted = 0; // remember positions relative to the cleaned up string
    foreach ($matches as $m) {
      $text = $m[0][0];
      $pos = $m[0][1];
      $comments[$pos - $numDeleted] = $text;
      $numDeleted += strlen($text);
    }

    // now perform the actual deletion
    $rep = preg_replace("/(\{\{.*\}\})|(▶.*◀)/U", '', $rep);

    return [ $rep, $comments ];
  }

  // replaces comment markers with comments, in appearence order
  private function restoreComments($rep, $comments) {
    // short and dirty; preg_replace_callback() would be faster
    foreach ($comments as $c) {
      $rep = preg_replace('/' . self::COMMENT_MARKER . '/', $c, $rep, 1);
    }

    return $rep;
  }

  // parseTree() ends up inserting too many formatting symbols. Try to clean some of them up.
  private function reduceFormatting($rep) {
    // negative lookbehind for \d so as to leave @15@ @Foobar@ alone
    $rep = preg_replace('/(?<!\d)@ @/', ' ', $rep);

    // bold markers shouldn't span line breaks, except before new meanings
    $rep = preg_replace('/@\s*\n\s*@(?!\d)/', "\n", $rep);

    $rep = str_replace('@, @', ', ', $rep);
    $rep = str_replace('@' . self::COMMENT_MARKER . '@', self::COMMENT_MARKER, $rep);
    return $rep;
  }

  // returns the modified contents
  private function parseTree($t, &$state, $comments, &$warnings) {
    $content = '';

    if ($t->isBranch()) {

      $rule = $t->getType();
      $state->pushRule($rule);
      foreach ($t->getSubnodes() as $c) {
        $content .= $this->parseTree($c, $state, $comments, $warnings);
      }
      $state->popRule();

      switch ($rule) {
        case 'pos':
          // parts of speech should always be italicized
          if (!$state->isItalic()) {
            $warnings[] = "Am pus în italic partea de vorbire «{$content}».";
            $content = '$' . $content . '$';
          }
          if ($state->isBold()) {
            $warnings[] = "Am scos din bold partea de vorbire «{$content}».";
            $content = '@' . $content . '@';
          }
          break;

        case 'posNoHash':
          // parts of speech should always go between hash signs
          $warnings[] = "Am pus între diezi partea de vorbire «{$content}».";
          $content = "#{$content}#";
          break;

        case 'formattedVz':
          if ($state->isBold()) {
            $warnings[] = "Am scos din bold textul «vz».";
            $content = '@' . $content . '@';
          }
          if ($state->isItalic()) {
            $warnings[] = "Am scos din italic textul «vz».";
            $content = '$' . $content . '$';
          }
          break;

        case 'mainForm': // for references
          if (!$state->isItalic()) {
            $warnings[] = "Am pus în italic textul «{$content}».";
            $content = '$' . $content . '$';
          }
          if (!$state->isBold()) {
            $warnings[] = "Am pus în bold textul «{$content}».";
            $content = '@' . $content . '@';
          }
          break;

        case 'form': // word forms and their inflected forms
          if ($content == '-ă') {
            $warnings[] = "Am înlocuit - cu ~ în forma «{$content}».";
            $content = '~ă';
          }
          break;

        case 'meaningNumber':
          $old = $state->getMeaningNumber();
          $new = explode('@', $content)[1];
          $parts = explode('-', $new);
          if (count($parts) > 2) {
            $warnings[] = "Număr de sens incorect: «{$new}».";
          } else {
            if (count($parts) == 1) {
              $from = $to = $new;
            } else {
              $from = $parts[0];
              $to = $parts[1];
            }
            if ($from != $old + 1) {
              $warnings[] = "Numerotare incorectă a sensurilor: «{$new}» după «{$old}».";
            }
            $state->setMeaningNumber($to);
          }
          break;

        case 'entryWithInflectedForms':
          $state->setForm($content);
          break;

        case 'accent':
          $unknown = strpos($content, '#nct#') !== false;
          $formHasAccent = preg_match("/(?<!\\\\|\\')'(\p{L})/u", $state->getForm());
          if ($unknown && $formHasAccent) {
            $warnings[] = 'Indicație de accent necunoscut, dar forma de bază are accent.';
          }
          break;

        case 'formattedSlash':
          $content = $this->fixSlashFormatting($content, $state);
          break;
      }

    } else { // leaf

      $oldPos = $state->getPosition();
      $content = $t->getContent();
      $state->processLeaf($content);
      $curPos = $state->getPosition();

      foreach (array_reverse($comments, true) as $pos => $text) {
        if (($pos > $oldPos) && ($pos <= $curPos)) {
          // Insert markers for comments that we have passed during this $content.
          // We could not have done this before parsing the definition, because
          // it could have broken the parser.
          $content = substr_replace($content, self::COMMENT_MARKER, $pos - $oldPos, 0);
        }
      }
    }

    return $content;
  }

  // $s: string containing exactly one slash plus bold and italic markers;
  // $endState: state at the END of this token.
  function fixSlashFormatting($s, $endState) {
    $result = '/';

    $final = [
      '@' => $endState->isBold(),
      '$' => $endState->isItalic(),
    ];
    $parts = explode('/', $s);

    foreach (['@', '$'] as $char) {
      // initial parity of $char to the left and right of the slash
      $left = substr_count($parts[0], $char) & 1;
      $right = substr_count($parts[1], $char) & 1;

      // desired parity of $char
      $left = $final[$char] ^ $left ^ $right;
      $right = $final[$char];

      // note that the initial $left ^ $right is equal to the final $left ^ $right,
      // so this does not change the parity; the overall definition remains valid
      $result = str_repeat($char, $left) . $result . str_repeat($char, $right);
    }

    return $result;
  }

}
