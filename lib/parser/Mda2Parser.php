<?php

class Mda2Parser extends Parser {
  const PARTS_OF_SPEECH = [
    'a', 'ad', 'ada', 'af', 'afi', 'afp', 'afpt', 'afs', 'ai', 'ain', 'am', 'amp',
    'an', 'anh', 'anhi', 'ap', 'apr', 'ard', 'arh', 'arp', 'art', 'arti', 'as', 'av',
    'avi', 'avr', 'c', 'ec', 'i', 'la', 'lav', 'lc', 'ls', 'nc', 'ncv', 'nf',
    'no', 'nof', 'pd', 'pdf', 'pdm', 'pin', 'pir', 'pî', 'png', 'pnh', 'pnhi',
    'pp', 'ppl', 'ppr', 'prl', 'prli', 'prn', 's', 'sa', 'sf', 'sfa', 'sfi',
    'sfm', 'sfn', 'sfp', 'sfpa', 'sfs', 'sfsa', 'si', 'sm', 'sma', 'smf',
    'smi', 'smn', 'smnf', 'smp', 'sms', 'smsa', 'sn', 'sna', 'snf', 'sni',
    'snm', 'snp', 'sns', 'ssg', 'ssga', 'ssp', 'v', 'va', 'vi', 'vi(a)', 'vif',
    'vim', 'vir', 'virp', 'virt', 'vit', 'vit(a)', 'vitr', 'viu', 'vp', 'vr',
    'vr(a)', 'vra', 'vri', 'vrim', 'vrp', 'vrr', 'vrt', 'vru', 'vt', 'vt(a)',
    'vta', 'vt(f)', 'vtf', 'vtfr', 'vti', 'vti(a)', 'vtir', 'vtr', 'vtr(a)',
    'vtra', 'vtrf', 'vtri', 'vtrp', 'vtrr', 'vu',
  ];

  const GRAMMAR = [
    'start' => [
      'definition',
      'reference',
    ],
    'definition' => [
      'entryWithInflectedForms (ws formattedPosList)? ws bracket ws numberedMeanings',
    ],
    'bracket' => [
      '/[$@]*/ "[" attestation (morphology formattedSlash ws?)* etymology "]" /[$@]*/',
    ],
    'attestation' => [
      '"#At:#" ws /([^\/]|\/ ?[\d\w])+/s formattedSlash ws',
    ],
    'morphology' => [
      'abbreviation',
      'accent',
      'alsoWritten',
      'cases',
      'number',
      'tenses',
      'pronunciation',
      'variants',
    ],
    'formattedSlash' => [
      '/[$@]*\/[$@]*/',
    ],
    'abbreviation' => [
      '"#Abr#:" ws /\$[^$]+\$/ ws',
    ],
    'accent' => [
      '("A:"|"#A:#") "#nct#" ws',
      '("A:"|"#A:#"|"#A și:#"|"A și (#înv#):") /[^\/]+/s',
    ],
    'alsoWritten' => [
      '("S:"|"#S:#"|"#S și:#") /[^\/]+/s',
    ],
    'cases' => [
      '("#Ac#:"|"#D:#"|"#G-D#:"|"#Vc#:"|"#Vc# și:") /[^\/]+/s',
    ],
    'number' => [
      '("#Pl:#"|"#Pl#:"|"#Pl# și:"|"#Sg#:") /[^\/]+/s',
    ],
    'pronunciation' => [
      '("#P:#"|"#P și:#") ws pronunciationList+/( și )|( )/ ws'
    ],
    'pronunciationList' => [
      'morphologyParent? pronunciationFormatting morphologyForm+", " ","? pronunciationFormatting',
      '"?"',
    ],
    'pronunciationFormatting' => [
      '/[$@]+/',
    ],
    'morphologyParent' => [
      '/\(.*?\)/ ws',
    ],
    'morphologyForm' => [
      '/[-~]*/ fragment+/[- ]/ /[-~]*/'
    ],
    'tenses' => [
      '("#Cj#:"|"#Cnd#:"|"#Grz#:"|"#Im#:"|"#Imt#:"|"#In#:"|"#Mp#:"|"#Par#:"|"#Ps:#"|"#Pzi:#") /[^\/]+/s',
    ],
    'variants' => [
      '"#V:#" ws variantsList+" " ws',
    ],
    'variantsList' => [
      'morphologyParent? /[$@]+/ (morphologyForm homonym?)+", " /[$@,]+/ variantDetails',
    ],
    'variantDetails' => [
      '(variantPosList|variantMorphInfo|variantMeaning)*',
    ],
    'variantPosList' => [
      'ws "$"? pos+", " /[$,]*/'
    ],
    'variantMorphInfo' => [
      'ws "(" ("#Pl:#"|"#Pl#:"|"#pl#"|"#pl#:"|"#S și:#"|"#A și:#"|"#P:#"|"#Pzi:#"|"#Pzi:# 3"|"#pzi:#"|"#abr#") " $" /[^$)]+/ "$"? ")" /[$,]*/',
      'ws "(#A:# #nct#)" ","?',
      'ws "(#A:# #ns#)" ","?',
      'ws "(#Pl:# #nct#)" ","?',
    ],
    'variantMeaning' => [
      'ws /\(@\d+@\)/',
    ],
    'etymology' => [
      '"#E:#" ws /([^\[\]]*\[[^\[\]]+\])*[^\[\]]*/',
    ],
    'reference' => [
      'entryWithInflectedForms ws formattedPosList ws formattedVz ws formattedMainForm',
      '(prefixForm|suffixForm) ws formattedVz ws formattedMainForm',
    ],
    'numberedMeanings' => [
      '(meaning ws)? (meaningNumber ws meaning)+ws',
      'meaning',
    ],
    'meaning' => [
      '/(.(?!\s+@\d))*./s', // stop at the " @nnn@ " number of the next meaning
    ],
    'meaningNumber' => [
      '/@\d+(-\d+)?@/',
    ],
    'entryWithInflectedForms' => [
      '(/[$@]*/ form /[$@]*/ homonym? "-"? /[$@]*/)+/,[$@]* /',
    ],
    'prefixForm' => [
      '/[$@]*/ fragment /[$@]*/ homonym? "-" /[$@]*/',
    ],
    'suffixForm' => [
      '/[$@]*/ "-" /[$@]*/ fragment /[$@]*/ homonym? /[$@]*/',
    ],
    'homonym' => [
      '/\^\d/',
      '/\^\{\d\}/',
    ],
    'formattedPosList' => [
      'formattedPos+", "',
    ],
    'formattedPos' => [
      '/[$@]*/ pos /[$@]*/',
    ],
    'pos' => [
      '"#" posHash "#"',
      'posNoHash',
    ],
    'posHash' => [],
    'posNoHash' => [],
    'formattedMainForm' => [
      '/[$@]*/ mainForm /[$@]*/',
    ],
    'mainForm' => [
      'form homonym? "-"?',
    ],
    'formattedVz' => [
      '/[$@]*/ "#vz#" /[$@]*/',
    ],
    'form' => [
      'fragment+/[- ]/',
      'fragment "-"', // prefixes
      '"-" fragment', // suffixes
    ],
    'fragment' => [
      "/[A-ZĂÂÎȘȚ]?([~a-zăâçîöșțüáắấéíî́óúý()']|##)+/u", // accept capitalized forms
    ],
    'ws' => [
      '/(\s|\n)+/',
    ],
    'ignored' => [
      '/.*/s',
    ],
  ];

