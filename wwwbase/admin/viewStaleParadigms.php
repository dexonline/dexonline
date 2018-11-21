<?php
require_once('../../phplib/Core.php');
User::mustHave(User::PRIV_ADMIN);
Util::assertNotMirror();

const MAX_DISPLAYED = 1000;

$timer = Request::get('timer');

if ($timer) {
  ini_set('max_execution_time', 2 * $timer);
  $deadline = DebugInfo::getTimeInMillis() + $timer * 1000;
  $fixed = 0;

  do {
    $lexemes = Lexeme::getStaleParadigms();
    Log::info('loaded %d lexemes', count($lexemes));
    foreach ($lexemes as $l) {
      $l->regenerateParadigm();
      $l->save(); // just to update modDate
    }
    $fixed += count($lexemes);
  } while (count($lexemes) && (DebugInfo::getTimeInMillis() < $deadline));

  FlashMessage::add("{$fixed} paradigme regenerate", 'success');
  Variable::poke('Count.staleParadigms', Lexeme::countStaleParadigms());
  Util::redirect('viewStaleParadigms');
}

$count = Variable::peek('Count.staleParadigms');
$lexemes = Lexeme::getStaleParadigms(MAX_DISPLAYED);

SmartyWrap::assign([
  'count' => $count,
  'lexemes' => $lexemes,
]);

SmartyWrap::addCss('admin');
SmartyWrap::display('admin/viewStaleParadigms.tpl');
