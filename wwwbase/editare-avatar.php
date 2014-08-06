<?php
require_once("../phplib/util.php");

$file = util_getUploadedFile('avatarFileName');
$error = '';

if (!$file) {
  $error = 'Ați încărcat un fișier invalid.';
} else if ($file['size'] > (1 << 21)) {
  $error = 'Dimensiunea maximă admisă este 2 MB.';
} else if (!in_array($file['type'], array('image/gif', 'image/jpeg', 'image/png'))) {
  $error = 'Sunt permise doar imagini jpeg, png sau gif.';
} else if ($file['error']) {
  $error = 'A intervenit o eroare la încărcare.';
}

if ($error) {
  FlashMessage::add($error);
  util_redirect(util_getWwwRoot() . 'preferinte');
}

$user = session_getUser();
if (!$user) {
  FlashMessage::add('Nu puteți alege o imagine de profil dacă nu sunteți autentificat.');
  util_redirect(util_getWwwRoot());
}

// Remove any old files (with different extensions)
$oldFiles = glob(util_getRootPath() . "wwwbase/img/user/{$user->id}_raw.*");
foreach ($oldFiles as $oldFile) {
  unlink($oldFile);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$destFileName = util_getRootPath() . "wwwbase/img/user/{$user->id}_raw.{$ext}";

if (!move_uploaded_file($file['tmp_name'], $destFileName)) {
  FlashMessage::add('A intervenit o eroare la copierea fișierului.');
  util_redirect(util_getWwwRoot() . 'preferinte');
}
chmod($destFileName, 0666);

SmartyWrap::addCss('jcrop');
SmartyWrap::addJs('jcrop');
SmartyWrap::assign('page_title', "Editarea pozei de profil");
SmartyWrap::assign('rawFileName', "{$user->id}_raw.{$ext}");
SmartyWrap::display('editare-avatar.ihtml');

?>
