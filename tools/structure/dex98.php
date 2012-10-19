<?php

/**
 * Parser for DEX '98.
 *
 * Grammar:
 * Definition -> Lexem PartOfSpeechList MeaningList - Etymology
 * Lexem -> @ Word @
 * PartOfSpeechList -> PartOfSpeech | PartOfSpeech , PartOfSpeechList
 * PartOfSpeech -> Token::Abbreviation | Token::Abbreviation PartOfSpeech
 * MeaningList
 * PreambleRoman -> Token::Abbreviation | lambda
 * DefinitionArabicList -> Token::HierarchyArabic Meaning DefinitionArabicList | Meaning
 * Meaning ->
 *
 **/

require_once __DIR__ . "/../../phplib/util.php";

/* $defResult = db_execute("select * from Definition where sourceId = 1 and status = 0 order by lexicon"); */
/* foreach ($defResult as $row) { */
/*   $def = Model::factory('Definition')->create($row); */
/*   print "Parsing {$def->id} {$def->lexicon}...\n"; */
/*   try { */
/*     parseDex98($def); */
/*   } catch (Exception $e) { */
/*     print($e->getMessage()); */
/*   } */
/* } */

$def = Definition::get_by_id(4319);
$tree = parseDex98($def);
print_r($tree);

/*************************************************************************/

function parseDex98($def) {
  $tok = new Tokenizer($def->internalRep);
  $tree = array();

  // Parse the Lexicon
  $tok->expect(Token::MarkerBold);
  $lexicon = array();
  while ($t = $tok->acceptOtherThan(Token::MarkerBold)) {
    $lexicon[] = $t;
  }
  $tok->expect(Token::MarkerBold);
  $tree['lexicon'] = makeText($lexicon);

  // Parse the inflected form (optional)
  if ($tok->accept(Token::MarkerItalic)) {
    $infl = array();
    while ($t = $tok->acceptOtherThan(Token::MarkerItalic)) {
      $infl[] = $t;
    }
    $tok->expect(Token::MarkerItalic);
    $tree['inflectedForm'] = makeText($infl);
  }

  // Parse the part of speech list
  $pos = array('');
  while ($t = $tok->accept(array(Token::Abbreviation, Token::PunctuationComma))) {
    if ($t->getType() == Token::Abbreviation) {
      $old = $pos[count($pos) - 1];
      $pos[count($pos) - 1] .= ($old ? ' ' : '') . $t->getValue();
    } else {
      $pos[] = '';
    }
  }
  $tree['pos'] = $pos;

  // Parse the definitions, up to Token::PunctuationDash or Token::BracketOpen
  $tree['meanings'] = array();
  $meaning = array();
  $hierarchy = null;
  while ($t = $tok->acceptOtherThan(array(Token::PunctuationDash, Token::BracketOpen))) {
    if (in_array($t->getType(), array(Token::HierarchyRoman, Token::HierarchyArabic, Token::DiamondWhite, Token::DiamondBlack))) {
      if (count($meaning)) {
        // End the previous meaning
        $tree['meanings'][] = array('hierarchy' => $hierarchy, 'text' => makeText($meaning));
        $meaning = array();
      }
      $hierarchy = (string)$t;
    } else {
      $meaning[] = $t;
    }
  }
  // End the final meaning
  if (count($meaning)) {
    $tree['meanings'][] = array('hierarchy' => $hierarchy, 'text' => makeText($meaning));
  }

  // Parse the pronunciation and variants
  if ($tok->accept(Token::BracketOpen)) {
    $specials = array('pronunciation' => array(), 'variants' => array());
    $section = null;
    while ($t = $tok->acceptOtherThan(Token::BracketClosed)) {
      if ($t->getType() == Token::Abbreviation && $t->getValue() == 'pronunțat') {
        $tok->expect(Token::PunctuationOther); // Skip the colon
        $section = 'pronunciation';
      } else if ($t->getType() == Token::Abbreviation && $t->getValue() == 'variantă') {
        $tok->expect(Token::PunctuationOther); // Skip the colon
        $section = 'variants';
      } else if ($t->getType() == Token::PunctuationDash) {
        // Skip it
      } else if ($section) {
        $specials[$section][] = $t;
      }
    }
    $tok->expect(Token::BracketClosed);
    if (!empty($specials['pronunciation'])) {
      $tree['pronunciation'] = makeText($specials['pronunciation']);
    }
    if (!empty($specials['variants'])) {
      $tree['variants'] = makeText($specials['variants']);
    }
  }

  // Parse the etymology all the way to the end.
  $tok->expect(Token::PunctuationDash);
  $etym = array();
  while ($t = $tok->acceptOtherThan(null)) {
    $etym[] = $t;
  }
  $tok->expect(null);
  $tree['etymology'] = makeText($etym);

  return $tree;
}

