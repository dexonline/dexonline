<?php

require_once("../../phplib/util.php");
util_assertModerator(PRIV_EDIT);
util_assertNotMirror();

$mentions = Model::factory('Mention')
          ->table_alias('m')
          ->select('mean.htmlRep')
          ->select('mean.breadcrumb')
          ->select('src.id', 'srcId')
          ->select('src.description', 'srcDesc')
          ->select('dest.id', 'destId')
          ->select('dest.description', 'destDesc')
          ->join('Meaning', ['m.meaningId', '=', 'mean.id'], 'mean')
          ->join('Tree', ['mean.treeId', '=', 'src.id'], 'src')
          ->join('Tree', ['m.objectId', '=', 'dest.id'], 'dest')
          ->where('m.objectType', Mention::TYPE_TREE)
          ->find_many();

SmartyWrap::assign('mentions', $mentions);
SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewTreeMentions.tpl');

?>
