<?php

require_once '../../lib/Core.php';

const GAMES = [
  'mill' => 'Game.millGamesPlayed',
  'scramble' => 'Game.scrambleGamesPlayed',
  'hangman' => 'Game.hangmanGamesPlayed',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$game = Request::get('game');

	if (isset(GAMES[$game])) {
		$entry = GAMES[$game];
		$cur = Variable::peek($entry, 0);
		Variable::poke($entry, $cur + 1);
	}

}