  function getGrammar() {
    // insert quotes around every part of speech
    $pos = array_map(function($s) {
      return '"' . $s . '"';
    }, self::PARTS_OF_SPEECH);

    // use the quoted version in the grammar
    $g = self::GRAMMAR;
    $g['posHash'] = $pos;
    $g['posNoHash'] = $pos;

    return $g;
  }

  function prepare($rep) {
    $rep = $this->migrateTildes($rep);
    $rep = $this->unformatSlashes($rep);
    return $rep;
  }

  // move tildes (~) and dashes (-) inside formatting (@ and $)
  private function migrateTildes($rep) {
    $rep = preg_replace('/ ([$@]*)([-~])([$@]+)(?=(\p{L}|[\'#]))/', ' $1$3$2', $rep);
    $rep = preg_replace('/(?<=\p{L})([$@]+)([-~])([$@,]*) /', '$2$1$3 ', $rep);
    return $rep;
  }

  // ensure the square brackets and the slashes inside them are not formatted
  private function unformatSlashes($rep) {
    $chunks = preg_split('|([\[\]/])|', $rep, -1, PREG_SPLIT_DELIM_CAPTURE);
    $len = count($chunks);

    for ($i = 0; $i < $len; $i += 2 /* skip delimiters */) {

      // pad each chunk so it has an even number of markers
      foreach (['@', '$'] as $sym) {
        $missing = substr_count($chunks[$i], $sym) % 2;
        if ($missing) {
          // add a marker here
          $chunks[$i] .= $sym;
          if ($i + 2 < $len) {
            $chunks[$i + 2] = $sym . $chunks[$i + 2];
          }
        }
      }

      // reorder formatting chars at both ends and remove duplicates, but
      // keep the original order whenever possible
      $chunks[$i] = preg_replace_callback(
        '/^[ $@]+/',
        [ $this, 'reorderHead' ],
        $chunks[$i]);
      $chunks[$i] = preg_replace_callback(
        '/[ $@]+$/',
        [ $this, 'reorderTail' ],
        $chunks[$i]);
    }

    return implode($chunks);
  }

  private function reorderHead($match) {
    return $this->reorderSpacesAndMarkers($match[0], true);
  }

  private function reorderTail($match) {
    return $this->reorderSpacesAndMarkers($match[0], false);
  }

  private function reorderSpacesAndMarkers($s, $beginning) {
    // check if there are spaces and remove them
    $s = str_replace(' ', '', $s, $spcCount);

    if ($s) {
      // $s only contains @ and $
      $sym = $s[0];
      $s = str_replace($sym, '', $s, $symCount);
      // now $s only has the other kind of marker
      $s = (($symCount % 2) ? $sym : '') .
        ((strlen($s) % 2) ? $s[0] : '');
    }

    if ($spcCount) {
      $s = $beginning ? (' ' . $s) : ($s . ' ');
    }

    return $s;
  }
}
