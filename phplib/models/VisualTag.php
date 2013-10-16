<?php

class VisualTag extends BaseObject implements DatedObject {
  public static $_table = 'VisualTag';

  public static function deleteByImageId($imageId) {
    $tags = VisualTag::get_all_by_imageId($imageId);

    foreach($tags as $tag) {
      $tag->delete();
    }
  }
}

?>
