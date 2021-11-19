<?php

Smart::assign([
  'letters' => Str::unicodeExplode('aăâbcdefghiîjklmnopqrsștțuvwxyz'),
]);
Smart::addResources('sprintf');
Smart::display('games/hangman.tpl');
