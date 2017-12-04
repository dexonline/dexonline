<?php

class MeaningSource extends Association implements DatedObject {
  public static $_table = 'MeaningSource';
  static $classes = ['Meaning', 'Source'];
  static $fields = ['meaningId', 'sourceId'];
  static $ranks = ['meaningRank', 'sourceRank'];

}
