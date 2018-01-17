<?php

require_once("../../phplib/Core.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$GAMES = [
		'mill' => 'Game.millGamesPlayed',
		'scramble' => 'Game.scrambleGamesPlayed',
		'hangman' => 'Game.hangmanGamesPlayed',
	];

	$game = Request::get('game');

	if (isset($GAMES[$game])) {
		$entry = $GAMES[$game];
		$cur = Variable::peek($entry, 0);
		Variable::poke($entry, $cur + 1);
	}

}
