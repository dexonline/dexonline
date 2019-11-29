<?php
User::mustHave(User::PRIV_ADMIN | User::PRIV_EDIT);

$sources = new SourceDropdown('getAllForPageImages', []);

Smart::assign([
  'sources' => (array)$sources,
  'isEditor' => User::can(User::PRIV_EDIT),
  ]);
Smart::addResources('ldring', 'tablesorter');
Smart::display('pageindex/list.tpl');
