<?php

ini_set('memory_limit', '512M');
User::mustHave(User::PRIV_EDIT);

$lexemes = Lexeme::loadWithoutPos();

$tags = Model::factory('Tag')
  ->where('isPos', true)
  ->order_by_asc('value')
  ->find_many();

Smart::assign([
  'lexemes' => $lexemes,
  'tags' => $tags,
]);
Smart::addResources('admin');
Smart::display('report/lexemesWithoutPos.tpl');