class Token {
  private $type;
  private $value;

  const Word = 1;
  const PunctuationDot = 2;
  const PunctuationComma = 3;
  const PunctuationDash = 4;
  const PunctuationOther = 5;
  const Abbreviation = 6;
  const MarkerBold = 100;
  const MarkerItalic = 101;
  const MarkerSpaced = 102;
  const HierarchyRoman = 200;
  const HierarchyArabic = 201;
  const HierarchyLowercase = 203;
  const DiamondWhite = 300;
  const DiamondBlack = 301;
  const ParenthesisOpen = 400;
  const ParenthesisClosed = 401;
  const BracketOpen = 402;
  const BracketClosed = 403;
  const EqualSign = 500;
  const Superscript = 501;
  const Subscript = 502;

  // To determine if there should be a space between tokens A and B, we assign preferences and scores for spacing before and after
  // every token type. If A's space-after score is greater than B's space-before score, than A's preference wins. Otherwise,
  // B's preference wins.
  private static $SPACING = array(Token::Word                     => array(true, 10, true, 10),
                                  Token::PunctuationDot           => array(false, 100, true, 10),
                                  Token::PunctuationComma         => array(false, 100, true, 10),
                                  Token::PunctuationDash          => array(false, 100, true, 10),
                                  Token::PunctuationOther         => array(false, 100, true, 10),
                                  Token::Abbreviation             => array(true, 10, true, 10),
                                  Token::MarkerBold               => array(false, 10, false, 200),
                                  Token::MarkerItalic             => array(false, 10, false, 200),
                                  Token::MarkerSpaced             => array(false, 10, false, 200),
                                  Token::HierarchyLowercase       => array(true, 100, true, 100),
                                  Token::ParenthesisOpen          => array(true, 10, false, 100),
                                  Token::ParenthesisClosed        => array(false, 100, true, 10),
                                  Token::BracketOpen              => array(true, 10, false, 100),
                                  Token::BracketClosed            => array(false, 100, true, 10),
                                  Token::EqualSign                => array(true, 100, true, 100),
                                  Token::Superscript              => array(false, 100, true, 10),
                                  Token::Subscript                => array(false, 100, true, 10),
                                  );

  function __construct($type, $value = null) {
    $this->type = $type;
    $this->value = $value;
  }

  function getType() {
    return $this->type;
  }

  function getValue() {
    return $this->value;
  }

  function __toString() {
    switch ($this->type) {
    case Token::Word:                 return "word ({$this->value})";
    case Token::PunctuationDot:       return ". (dot)";
    case Token::PunctuationComma:     return ", (comma)";
    case Token::PunctuationDash:      return "- (dash)";
    case Token::PunctuationOther:     return "{$this->value} (punctuation)";
    case Token::Abbreviation:         return "abbreviation ({$this->value})";
    case Token::MarkerBold:           return "@ (bold marker)";
    case Token::MarkerItalic:         return "$ (italic marker)";
    case Token::MarkerSpaced:         return "% (spaced marker)";
    case Token::HierarchyRoman:       return "{$this->value} (Roman hierarchy)";
    case Token::HierarchyArabic:      return "{$this->value} (Arabic hierarchy)";
    case Token::HierarchyLowercase:   return "{$this->value} (lowercase hierarchy)";
    case Token::DiamondWhite:         return "* (white diamond)";
    case Token::DiamondBlack:         return "** (black diamond)";
    case Token::ParenthesisOpen:      return "( (open parenthesis)";
    case Token::ParenthesisClosed:    return "( (closed parenthesis)";
    case Token::BracketOpen:          return "( (open bracket)";
    case Token::BracketClosed:        return "( (closed bracket)";
    case Token::EqualSign:            return "= (equal sign)";
    case Token::Superscript:          return "superscript ({$this->value})";
    case Token::Subscript:            return "subscript ({$this->value})";
    case null:                        return "null";
    }
  }

  static function getName($type) {
    $t = new Token($type);
    return (string)$t;
  }

  function spaceBetween($other) {
    return (self::$SPACING[$this->type][3] >= self::$SPACING[$other->type][1])
      ? self::$SPACING[$this->type][2]
      : self::$SPACING[$other->type][0];
  }
}


class Tokenizer {
  public static $ALPHABET = null;
  private $s;
  private $pos;
  private $len;
  private $source;
  private $lookAhead; // a Token we have already consumed, but not returned

