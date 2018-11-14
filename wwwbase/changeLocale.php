<?php

require_once('../phplib/Core.php');

$id = Request::get('id');
Locale::change($id);

FlashMessage::add(_('Interface language changed. ' .
                    'Definition text and external links are always in Romanian.'),
                  'success');
Util::redirect(Core::getWwwRoot());
