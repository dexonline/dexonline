<?php

class Donation extends BaseObject implements DatedObject {
  public static $_table = 'Donation';

  const SOURCE_MANUAL = 1;
  const SOURCE_OTRS = 2;
}
