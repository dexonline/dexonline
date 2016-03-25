<?php
require_once("../phplib/util.php");

$user = session_getUser();
if (!$user) {
  FlashMessage::add('Nu puteți alege o imagine de profil dacă nu sunteți autentificat.');
  util_redirect(util_getWwwRoot());
}

define('AVATAR_RESOLUTION', 48);
define('AVATAR_QUALITY', 100);
$AVATAR_REMOTE_FILE = "/img/user/{$user->id}.jpg";
$AVATAR_RAW_GLOB = util_getRootPath() . "wwwbase/img/generated/{$user->id}_raw.*";

$x0 = util_getRequestParameter('x0');
$y0 = util_getRequestParameter('y0');
$side = util_getRequestParameter('side');
$delete = util_getRequestParameter('delete');

if ($delete) {
  $f = new FtpUtil();
  $f->staticServerDelete($AVATAR_REMOTE_FILE);
  $user->hasAvatar = 0;
  $user->save();
  FlashMessage::add('Am șters imaginea.', 'success');
  util_redirect('preferinte');
}

$rawFileList = glob($AVATAR_RAW_GLOB);
if (empty($rawFileList)) {
  FlashMessage::add('Imaginea dumneavoastră de profil nu mai există. Vă rugăm să o reîncărcați.');
  util_redirect(util_getWwwRoot());
}
$rawFileName = $rawFileList[0];

$canvas = imagecreatetruecolor(AVATAR_RESOLUTION, AVATAR_RESOLUTION);
$image = loadImage($rawFileName);
imagecopyresampled($canvas, $image, 0, 0, $x0, $y0, AVATAR_RESOLUTION, AVATAR_RESOLUTION, $side, $side);
sharpenImage($canvas);
$tmpFileName = tempnam(Config::get('global.tempDir'), 'dex_avatar_');
imagejpeg($canvas, $tmpFileName, AVATAR_QUALITY);
$f = new FtpUtil();
$f->staticServerPut($tmpFileName, $AVATAR_REMOTE_FILE);
unlink($rawFileName);
unlink($tmpFileName);

$user->hasAvatar = 1;
$user->save();

FlashMessage::add('Am salvat imaginea.', 'success');
util_redirect('preferinte');

/****************************************************************************/

/* Load an image by its (supported) type */
function loadImage($file) {
  $size = getimagesize($file);
  switch ($size['mime']) {
  case 'image/jpeg': return imagecreatefromjpeg($file);
  case 'image/gif': return imagecreatefromgif($file);
  case 'image/png': return imagecreatefrompng($file);
  default: return null;
  }
}

/* Sharpen an image
 * Code courtesy of http://adamhopkinson.co.uk/blog/2010/08/26/sharpen-an-image-using-php-and-gd/
 */
function sharpenImage(&$i) {
  $sharpen = array(
    array(-1.2, -1.0, -1.2),
    array(-1.0, 22.0, -1.0),
    array(-1.2, -1.0, -1.2)
  );
  $divisor = array_sum(array_map('array_sum', $sharpen));
  imageconvolution($i, $sharpen, $divisor, 0);
}

?>
