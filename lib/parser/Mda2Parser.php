<?php

class Mda2Parser extends Parser {
  const PARTS_OF_SPEECH = [
    'a', 'ad', 'ada', 'af', 'afi', 'afp', 'afpt', 'afs', 'ai', 'ain', 'am', 'amp',
    'an', 'ang', 'anh', 'anhi', 'ap', 'apr', 'aps', 'ard', 'arh', 'arn', 'arp', 'art', 'arti',
    'as', 'av', 'avi', 'avr', 'c', 'ec', 'i', 'la', 'lav', 'lc', 'ls', 'nc', 'ncv',
    'nf', 'no', 'nof', 'pd', 'pdf', 'pdm', 'pin', 'pir', 'pî', 'png', 'pnh', 'pnhi',
    'pp', 'ppl', 'ppr', 'pps', 'prf', 'prl', 'prli', 'prn', 's', 'sa', 'sf', 'sfa', 'sfi',
    'sfm', 'sfn', 'sfp', 'sfpa', 'sfs', 'sfsa', 'si', 'sm', 'sma', 'smf', 'smfi',
    'smi', 'smn', 'smnf', 'smp', 'sms', 'smsa', 'sn', 'sna', 'snf', 'sni',
    'snm', 'snp', 'sns', 'ssg', 'ssga', 'ssp', 'v', 'va', 'vi', 'vi(a)', 'vif',
    'viim', 'vim', 'vir', 'virp', 'virt', 'vit', 'vit(a)', 'vitr', 'viu', 'vp', 'vr',
    'vr(a)', 'vra', 'vri', 'vrim', 'vrp', 'vrr', 'vrt', 'vru', 'vt', 'vt(a)',
    'vta', 'vt(f)', 'vtf', 'vtfa', 'vtfr', 'vti', 'vti(a)', 'vtir', 'vtr', 'vtr(a)',
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
      '("#Cj#:"|"#Cj# și:"|"#Cnd#:"|"#Grz#:"|"#Im#:"|"#Imt#:"|"#In#:"|"#Mp#:"|"#Par#:"|"#Ps:#"|"#Pzi:#") /[^\/]+/s',
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
      'ws "(" ("#Pl:#"|"#Pl#:"|"#pl#"|"#pl#:"|"#Pl# și:"|"#S și:#"|"#A și:#"|"#P:#"|"#Pzi:#"|"#Pzi:# 3"|"#pzi:#"|"#abr#") " $" /[^$)]+/ "$"? ")" /[$,]*/',
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

  function postProcess(string $rule, string $content, ParserState $state, array &$warnings) {
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