  function __construct($s) {
    if (!self::$ALPHABET) {
      self::$ALPHABET = array_merge(range('a', 'z'), array('ă', 'â', 'î', 'ș', 'ț', 'á', 'é', 'ë', 'í', 'ó', 'ú', '-', '"'),
                                    array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'));
    }

    $this->s = $s;
    $this->pos = 0;
    $this->len = mb_strlen($s);
    $this->source = Source::get_by_urlName('dex');
  }

  private function throwException($message) {
    $snippet = mb_substr($this->s, $this->pos, 20);
    throw new Exception("At position {$this->pos} [$snippet]: {$message}");
  }

  // Returns the next token. Returns null on end of stream. Throws exceptions when needed
  function getNextToken() {
    if ($this->pos >= $this->len) {
      return null;
    }

    $s = mb_substr($this->s, $this->pos);
    $matches = array();

    if (preg_match("/^@([IVX]+)\\.@/", $s, $matches)) {
      // Roman numeral hierarchy, @III.@
      $value = AdminStringUtil::romanToArabic($matches[1]);
      if (!$value) {
        $this->throwException("Unknown Roman numeral [{$matches[1]}]");
      }
      $this->pos += strlen($matches[0]);
      return new Token(Token::HierarchyRoman, $value);
    }
      
    if (preg_match("/^@(\\d+)\\.@/", $s, $matches)) {
      // Arabic numeral hierarchy, @3.@
      $this->pos += strlen($matches[0]);
      return new Token(Token::HierarchyArabic, $matches[1]);
    }
      
    if (preg_match("/^@([a-z])\\)@/", $s, $matches)) {
      // Lowercase hierarchy, @a)@
      $value = ord($matches[1]) - ord('a') + 1;
      $this->pos += strlen($matches[0]);
      return new Token(Token::HierarchyLowercase, $value);
    }
      
    if (preg_match("/^\\*+/", $s, $matches)) {
      // Diamonds, * and **
      if (strlen($matches[0]) > 2) {
        $this->throwException("Unknown asterisk count [{$matches[0]}]");
        return false;
      }
      $this->pos += strlen($matches[0]);
      $type = ($matches[0] == '*') ? Token::DiamondWhite : Token::DiamondBlack;
      return new Token($type, $matches[0]);
    }
      
    if (preg_match("/^@/", $s)) {
      // Bold marker
      $this->pos++;
      return new Token(Token::MarkerBold, '@');
    }

    if (preg_match("/^\\$/", $s)) {
      // Italic marker
      $this->pos++;
      return new Token(Token::MarkerItalic, '$');
    }

    if (preg_match("/^%/", $s)) {
      // Spaced marker
      $this->pos++;
      return new Token(Token::MarkerSpaced, '%');
    }

    if (preg_match("/^\\./", $s)) {
      // Dot
      $this->pos++;
      return new Token(Token::PunctuationDot, '.');
    }

    if (preg_match("/^,/", $s)) {
      // Comma
      $this->pos++;
      return new Token(Token::PunctuationComma, ',');
    }

    if (preg_match("/^ - /", $s)) {
      // Dash
      $this->pos += 3;
      return new Token(Token::PunctuationDash, '-');
    }

    if (preg_match("/^[!?:;]/", $s, $matches)) {
      // Other
      $this->pos++;
      return new Token(Token::PunctuationOther, $matches[0]);
    }

    if (preg_match("/^\\(/", $s)) {
      // Open parent
      $this->pos++;
      return new Token(Token::ParenthesisOpen, '(');
    }

    if (preg_match("/^\\)/", $s)) {
      // Closed parent
      $this->pos++;
      return new Token(Token::ParenthesisClosed, ')');
    }

    if (preg_match("/^\\[/", $s)) {
      // Open bracket
      $this->pos++;
      return new Token(Token::BracketOpen, '[');
    }

    if (preg_match("/^\\]/", $s)) {
      // Closed bracket
      $this->pos++;
      return new Token(Token::BracketClosed, ']');
    }

    if (preg_match("/^=/", $s)) {
      // Equal sign
      $this->pos++;
      return new Token(Token::EqualSign, '=');
    }

    if (preg_match("/^#([^#]+)#/", $s, $matches)) {
      // Abbreviation
      $long = AdminStringUtil::getAbbreviation($this->source->id, mb_strtolower($matches[1]));
      if (!$long) {
        $this->throwException("Unknown abbreviation [{$matches[1]}]");
        return false;
      }
      $this->pos += strlen($matches[0]);
      return new Token(Token::Abbreviation, $long);
    }

    if (preg_match("/^([_^])(\d)/", $s, $matches)) {
      // Superscript / subscript -- short syntax
      $this->pos += strlen($matches[0]);
      $type = $matches[1] == '^' ? Token::Superscript : Token::Subscript;
      return new Token($type, $matches[2]);
    }

    if (preg_match("/^([_^])\{([^}]*)\}/", $s, $matches)) {
      // Superscript / subscript -- long syntax
      $this->pos += strlen($matches[0]);
      $type = $matches[1] == '^' ? Token::Superscript : Token::Subscript;
      return new Token($type, $matches[2]);
    }

    if (in_array(mb_strtolower(StringUtil::getCharAt($s, 0)), self::$ALPHABET)) {
      // Word
      $i = 0;
      $len = mb_strlen($s);
      while ($i < $len && in_array(mb_strtolower(StringUtil::getCharAt($s, $i)), self::$ALPHABET)) {
        $i++;
      }
      $this->pos += $i;
      return new Token(Token::Word, mb_substr($s, 0, $i));
    }

    if (preg_match("/^[ \t\n]+/", $s, $matches)) {
      // Whitespace
      $this->pos += strlen($matches[0]);
      return $this->getNextToken();
    }

    $this->throwException(sprintf("Unknown symbol [%s]", mb_substr($s, 0, 1)));
    return false;
  }

  /**
   * Returns the token value if the next token has one of the types in $tokenTypes.
   * $tokenTypes can be an array of types or a single type.
   * Returns the token or false if the next token has a different type.
   **/
  function accept($tokenTypes, $include = true) {
    global $tokens;

    if (!is_array($tokenTypes)) {
      $tokenTypes = array($tokenTypes);
    }
    if (!$this->lookAhead) {
      $this->lookAhead = $this->getNextToken();
    }
    if (!$this->lookAhead) {
      return null; // end of stream
    }
    if ($include == in_array($this->lookAhead->getType(), $tokenTypes)) {
      $result = $this->lookAhead;
      $this->lookAhead = null;
      return $result;
    }
    return false;
  }

  /**
   * Returns the token value *unless* the next token has one of the types in $tokenTypes.
   * $tokenTypes can be an array of types or a single type.
   * Returns the token or false if the next token has a forbidden type.
   **/
  function acceptOtherThan($tokenTypes) {
    return $this->accept($tokenTypes, false);
  }

  function expect($tokenTypes) {
    $token = $this->accept($tokenTypes);
    if ($token === false) {
      if (is_array($tokenTypes)) {
        $tokenTypeNames = array();
        foreach ($tokenTypes as $tt) {
          $tokenTypeNames[] = Token::getName($tt);
        }
        $expected = 'one of {' . implode(',', $tokenTypeNames) . '}';
      } else {
        $expected = Token::getName($tokenTypes);
      }
      $this->throwException("Expected $expected");
      exit;
    }
    return $token;
  }
}

/**
 * Converts an array of token into text, adding spaces where necessary.
 * Throws an error if it encounters an unexpected token type (i.e. diamonds).
 **/
function makeText($tokens) {
  $result = '';
  $inBold = false;
  $inItalic = false;
  $inSpaced = false;

  $prevToken = new Token(Token::ParenthesisOpen);
  foreach ($tokens as $t) {
    $spacing = ($result && $prevToken->spaceBetween($t)) ? ' ' : '';
    switch ($t->getType()) {
    case Token::Word:
    case Token::PunctuationDot:
    case Token::PunctuationComma:
    case Token::PunctuationDash:
    case Token::PunctuationOther:
    case Token::Abbreviation:
    case Token::ParenthesisOpen:
    case Token::ParenthesisClosed:
    case Token::EqualSign:
      $result .= $spacing . $t->getValue(); break;
    case Token::MarkerBold:
      $result .= $inBold ? ('@' . $spacing) : ($spacing . '@');
      $inBold = !$inBold;
      break;
    case Token::MarkerItalic:
      $result .= $inItalic ? ('$' . $spacing) : ($spacing . '$');
      $inItalic = !$inItalic;
      break;
    case Token::MarkerSpaced:
      $result .= $inSpaced ? ('%' . $spacing) : ($spacing . '%');
      $inSpaced = !$inSpaced;
      break;
    case Token::HierarchyLowercase: $result .= $spacing . '@' . chr(ord('a') + $t->getValue() - 1) . ')@'; break;
    case Token::Superscript:
      $result .= $spacing . '^' . $t->getValue(); break;
    case Token::Subscript:
      $result .= $spacing . '_' . $t->getValue(); break;
    default: die("Unknown token type {$t->getType()} in makeText()\n");
    }
    $prevToken = $t;
  }
  return $result;
}

?>
