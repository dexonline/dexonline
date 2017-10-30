<?php

class PageIndex extends BaseObject {
  public static $_table = 'PageIndex';

  function getImagePath() {
    if ($this->volume) {
      return sprintf("/pages/%03d/vol%02d/%04d.png", $this->sourceId, $this->volume, $this->page);
    } else {
      return sprintf("/pages/%03d/%04d.png", $this->sourceId, $this->page);
    }
  }
}
