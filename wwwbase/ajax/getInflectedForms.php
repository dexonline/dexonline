<?php
require_once("../../phplib/util.php");

$query = util_getRequestParameter('query');
$field = StringUtil::hasDiacritics($query) ? 'formNoAccent' : 'formUtf8General';

$dbResults = Model::factory('InflectedForm')
           ->table_alias('iff')
           ->select('iff.id')
           ->select('iff.form')
           ->select('l.form', 'baseForm')
           ->select('lm.modelType')
           ->select('lm.modelNumber')
           ->select('lm.restriction')
           ->select('i.description')
           ->join('LexemModel', ['iff.lexemModelId', '=', 'lm.id'], 'lm')
           ->join('Lexem', ['lm.lexemId', '=', 'l.id'], 'l')
           ->join('Inflection', ['iff.inflectionId', '=', 'i.id'], 'i')
           ->where_like("iff.{$field}", "{$query}%")
           ->order_by_asc('iff.formNoAccent')
           ->limit(20)
           ->find_many();

$resp = array('results' => array());
foreach ($dbResults as $rec) {
  $resp['results'][] = ['id' => $rec->id,
                        'form' => $rec->form,
                        'baseForm' => $rec->baseForm,
                        'model' => "{$rec->modelType}{$rec->modelNumber}{$rec->restriction}",
                        'inflection' => $rec->description];
}
print json_encode($resp);
