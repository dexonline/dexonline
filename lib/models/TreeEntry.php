<?php

class TreeEntry extends Association implements DatedObject {
  public static $_table = 'TreeEntry';
  static $classes = ['Tree', 'Entry'];
  static $fields = ['treeId', 'entryId'];
  static $ranks = ['treeRank', 'entryRank'];
}
