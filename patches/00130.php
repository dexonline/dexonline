<?php

$f = new FtpUtil();
$ftp_user_imgs = $f->staticServerRelativeFileList('img/user');

$existing_user_imgs = array_map(function($item) {
  $file = array_pop(explode('/', $item));
  $id = reset(explode('.', $file));
  return $id;
}, $ftp_user_imgs);

printf("Existing avatars: %s\n", count($existing_user_imgs));
printf("Setting hasAvatar for users with avatars.\n");

ORM::for_table('User')->where_id_in($existing_user_imgs)->find_result_set()->set('hasAvatar', 1)->save();

?>
