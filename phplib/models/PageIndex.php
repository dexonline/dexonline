<?php

class PageIndex extends BaseObject implements DatedObject {
  public static $_table = 'PageIndex';

  function getPdfPath() {
    if ($this->volume) {
      return sprintf("/pages/%03d/vol%02d/%04d.pdf", $this->sourceId, $this->volume, $this->page);
    } else {
      return sprintf("/pages/%03d/%04d.pdf", $this->sourceId, $this->page);
    }
  }
}
