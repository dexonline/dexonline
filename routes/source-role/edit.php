<?php

User::mustHave(User::PRIV_ADMIN);

$saveButton = Request::has('saveButton');

if ($saveButton) {

  $roles = buildRoles(
    Request::getArray('roleId'),
    Request::getArray('roleNameSingular'),
    Request::getArray('roleNamePlural'),
    Request::getArray('rolePriority'));

  if (validate($roles)) {
    SourceRole::update($roles);
    Log::notice('Saved source roles.');
    FlashMessage::add('Am salvat rolurile.', 'success');
    Util::redirect(Router::link('aggregate/dashboard'));
  }

} else {

  // first time loading the page
  $roles = Model::factory('SourceRole')
    ->order_by_asc('nameSingular')
    ->find_many();
}

Smart::assign([
  'roles' => $roles,
]);
Smart::display('source-role/edit.tpl');

/*************************************************************************/

/**
 * Returns true on success, false on errors.
 */
function validate($roles) {
  foreach ($roles as $r) {
    if (!$r->nameSingular) {
      FlashMessage::add('Numele la singular nu poate fi vid.');
    }
    if (!$r->namePlural) {
      FlashMessage::add('Numele la plural nu poate fi vid.');
    }
    if (!$r->priority) {
      FlashMessage::add('Prioritatea poate fi vidă.');
    }
    if ($r->priority > 3) {
      FlashMessage::add('Prioritățile peste 3 sînt tratate vizual ca prioritate 1.',
                        'warning');
    }
  }

  return !FlashMessage::hasErrors();
}

function buildRoles($ids, $nameSingulars, $namePlurals, $priorities) {
  $result = [];

  foreach ($ids as $i => $id) {
    // ignore empty records
    if ($nameSingulars[$i] || $namePlurals[$i] || $priorities[$i]) {
      $role = $id
        ? SourceRole::get_by_id($id)
        : Model::factory('SourceRole')->create();
      $role->nameSingular = $nameSingulars[$i];
      $role->namePlural = $namePlurals[$i];
      $role->priority = $priorities[$i];
      $result[] = $role;
    }
  }

  return $result;
}

