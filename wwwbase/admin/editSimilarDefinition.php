<?php

require_once '../../phplib/Core.php';

User::mustHave(User::PRIV_EDIT);

$defId = Request::get('defId');
$similarId = Request::get('similarId');
$rank = Request::get('rank');
$action = Request::get('action');

$def = Definition::get_by_id($defId);
$similar = Definition::get_by_id($similarId);

$mod = DiffUtil::diffAction($similar, $def, $rank, $action);
$mod->process();
$mod->save();

Util::redirect("definitionEdit.php?definitionId={$defId}");
