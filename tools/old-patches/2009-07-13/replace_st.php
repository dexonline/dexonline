<?php

require_once('../../phplib/util.php');

$FIELDS = array(
                'Comment' => array('Contents', 'HtmlContents'),
                'Definition' => array('Lexicon', 'InternalRep', 'HtmlRep'),
                'GuideEntry' => array('Correct', 'CorrectHtml', 'Wrong', 'WrongHtml', 'Comments', 'CommentsHtml'),
                'RecentLink' => array('Text'),
                'Source' => array('ShortName', 'Name', 'Author', 'Publisher', 'Year'),
                'Typo' => array('Problem'),
                'User' => array('Name'),
                'inflections' => array('infl_descr'),
                'lexems' => array('lexem_forma', 'lexem_neaccentuat', 'lexem_utf8_general', 'lexem_invers', 'lexem_descr', 'lexem_parse_info', 'lexem_comment'),
                'model_types' => array('mt_descr'),
                'models' => array('model_descr', 'model_exponent'),
                'transforms' => array('transf_from', 'transf_to', 'transf_descr'),
                'wordlist' => array('wl_form', 'wl_neaccentuat', 'wl_utf8_general'),
                );

$MAPPINGS = array(array('ş', 'ș'), array('Ş', 'Ș'), array('ţ', 'ț'), array('Ţ', 'Ț'));

foreach ($FIELDS as $table => $fields) {
  foreach ($fields as $field) {
    foreach ($MAPPINGS as $mapping) {
      $query = "update $table set $field = replace($field, '{$mapping[0]}', '{$mapping[1]}')";
      print "$query\n";
      if (mysql_query($query)) {
        print "    " . mysql_affected_rows() . " rows affected.\n";
      } else {
        OS::errorAndExit(mysql_error());
      }
    }
  }
}
                

?>
