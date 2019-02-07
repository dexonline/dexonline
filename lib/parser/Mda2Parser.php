<?php

class Mda2Parser extends Parser {
  const PARTS_OF_SPEECH = [
    'a', 'ad', 'ada', 'af', 'afi', 'afp', 'afpt', 'afs', 'ai', 'ain', 'am', 'amp', 'an', 'anh',
    'apr', 'ard', 'arh', 'arp', 'art', 'arti', 'as', 'av', 'avi', 'avr', 'c', 'ec', 'i', 'la', 'lav',
    'lc', 'ls', 'nc', 'ncv', 'nf', 'no', 'pd', 'pdf', 'pdm', 'pin', 'pir', 'pî', 'pnh', 'pnhi',
    'pp', 'ppl', 'ppr', 'prl', 'prli', 'prn', 's', 'sa', 'sf', 'sfa', 'sfi', 'sfm', 'sfn', 'sfp',
    'sfpa', 'sfs', 'sfsa', 'si', 'sm', 'sma', 'smf', 'smi', 'smn', 'smnf', 'smp', 'sms', 'smsa',
    'sn', 'sna', 'snf', 'sni', 'snm', 'snp', 'sns', 'ssg', 'ssga', 'ssp', 'v', 'va', 'vi',
    'vi(a)', 'vif', 'vim', 'vir', 'virp', 'virt', 'vit', 'vit(a)', 'vitr', 'viu', 'vp', 'vr',
    'vr(a)', 'vra', 'vri', 'vrim', 'vrp', 'vrr', 'vrt', 'vru', 'vt', 'vt(a)', 'vta', 'vt(f)',
    'vtf', 'vtfr', 'vti', 'vti(a)', 'vtir', 'vtr', 'vtr(a)', 'vtra', 'vtrf', 'vtri', 'vtrp',
    'vtrr', 'vu',
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
      'plural',
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
      '("#Ac#:"|"#D:#"|"#G-D#:"|"#Vc#:") /[^\/]+/s',
    ],
    'plural' => [
      '("#Pl:#"|"#Pl#:"|"#Pl# și:") /[^\/]+/s',
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
      'ws "(" ("#Pl:#"|"#Pl#:"|"#pl#"|"#pl#:"|"#S și:#"|"#A și:#"|"#P:#"|"#Pzi:#"|"#Pzi:# 3"|"#pzi:#") " $" /[^$)]+/ "$"? ")" /[$,]*/',
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
}
