<?php

require_once('../phplib/Core.php');

$id = Request::get('id');
Locale::change($id);

FlashMessage::add(_('Interface language changed. ' .
                    'Definitions are always in Romanian (this cannot be changed).'),
                  'success');
Util::redirect(Core::getWwwRoot());
