<?php
/**
 * Description of DiffUtil
 * @holds diff specific constants and functions
 */

require_once __DIR__ . '/third-party/FineDiff.php';

class DiffUtil {

  const GRANULARITY_CHARACTER = 0;
  const GRANULARITY_WORD = 1;
  const GRANULARITY_SENTENCE = 2;
  const GRANULARITY_PARAGRAPH = 3;
  const NUM_GRANULARITIES = 4;
  const DEFAULT_GRANULARITY = self::GRANULARITY_WORD;

  const GRANULARITY_NAMES = [
    self::GRANULARITY_CHARACTER => 'caracter',
    self::GRANULARITY_WORD => 'cuvânt',
    self::GRANULARITY_SENTENCE => 'propoziție',
    self::GRANULARITY_PARAGRAPH => 'paragraf',
  ];

  // Returns the equivalent FineDiff granularity of a DiffUtil granularity.
  // Copied from FineDiff.php, but including $@% among delimiters.
  // This is necessary so that the HTML diff doesn't break.
  const FINE_DIFF_GRANULARITY = [
    self::GRANULARITY_CHARACTER => [ "\n\r", ".\n\r", " \t.\n\r", "" ],
    self::GRANULARITY_WORD => [ "\n\r", ".\n\r", " \t.\n\r" ],
    self::GRANULARITY_SENTENCE => [ "\n\r", ".\n\r" ],
    self::GRANULARITY_PARAGRAPH => [ "\n\r" ],
  ];

  // generates a diff in internal notation using FineDiff
  static function internalDiff($old, $new) {
    $granularity = Session::getDiffGranularity();
    $fineDiffG = self::FINE_DIFF_GRANULARITY[$granularity];
    $opcodes = FineDiff::getDiffOpcodes($old, $new, $fineDiffG);
    ob_start();
    FineDiff::renderFromOpcodes($old, $opcodes, 'DiffUtil::renderInternal');
    $result = ob_get_clean();

    $result = self::escapeDeletions($result);

    return $result;
  }

  // escape all unescaped @#$% characters in the deleted chunks, so that they
  // won't influence the resulting HTML
  static function escapeDeletions($s) {
    // quick and dirty
    do {
      $count = 0;
      // look for unescaped @#$% within unescaped {-...-} and escape it
      $s = preg_replace("/(?<!\\\\)\{-([^}]*)(?<!\\\\)([@#$%])([^}]*)-\}/",
                        '{-${1}\\\\${2}${3}-}',
                        $s, -1, $count);
    } while ($count);

    return $s;
  }

  // returns a degree of dissimilarity between two strings.
  static function diffMeasure($old, $new) {
    $diff = new FineDiff($old, $new, self::GRANULARITY_CHARACTER);
    $s = 0;

    foreach ($diff->getOps() as $op) {
      $class = get_class($op);
      switch ($class) {
      case 'FineDiffCopyOp':
        break; // identical chunks do not contribute to the dissimilarity
      case 'FineDiffInsertOp':
        $s += mb_strlen($op->text);
        break;
      case 'FineDiffDeleteOp':
        $s += $op->fromLen;
        break;
      case 'FineDiffReplaceOp':
        $s += max($op->fromLen, mb_strlen($op->text));
        break;
      }
    }

    return $s;
  }

  // Emits our internal notation {-...-} and {+...+} for deletions and insertions.
  // Adapted from FineDiff.php::renderDiffToHTMLFromOpcode().
  static function renderInternal($opcode, $from, $fromOffset, $fromLen) {
		if ($opcode === 'c') {
			echo substr($from, $fromOffset, $fromLen);

    } else if ($opcode === 'd') {
			$deletion = substr($from, $fromOffset, $fromLen);
      printf('{-%s-}', $deletion);

    } else /* if ( $opcode === 'i' ) */ {
      $insertion = substr($from, $fromOffset, $fromLen);
      printf('{+%s+}', $insertion);

    }
  }

  // Acts on the $rank-th different chunk between $d1 and $d2, according to $action.
  // * if $action == 1 (insert):
  //   * if the chunk is an insertion, then inserts the chunk in $d1
  //   * otherwise inserts the chunk in $d2
  // * otherwise $action == 0 (delete):
  //   * if the chunk is an insertion, then deletes the chunk from $d2
  //   * otherwise deletes the chunk from $d1
  //
  // Modifies exactly one of $d1 and $d2. Returns the modified definition
  static function diffAction($d1, $d2, $rank, $action) {
    $granularity = Session::getDiffGranularity();
    $fineDiffG = DiffUtil::FINE_DIFF_GRANULARITY[$granularity];
    $fd = new FineDiff($d1->internalRep, $d2->internalRep, $fineDiffG);
    $ops = $fd->getOps();

    $ops = self::splitReplaceOps($ops);

    // find the op that was clicked
    $i = 0; // op index
    $pos1 = $pos2 = 0; // string positions

    // consume initial copy operators
    while ($ops[$i] instanceof FineDiffCopyOp) {
      $pos1 += $ops[$i]->len;
      $pos2 += $ops[$i]->len;
      $i++;
    }

    while ($rank) {
      $rank--;
      if ($ops[$i] instanceof FineDiffInsertOp) {
        $pos2 += strlen($ops[$i++]->text);
      } else { // FineDiffDeleteOp
        $pos1 += $ops[$i++]->fromLen;
      }

      // consume more copy operators
      while ($ops[$i] instanceof FineDiffCopyOp) {
        $pos1 += $ops[$i]->len;
        $pos2 += $ops[$i]->len;
        $i++;
      }
    }

    // perform the actual insertion / deletion
    if ($action == 1) { // insertion
      if ($ops[$i] instanceof FineDiffInsertOp) {
        $d1->internalRep = substr_replace($d1->internalRep, $ops[$i]->text, $pos1, 0);
        return $d1;
      } else {
        $text = substr($d1->internalRep, $pos1, $ops[$i]->fromLen);
        $d2->internalRep = substr_replace($d2->internalRep, $text, $pos2, 0);
        return $d2;
      }
    } else { // deletion
      if ($ops[$i] instanceof FineDiffInsertOp) {
        $len = strlen($ops[$i]->text);
        $d2->internalRep = substr_replace($d2->internalRep, '', $pos2, $len);
        return $d2;
      } else {
        $len = $ops[$i]->fromLen;
        $d1->internalRep = substr_replace($d1->internalRep, '', $pos1, $len);
        return $d1;
      }
    }
  }

  // split replace ops into delete + insert ops pairs
  static function splitReplaceOps($ops) {
    $result = [];
    foreach ($ops as $o) {
      if ($o instanceof FineDiffReplaceOp) {
        $result[] = new FineDiffDeleteOp($o->fromLen);
        $result[] = new FineDiffInsertOp($o->text);
      } else {
        $result[] = $o;
      }
    }
    return $result;
  }
}
